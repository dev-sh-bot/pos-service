<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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
            throw new \RuntimeException('SRB sale value must be greater than zero.');
        }

        $payload = $this->payload($order);

        $response = Http::acceptJson()->asJson()->timeout(20)->post(self::ENDPOINT, $payload);
        $data = $response->json() ?: [];

        if (! $response->successful() || ($data['resCode'] ?? null) !== '00' || empty($data['srbInvoiceId']) || empty($data['QRCodeLink'])) {
            $message = $data['error'] ?? $data['message'] ?? 'SRB did not accept the invoice.';
            throw new \RuntimeException('SRB invoice submission failed: ' . $message);
        }

        $order->update([
            'srb_invoice_id' => (string) $data['srbInvoiceId'],
            'srb_qr_code_link' => (string) $data['QRCodeLink'],
            'srb_status' => 'submitted',
            'srb_response' => json_encode($data, JSON_THROW_ON_ERROR),
        ]);

        return $order->refresh();
    }

    public function payload(Order $order): array
    {
        $order->loadMissing(['branch', 'customer']);
        $branch = $order->branch;
        $saleValue = round((float) $order->subtotal, 2);
        $taxAmount = round((float) $order->tax_amount, 2);
        $discountAmount = round((float) $order->discount_amount, 2);

        return [
            'posId' => (int) $branch?->srb_pos_id,
            'name' => $this->cleanBusinessName((string) config('settings.srb_business_name')),
            'ntn' => $this->normaliseNtn((string) config('settings.srb_ntn')),
            'invoiceDateTime' => Carbon::parse($order->created_at)->format('Y-m-d H:i:s'),
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
            'cpcCode' => 'N/A',
            'extraInf' => 'N/A',
            'modeOfPay' => 'Cash',
            'transType' => config('settings.srb_transaction_type', 'Test'),
            'posUser' => (string) $branch?->srb_pos_user,
            'posPass' => (string) $branch?->srb_pos_password,
        ];
    }

    private function ensureConfigured(Order $order): void
    {
        if (! $order->branch || blank($order->branch->srb_pos_id) || blank($order->branch->srb_pos_user) || blank($order->branch->srb_pos_password) || blank(config('settings.srb_business_name')) || blank(config('settings.srb_ntn'))) {
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
}
