<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Branch;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Services\SrbInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:orders.view')->only(['index']);
        $this->middleware('can:orders.create')->only(['store']);
        $this->middleware('can:orders.edit')->only(['partialPayment']);
    }

    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = $request->user();
        $isAdmin = (bool) ($user && $user->isSuperAdmin());

        // Branches accessible to the user
        if ($isAdmin) {
            $branches = Branch::where('is_active', true)->orderBy('name')->get();
        } else {
            $branches = $user
                ? $user->branches()->where('is_active', true)->orderBy('name')->get()
                : collect();
        }

        $allowedBranchIds = $branches->pluck('id')->all();

        // Selected branch filter: Admin can view all or specific; regular user only assigned
        $selectedBranchId = null;
        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $reqBranchId = (int) $request->input('branch_id');
            if ($isAdmin || in_array($reqBranchId, $allowedBranchIds, true)) {
                $selectedBranchId = $reqBranchId;
            }
        } elseif (! $isAdmin && $branches->count() === 1) {
            $selectedBranchId = $branches->first()->id;
        }

        $ordersQuery = Order::query()
            ->with(['items' => function($query) {
                $query->with(['product', 'service', 'deal']);
            }, 'payments', 'customer', 'branch', 'counter', 'saleReturns.items'])
            ->when($selectedBranchId, function ($query, $branchId): void {
                $query->where('branch_id', $branchId);
            }, function ($query) use ($isAdmin, $allowedBranchIds): void {
                if (! $isAdmin) {
                    $query->whereIn('branch_id', $allowedBranchIds);
                }
            })
            ->when($request->input('start_date'), function ($query, $startDate): void {
                $query->where('created_at', '>=', $startDate . ' 00:00:00');
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($q) use ($search): void {
                    $q->where('invoice_no', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('srb_invoice_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search): void {
                            $cq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        // Summary totals for filtered dataset
        $totalsQuery = clone $ordersQuery;
        $totalsQuery->getQuery()->orders = null;
        $totalSales = (float) $totalsQuery->sum('total_amount');
        $filteredOrderIds = (clone $ordersQuery)->pluck('id');
        $receivedAmount = $filteredOrderIds->isNotEmpty()
            ? (float) DB::table('payments')->whereIn('order_id', $filteredOrderIds)->sum('amount')
            : 0;

        $orders = $ordersQuery->paginate(15)->withQueryString();

        return view('orders.index', [
            'orders'           => $orders,
            'total'            => $totalSales,
            'receivedAmount'   => $receivedAmount,
            'branches'         => $branches,
            'selectedBranchId' => $selectedBranchId,
            'isAdmin'          => $isAdmin,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('cart.index');
    }

    public function store(OrderStoreRequest $request, SrbInvoiceService $srbInvoiceService): \Illuminate\Http\JsonResponse
    {
        $userId = $request->user()?->id;
        $lockKey = $userId ? "order_submission_user_{$userId}" : ("order_sub_" . md5((string) $request->ip() . '_' . (string) $request->input('amount')));
        $lock = Cache::lock($lockKey, 10);

        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'An order is already being processed. Please wait a moment...',
            ], 429);
        }

        // Idempotency check: if client provided a submission_id and it already completed, return existing order
        $submissionId = $request->input('submission_id');
        if ($submissionId) {
            $existingOrderId = Cache::get("order_sub_done_{$submissionId}");
            if ($existingOrderId) {
                $existingOrder = Order::with(['items' => function($query) {
                    $query->with(['product', 'service', 'deal']);
                }, 'payments', 'customer', 'user', 'branch', 'counter', 'receivingAccount'])->find($existingOrderId);

                if ($existingOrder) {
                    if ($lock) {
                        optional($lock)->release();
                    }
                    return response()->json([
                        'success' => true,
                        'message' => __('order.created_successfully'),
                        'order_id' => $existingOrder->id,
                        'order' => $existingOrder,
                    ], 200);
                }
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $userId) {
                // 1. CRITICAL: Lock user_cart rows FOR UPDATE FIRST to prevent race conditions and duplicate entries
                $cartItemRows = DB::table('user_cart')
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->get();

                if ($cartItemRows->isEmpty()) {
                    throw new \Exception(__('cart.empty'));
                }

                $cartItems = $cartItemRows->map(function ($row) {
                    if ($row->item_type === 'service' || (string) $row->item_type === '1') {
                        $item = Service::find($row->item_id);
                    } elseif ($row->item_type === 'deal' || (string) $row->item_type === '2') {
                        $item = Deal::find($row->item_id);
                    } else {
                        $item = Product::find($row->item_id);
                    }

                    if ($item) {
                        $item->pivot = (object) [
                            'quantity' => $row->quantity,
                        ];
                    }

                    return $item;
                })->filter();

                if ($cartItems->isEmpty()) {
                    throw new \Exception(__('cart.empty'));
                }

                // Determine branch and counter
                $branchId = $request->session()->get('pos_branch_id');
                $counterId = $request->session()->get('pos_counter_id');

                if (!$branchId && $request->user()) {
                    $userBranch = $request->user()->branches()->first();
                    if ($userBranch) {
                        $branchId = $userBranch->id;
                    }
                }
                if (!$branchId) {
                    $defaultBranch = Branch::first();
                    if ($defaultBranch) {
                        $branchId = $defaultBranch->id;
                    }
                }

                $branchCode = 'BR01';
                if ($branchId) {
                    $branch = Branch::find($branchId);
                    if ($branch && !empty($branch->code)) {
                        $branchCode = strtoupper(trim($branch->code));
                    }
                }

                // Generate Independent Lifetime Invoice Number per Branch: INV-{BranchCode}-{001}
                $cleanBranchCode = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $branchCode ?: 'BR01'));
                $prefix = "INV-{$cleanBranchCode}-";

                // Find all existing invoice numbers for this branch to accurately determine the true maximum sequence
                $existingInvoices = Order::where(function ($q) use ($branchId, $prefix) {
                        if ($branchId) {
                            $q->where('branch_id', $branchId);
                        } else {
                            $q->where('invoice_no', 'like', $prefix . '%');
                        }
                    })
                    ->where('invoice_no', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->pluck('invoice_no');

                $maxSeq = 0;
                foreach ($existingInvoices as $inv) {
                    if (preg_match('/-(\d+)$/', (string) $inv, $matches)) {
                        $num = (int) $matches[1];
                        if ($num > $maxSeq) {
                            $maxSeq = $num;
                        }
                    }
                }

                $nextNumber = $maxSeq + 1;
                $seqStr = str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
                $invoiceNo = "{$prefix}{$seqStr}";

                // Ensure globally unique continuous sequence
                while (Order::where('invoice_no', $invoiceNo)->exists()) {
                    $nextNumber++;
                    $seqStr = str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
                    $invoiceNo = "{$prefix}{$seqStr}";
                }

                // Create order directly with invoice_no
                $subtotal = (float) $request->subtotal;
                $taxPercent = $request->tax_percent !== null
                    ? (float) $request->tax_percent
                    : ((bool) config('settings.tax_enabled', false) ? 8 : 0);
                $taxAmount = $request->tax_amount !== null
                    ? (float) $request->tax_amount
                    : $subtotal * $taxPercent / 100;
                $discountAmount = min($subtotal, max(0, (float) ($request->discount_amount ?? 0)));
                $discountPercent = min(100, max(0, (float) ($request->discount_percent ?? 0)));

                $paymentMethod = in_array(strtolower((string) $request->input('payment_method')), ['card', 'cash'], true)
                    ? strtolower((string) $request->input('payment_method'))
                    : 'cash';

                $cardHolderName     = $paymentMethod === 'card' ? ($request->input('card_holder_name') ?: null) : null;
                $cardLastFour       = $paymentMethod === 'card' ? (preg_replace('/\D/', '', (string)($request->input('card_last_four') ?? '')) ?: null) : null;
                $receivingAccountId = $paymentMethod === 'card' ? ($request->input('receiving_account_id') ?: null) : null;

                $order = Order::create([
                    'customer_id'           => $request->customer_id,
                    'user_id'               => $request->user()->id,
                    'branch_id'             => $branchId,
                    'counter_id'            => $counterId,
                    'invoice_no'            => $invoiceNo,
                    'subtotal'              => $subtotal,
                    'tax_percent'           => $taxPercent,
                    'tax_amount'            => $taxAmount,
                    'discount_percent'      => $discountPercent,
                    'discount_amount'       => $discountAmount,
                    'total_amount'          => max(0, $subtotal + $taxAmount - $discountAmount),
                    'payment_method'        => $paymentMethod,
                    'card_holder_name'      => $cardHolderName,
                    'card_last_four'        => $cardLastFour,
                    'receiving_account_id'  => $receivingAccountId,
                    'created_at'            => Carbon::now('Asia/Karachi'),
                ]);

                // Create order items and update product quantities
                foreach ($cartItems as $item) {
                    $this->createOrderItem($order, $item, $request->input('item_rates', []));
                    $this->reduceProductStock($item, $order);
                }

                // Clear cart immediately within the transaction
                DB::table('user_cart')->where('user_id', $request->user()->id)->delete();

                // Create payment
                $order->payments()->create([
                    'amount'               => $request->amount,
                    'payment_method'       => $paymentMethod,
                    'user_id'              => $request->user()->id,
                    'card_holder_name'     => $cardHolderName,
                    'card_last_four'       => $cardLastFour,
                    'receiving_account_id' => $receivingAccountId,
                ]);

                return $order;
            });

            // Remember created order for idempotency
            if ($submissionId) {
                Cache::put("order_sub_done_{$submissionId}", $order->id, 300);
            }

            // Submit to SRB outside the database transaction so local orders are NEVER rolled back
            if ((bool) config('settings.srb_enabled', false)) {
                try {
                    $srbInvoiceService->submit($order);
                } catch (\Throwable $srbEx) {
                    Log::warning("SRB invoice submission warning for order #{$order->id} ({$order->invoice_no}): " . $srbEx->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => __('order.created_successfully'),
                'order_id' => $order->id,
                'order' => $order->fresh(['items' => function($query) {
                    $query->with(['product', 'service', 'deal']);
                }, 'payments', 'customer', 'user', 'branch', 'counter', 'receivingAccount']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } finally {
            if ($lock) {
                optional($lock)->release();
            }
        }
    }

    public function partialPayment(Request $request)
    {
        $order = Order::findOrFail($request->input('order_id'));

        $user = $request->user();
        if ($user && ! $user->isSuperAdmin()) {
            $allowedBranchIds = $user->branches()->where('is_active', true)->pluck('branches.id')->all();
            if ($order->branch_id && ! in_array($order->branch_id, $allowedBranchIds, true)) {
                abort(403, 'Unauthorized action for this branch order.');
            }
        }

        $remainingAmount = $order->total() - $order->receivedAmount();

        if ($request->input('amount') > $remainingAmount) {
            return redirect()->route('orders.index')
                ->withErrors(__('order.amount_exceeds_balance'));
        }

        DB::transaction(function () use ($order, $request): void {
            $order->payments()->create([
                'amount' => $request->amount,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('orders.index')
            ->with('success', __('order.partial_payment_success', [
                'amount' => config('settings.currency_symbol') . number_format($request->amount, 2)
            ]));
    }

    /**
     * Create an order item from cart item.
     */
    private function createOrderItem(Order $order, $item, array $itemRates = []): void
    {
        $itemType = 'product';
        $typeInt = 0;
        if ($item instanceof \App\Models\Service) {
            $itemType = 'service';
            $typeInt = 1;
        } elseif ($item instanceof \App\Models\Deal) {
            $itemType = 'deal';
            $typeInt = 2;
        }

        $itemKey = $itemType . ':' . $item->id;
        $unitPrice = array_key_exists($itemKey, $itemRates)
            ? (float) $itemRates[$itemKey]
            : (float) ($item->discounted_amount ?? $item->price ?? $item->rate ?? 0);

        $order->items()->create([
            'price' => $unitPrice * $item->pivot->quantity,
            'quantity' => $item->pivot->quantity,
            'item_id' => $item->id,  // Fixed: use item_id instead of product_id
            'item_type' => $typeInt,
            'item_name' => $item->name,
        ]);
    }

    private function cartItems(int $userId): \Illuminate\Support\Collection
    {
        return DB::table('user_cart')->where('user_id', $userId)->get()->map(function ($row) {
            $item = null;
            if ((int) $row->item_type === 1) {
                $item = Service::find($row->item_id);
            } elseif ((int) $row->item_type === 2) {
                $item = Deal::find($row->item_id);
            } else {
                $item = Product::find($row->item_id);
            }
            if (! $item) return null;
            $item->item_type = (int) $row->item_type;
            $item->pivot = (object) ['quantity' => (int) $row->quantity];
            return $item;
        })->filter()->values();
    }

    /**
     * Reduce product stock based on cart quantity and record stock ledger entry.
     */
    private function reduceProductStock($item, $order = null): void
    {
        if ($item instanceof \App\Models\Product) {
            $qty = (float) $item->pivot->quantity;
            $avgCost = \App\Models\Stock::calculateAverageCost($item->id);

            $item->decrement('quantity', $qty);

            if ($order) {
                \App\Models\Stock::create([
                    'item_id'      => $item->id,
                    'voucher_no'   => $order->invoice_no ?: ('ORD-' . $order->id),
                    'voucher_type' => \App\Models\Stock::TYPE_SALE,
                    'voucher_date' => $order->created_at ? $order->created_at->toDateString() : now()->toDateString(),
                    'customer_id'  => $order->customer_id,
                    'branch_id'    => $order->branch_id,
                    'qty'          => $qty,
                    'rate'         => $avgCost,
                    'amount'       => round($qty * $avgCost, 2),
                    'status'       => 1,
                    'notes'        => 'Sale Invoice #' . ($order->invoice_no ?: $order->id),
                ]);
            }
        }
    }
}
