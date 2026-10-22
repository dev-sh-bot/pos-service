<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SrbLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SrbInvoiceService
{
    private const ENDPOINT = 'https://pos.srb.gos.pk/ePOSGateway/v1/SalesInvoiceService.api';

    /**
     * Submit an order to SRB and persist the fiscal invoice information.
     */
    public function submit(Order $order): Order
    {
        $order->loadMissing(['branch', 'customer']);
        $this->ensureConfigured($order);

        $saleValue = round((float) $order->subtotal, 2);
        if ($saleValue <= 0) {
            $order->update([
                'srb_status' => 'failed',
                'srb_response' => json_encode(['error' => 'SRB sale value must be greater than zero.']),
            ]);
            return $order->refresh();
        }

        return $this->dispatchToSrb($order);
    }

    /**
     * Dispatch invoice to SRB with branch-wise duplicate detection and auto-advance retry loop.
     */
    private function dispatchToSrb(Order $order, int $attempt = 1, int $maxAttempts = 10): Order
    {
        $payload = $this->payload($order);
        $invoiceNo = (string) $this->invoiceId($order);
        $branchId = $order->branch_id;

        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->timeout(25)
                ->connectTimeout(10)
                ->post(self::ENDPOINT, $payload);

            $data = $response->json() ?: [];
            $statusCode = $response->status();
            $rawBody = $response->body();
        } catch (\Throwable $e) {
            Log::error("SRB network/connection failure for Order #{$order->id} ({$order->invoice_no}): " . $e->getMessage());

            $this->logHit([
                'order_id' => $order->id,
                'branch_id' => $branchId,
                'invoice_no' => $invoiceNo,
                'invoice_type' => 1,
                'request_type' => 'invoice',
                'attempt' => $attempt,
                'status_code' => null,
                'res_code' => null,
                'is_success' => false,
                'payload' => $payload,
                'response' => null,
                'error_message' => 'Connection to SRB failed: ' . $e->getMessage(),
            ]);

            $order->update([
                'srb_status' => 'failed',
                'srb_response' => json_encode(['error' => 'Connection to SRB failed: ' . $e->getMessage()]),
            ]);
            return $order->refresh();
        }

        $resCode = (string) ($data['resCode'] ?? '');
        $hasFiscalData = !empty($data['srbInvoiceId']) && !empty($data['QRCodeLink']);
        $isSuccess = ($resCode === '00' || $response->successful()) && $hasFiscalData;

        // Check if SRB says invoice already exists / duplicate
        $rawMessage = strtolower(($rawBody ?? '') . ' ' . json_encode($data));
        $isDuplicate = str_contains($rawMessage, 'duplicate') ||
            str_contains($rawMessage, 'already exist') ||
            str_contains($rawMessage, 'already submitted') ||
            $resCode === '02';

        $errorMessage = null;
        if (! $isSuccess) {
            $errorMessage = $data['resCodeDesc'] ?? $data['response'] ?? $data['error'] ?? ($isDuplicate ? 'Duplicate invoice rejected by SRB' : 'SRB invoice submission failed');
        }

        // Log this attempt
        $this->logHit([
            'order_id' => $order->id,
            'branch_id' => $branchId,
            'invoice_no' => $invoiceNo,
            'invoice_type' => 1,
            'request_type' => 'invoice',
            'attempt' => $attempt,
            'status_code' => $statusCode,
            'res_code' => $resCode ?: null,
            'is_success' => $isSuccess,
            'srb_invoice_id' => $data['srbInvoiceId'] ?? null,
            'srb_qr_code_link' => $data['QRCodeLink'] ?? null,
            'payload' => $payload,
            'response' => !empty($data) ? $data : $rawBody,
            'error_message' => $errorMessage,
        ]);

        // Check if response indicates success with fiscal data
        if ($isSuccess) {
            $order->update([
                'srb_invoice_id' => (string) $data['srbInvoiceId'],
                'srb_qr_code_link' => (string) $data['QRCodeLink'],
                'srb_status' => 'submitted',
                'srb_response' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
            return $order->refresh();
        }

        if ($isDuplicate) {
            // If SRB already accepted it and returned the invoiceId/QRCodeLink
            if ($hasFiscalData) {
                $order->update([
                    'srb_invoice_id' => (string) $data['srbInvoiceId'],
                    'srb_qr_code_link' => (string) $data['QRCodeLink'],
                    'srb_status' => 'submitted',
                    'srb_response' => json_encode($data, JSON_THROW_ON_ERROR),
                ]);
                return $order->refresh();
            }
        }

        // If not successful and not recoverable
        $order->update([
            'srb_status' => 'failed',
            'srb_response' => json_encode($data),
        ]);

        return $order->refresh();
    }

    /**
     * Advance the order invoice number to the next available sequence.
     */
    public function advanceInvoiceNo(Order $order): string
    {
        $currentNo = (string) $order->invoice_no;
        $prefix = 'INV-';
        $number = 1;

        if (preg_match('/^(.*-)(\d+)$/', $currentNo, $matches)) {
            $prefix = $matches[1];
            $number = ((int) $matches[2]) + 1;
        } else {
            $branchCode = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $order->branch?->code ?: 'BR01'));
            $prefix = "INV-{$branchCode}-";
            $number = 1;
        }

        $existingInvoices = Order::where('invoice_no', 'like', $prefix . '%')
            ->where('id', '!=', $order->id)
            ->pluck('invoice_no');

        foreach ($existingInvoices as $inv) {
            if (preg_match('/-(\d+)$/', (string) $inv, $lastMatches)) {
                $lastNum = (int) $lastMatches[1];
                if ($lastNum >= $number) {
                    $number = $lastNum + 1;
                }
            }
        }

        $newNo = $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
        while (Order::where('invoice_no', $newNo)->where('id', '!=', $order->id)->exists()) {
            $number++;
            $newNo = $prefix . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
        }

        return $newNo;
    }

    public function payload(Order $order): array
    {
        $order->loadMissing(['branch', 'customer']);
        $branch = $order->branch;
        $saleValue = round((float) $order->subtotal, 2);
        $taxAmount = round((float) $order->tax_amount, 2);
        $discountAmount = round((float) $order->discount_amount, 2);

        return [
            // Testing: static POS ID 'POS-111' (revert back to: (int) $branch?->srb_pos_id)
            'posId' => (int) $branch?->srb_pos_id,
            'name' => $this->cleanBusinessName((string) config('settings.srb_business_name')),
            'ntn' => $this->normaliseNtn((string) config('settings.srb_ntn')),
            'invoiceDateTime' => Carbon::parse($order->created_at)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s'),
            'invoiceType' => 1,
            'invoiceId' => $this->invoiceId($order),
            'rateValue' => round((float) $order->tax_percent, 2),
            'saleValue' => $saleValue,
            'taxAmount' => $taxAmount,
            'discountAmount' => $discountAmount,
            'serviceCharges' => 0,
            'extraCharges' => 0,
            'netAmount' => round((float) $order->total_amount, 2),
            'consumerName' => $order->customer?->full_name ?: 'N/A',
            'consumerMobile' => $order->customer?->phone ?: 'N/A',
            'consumerEmail' => $order->customer?->email ?: 'N/A',
            'consumerNTN' => 'N/A',
            'address' => $order->customer?->address ?: 'N/A',
            'cpcCode' => '9722',
            'extraInf' => 'N/A',
            'modeOfPay' => strtolower((string) ($order->payment_method ?? 'cash')) === 'card' ? 'Card' : 'Cash',
            'transType' => config('settings.srb_transaction_type', 'Test'),
            'posUser' => (string) $branch?->srb_pos_user,
            'posPass' => (string) $branch?->srb_pos_password,
        ];
    }

    /**
     * Submit a sale return to SRB and persist the fiscal return invoice information.
     */
    public function submitReturn(\App\Models\SaleReturn $saleReturn): \App\Models\SaleReturn
    {
        $saleReturn->loadMissing(['order.customer', 'branch', 'customer']);
        $this->ensureConfigured($saleReturn);

        $saleValue = round((float) $saleReturn->refund_subtotal, 2);
        if ($saleValue <= 0) {
            $saleReturn->update([
                'srb_status' => 'failed',
                'srb_response' => json_encode(['error' => 'SRB sale return value must be greater than zero.']),
            ]);
            return $saleReturn->refresh();
        }

        $payload = $this->returnPayload($saleReturn);
        $returnNo = (string) $saleReturn->return_no;
        $branchId = $saleReturn->branch_id ?? $saleReturn->order?->branch_id;

        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->asJson()
                ->timeout(25)
                ->connectTimeout(10)
                ->post(self::ENDPOINT, $payload);

            $data = $response->json() ?: [];
            $statusCode = $response->status();
            $rawBody = $response->body();
        } catch (\Throwable $e) {
            Log::error("SRB return network failure for return #{$saleReturn->id}: " . $e->getMessage());

            $this->logHit([
                'order_id' => $saleReturn->order_id,
                'sale_return_id' => $saleReturn->id,
                'branch_id' => $branchId,
                'invoice_no' => $returnNo,
                'invoice_type' => 2,
                'request_type' => 'sale_return',
                'attempt' => 1,
                'status_code' => null,
                'res_code' => null,
                'is_success' => false,
                'payload' => $payload,
                'response' => null,
                'error_message' => 'Connection to SRB failed: ' . $e->getMessage(),
            ]);

            $saleReturn->update([
                'srb_status' => 'failed',
                'srb_response' => json_encode(['error' => 'Connection to SRB failed: ' . $e->getMessage()]),
            ]);
            return $saleReturn->refresh();
        }

        $resCode = (string) ($data['resCode'] ?? '');
        $hasFiscalData = !empty($data['srbInvoiceId']) && !empty($data['QRCodeLink']);
        $isSuccess = ($resCode === '00' || $response->successful()) && $hasFiscalData;

        $errorMessage = null;
        if (! $isSuccess) {
            $errorMessage = $data['resCodeDesc'] ?? $data['response'] ?? $data['error'] ?? 'SRB return submission failed';
        }

        $this->logHit([
            'order_id' => $saleReturn->order_id,
            'sale_return_id' => $saleReturn->id,
            'branch_id' => $branchId,
            'invoice_no' => $returnNo,
            'invoice_type' => 2,
            'request_type' => 'sale_return',
            'attempt' => 1,
            'status_code' => $statusCode,
            'res_code' => $resCode ?: null,
            'is_success' => $isSuccess,
            'srb_invoice_id' => $data['srbInvoiceId'] ?? null,
            'srb_qr_code_link' => $data['QRCodeLink'] ?? null,
            'payload' => $payload,
            'response' => !empty($data) ? $data : $rawBody,
            'error_message' => $errorMessage,
        ]);

        if ($isSuccess) {
            $saleReturn->update([
                'srb_invoice_id' => (string) $data['srbInvoiceId'],
                'srb_qr_code_link' => (string) $data['QRCodeLink'],
                'srb_status' => 'submitted',
                'srb_response' => json_encode($data, JSON_THROW_ON_ERROR),
            ]);
            return $saleReturn->refresh();
        }

        $saleReturn->update([
            'srb_status' => 'failed',
            'srb_response' => json_encode($data),
        ]);

        return $saleReturn->refresh();
    }

    public function returnPayload(\App\Models\SaleReturn $saleReturn): array
    {
        $saleReturn->loadMissing(['order.customer', 'branch', 'customer']);
        $order = $saleReturn->order;
        $branch = $saleReturn->branch ?? $order?->branch;
        $customer = $saleReturn->customer ?? $order?->customer;

        $saleValue = round((float) $saleReturn->refund_subtotal, 2);
        $taxAmount = round((float) $saleReturn->refund_tax, 2);
        $discountAmount = round((float) $saleReturn->refund_discount, 2);
        $netAmount = round((float) $saleReturn->refund_total, 2);
        $rateValue = round((float) ($order?->tax_percent ?? 0), 2);

        $origInvoiceNo = $order ? $order->getInvoiceNumber() : 'N/A';
        $origSrbId = $order?->srb_invoice_id;
        $extraInf = 'Orig Inv: ' . $origInvoiceNo . ($origSrbId ? ' | SRB ID: ' . $origSrbId : '');

        return [
            // Testing: static POS ID 'POS-111' (revert back to: (int) $branch?->srb_pos_id)
            'posId' => (int) $branch?->srb_pos_id,
            'name' => $this->cleanBusinessName((string) config('settings.srb_business_name')),
            'ntn' => $this->normaliseNtn((string) config('settings.srb_ntn')),
            'invoiceDateTime' => Carbon::parse($saleReturn->created_at)->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s'),
            'invoiceType' => 2, // 2 = Sales Return (Credit Note)
            'invoiceId' => (string) $saleReturn->return_no,
            'rateValue' => $rateValue,
            'saleValue' => $saleValue,
            'taxAmount' => $taxAmount,
            'discountAmount' => $discountAmount,
            'serviceCharges' => 0,
            'extraCharges' => 0,
            'netAmount' => $netAmount,
            'consumerName' => $customer?->full_name ?: ($customer ? ($customer->first_name . ' ' . $customer->last_name) : 'N/A'),
            'consumerMobile' => $customer?->phone ?: 'N/A',
            'consumerEmail' => $customer?->email ?: 'N/A',
            'consumerNTN' => 'N/A',
            'address' => $customer?->address ?: 'N/A',
            'cpcCode' => '9722',
            'extraInf' => $extraInf,
            'modeOfPay' => strtolower((string) ($saleReturn->refund_method ?? 'cash')) === 'card' ? 'Card' : 'Cash',
            'transType' => config('settings.srb_transaction_type', 'Test'),
            'posUser' => (string) $branch?->srb_pos_user,
            'posPass' => (string) $branch?->srb_pos_password,
        ];
    }

    private function ensureConfigured(Order|\App\Models\SaleReturn $model): void
    {
        $branch = $model->branch ?? ($model instanceof \App\Models\SaleReturn ? $model->order?->branch : null);
        // Testing: Using static POS-111 so bypass branch->srb_pos_id blank check
        if (! $branch || blank($branch->srb_pos_user) || blank($branch->srb_pos_password) || blank(config('settings.srb_business_name')) || blank(config('settings.srb_ntn'))) {
            throw new \RuntimeException('SRB is enabled, but this branch SRB configuration is incomplete.');
        }
    }

    private function invoiceId(Order $order): string
    {
        return $order->getInvoiceNumber();
    }

    private function normaliseNtn(string $ntn): string
    {
        $ntn = trim($ntn);

        // Remove starting S
        $ntn = preg_replace('/^S/i', '', $ntn);

        // Remove trailing -7, -12, etc.
        $ntn = preg_replace('/-\d+$/', '', $ntn);

        // Keep only numbers
        return preg_replace('/[^0-9]/', '', $ntn);
    }

    private function cleanBusinessName(string $name): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9 ]/', '', $name));
    }

    /**
     * Log an SRB API hit into the srb_logs table.
     */
    private function logHit(array $data): void
    {
        try {
            SrbLog::create([
                'order_id' => $data['order_id'] ?? null,
                'sale_return_id' => $data['sale_return_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'invoice_no' => $data['invoice_no'] ?? null,
                'invoice_type' => $data['invoice_type'] ?? 1,
                'request_type' => $data['request_type'] ?? 'invoice',
                'endpoint' => $data['endpoint'] ?? self::ENDPOINT,
                'attempt' => $data['attempt'] ?? 1,
                'status_code' => $data['status_code'] ?? null,
                'res_code' => $data['res_code'] ?? null,
                'is_success' => (bool) ($data['is_success'] ?? false),
                'srb_invoice_id' => $data['srb_invoice_id'] ?? null,
                'srb_qr_code_link' => $data['srb_qr_code_link'] ?? null,
                'payload' => is_array($data['payload'] ?? null) ? json_encode($data['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($data['payload'] ?? null),
                'response' => is_array($data['response'] ?? null) ? json_encode($data['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($data['response'] ?? null),
                'error_message' => $data['error_message'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save SRB log: ' . $e->getMessage());
        }
    }
}
