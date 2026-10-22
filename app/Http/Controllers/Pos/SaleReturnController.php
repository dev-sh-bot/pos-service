<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Services\SrbInvoiceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:sale_returns.view')->only(['index', 'posReturn']);
        $this->middleware('can:sale_returns.list')->only(['returnList']);
        $this->middleware('can:sale_returns.create')->only(['processReturn']);
    }

    private function ensureTablesExist(): void
    {
        if (!Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
                $table->string('return_no')->unique();
                $table->decimal('refund_subtotal', 10, 2)->default(0);
                $table->decimal('refund_tax', 10, 2)->default(0);
                $table->decimal('refund_discount', 10, 2)->default(0);
                $table->decimal('refund_total', 10, 2)->default(0);
                $table->string('refund_method')->default('cash');
                $table->string('return_reason')->nullable();
                $table->text('notes')->nullable();
                $table->string('srb_invoice_id')->nullable()->unique();
                $table->text('srb_qr_code_link')->nullable();
                $table->string('srb_status', 20)->nullable();
                $table->text('srb_response')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('sale_returns', 'srb_invoice_id')) {
                Schema::table('sale_returns', function (Blueprint $table) {
                    $table->string('srb_invoice_id')->nullable()->unique()->after('refund_total');
                    $table->text('srb_qr_code_link')->nullable()->after('srb_invoice_id');
                    $table->string('srb_status', 20)->nullable()->after('srb_qr_code_link');
                    $table->text('srb_response')->nullable()->after('srb_status');
                });
            }
            if (!Schema::hasColumn('sale_returns', 'card_holder_name')) {
                Schema::table('sale_returns', function (Blueprint $table) {
                    $table->string('card_holder_name')->nullable()->after('refund_method');
                    $table->string('card_last_four', 4)->nullable()->after('card_holder_name');
                    $table->unsignedBigInteger('receiving_account_id')->nullable()->after('card_last_four');
                });
            }
        }

        if (!Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedTinyInteger('item_type')->default(0); // 0=product, 1=service, 2=deal
                $table->string('item_name');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('refund_amount', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function index(Request $request): View
    {
        $this->ensureTablesExist();

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

        $query = Order::query()
            ->with([
                'items' => function ($q) {
                    $q->with([
                        'product',
                        'service.category',
                        'deal.services',
                    ]);
                },
                'payments',
                'customer',
                'branch',
                'counter',
                'user',
                'saleReturns.items'
            ])
            ->when($selectedBranchId, function ($query, $branchId): void {
                $query->where('branch_id', $branchId);
            }, function ($query) use ($isAdmin, $allowedBranchIds): void {
                if (! $isAdmin) {
                    $query->whereIn('branch_id', $allowedBranchIds);
                }
            });

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $totalsQuery = clone $query;
        $totalsQuery->getQuery()->orders = null;
        $totalOrders = (clone $query)->count();
        $totalSales = (float) $totalsQuery->sum('total_amount');

        return view('sale_returns.index', compact('orders', 'branches', 'selectedBranchId', 'isAdmin', 'totalOrders', 'totalSales'));
    }

    /**
     * Display list of all processed Sale Returns with branch filtering, search, and date range.
     */
    public function returnList(Request $request): View
    {
        $this->ensureTablesExist();

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

        $query = SaleReturn::query()
            ->with([
                'order',
                'customer',
                'branch',
                'counter',
                'user',
                'items',
                'receivingAccount',
            ])
            ->when($selectedBranchId, function ($q, $branchId): void {
                $q->where('branch_id', $branchId);
            }, function ($q) use ($isAdmin, $allowedBranchIds): void {
                if (! $isAdmin) {
                    $q->whereIn('branch_id', $allowedBranchIds);
                }
            });

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                    ->orWhere('srb_invoice_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('invoice_no', 'like', "%{$search}%")
                            ->orWhere('id', $search);
                    })
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date') . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date') . ' 23:59:59');
        }

        $totalReturns = (clone $query)->count();
        $totalsQuery = clone $query;
        $totalsQuery->getQuery()->orders = null;
        $totalRefunds = (float) $totalsQuery->sum('refund_total');

        $returns = $query->latest()->paginate(15)->withQueryString();

        return view('sale_returns.list', compact(
            'returns',
            'branches',
            'selectedBranchId',
            'isAdmin',
            'totalReturns',
            'totalRefunds'
        ));
    }

    /**
     * Display the POS Screen initialized for Sale Return on a specific Order.
     */
    public function posReturn(Request $request, $order): View
    {
        $this->ensureTablesExist();

        $user = $request->user();
        $isAdmin = (bool) ($user && $user->isSuperAdmin());

        if (! ($order instanceof Order)) {
            $order = Order::findOrFail($order);
        }

        $order->loadMissing([
            'items' => function ($q) {
                $q->with([
                    'product',
                    'service.category',
                    'deal.services',
                ]);
            },
            'customer',
            'branch',
            'counter',
            'user',
            'payments',
            'receivingAccount',
        ]);

        // Branch permission security check
        if (! $isAdmin && $user) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            if (! empty($allowedBranchIds) && $order->branch_id && ! in_array($order->branch_id, $allowedBranchIds)) {
                abort(403, 'Unauthorized access to this branch order.');
            }
        }

        // Set session pos_branch_id and pos_counter_id to match the order
        if ($order->branch_id) {
            session(['pos_branch_id' => $order->branch_id]);
        }
        if ($order->counter_id) {
            session(['pos_counter_id' => $order->counter_id]);
        }

        // Calculate already returned quantities for items in this order
        $returnedQuantities = SaleReturnItem::whereIn('order_item_id', $order->items->pluck('id'))
            ->groupBy('order_item_id')
            ->selectRaw('order_item_id, SUM(quantity) as returned_qty')
            ->pluck('returned_qty', 'order_item_id')
            ->all();

        // Transform items into rich POS return items array
        $returnItems = $order->items->map(function ($item) use ($returnedQuantities) {
            $itemType = (int) $item->item_type;
            $deal = $itemType === 2 ? $item->deal : null;
            $service = $itemType === 1 ? $item->service : null;
            $product = $itemType === 0 ? $item->product : null;

            $isDeal = $itemType === 2;
            $isService = $itemType === 1;

            $type = $isDeal ? 'deal' : ($isService ? 'service' : 'product');
            $typeLabel = $isDeal ? 'Deal Package' : ($isService ? 'Service' : 'Product');
            $name = $item->item_name ?: ($deal?->name ?? $service?->name ?? $product?->name ?? 'Item');

            $dealServices = [];
            if ($isDeal && $deal) {
                $dealServices = $deal->services->map(fn($s) => [
                    'name' => $s->name,
                    'rate' => number_format((float) $s->rate, 2),
                ])->values()->toArray();
            }

            $purchasedQty = max(1, (int) $item->quantity);
            $alreadyReturned = (int) ($returnedQuantities[$item->id] ?? 0);
            $availableQty = max(0, $purchasedQty - $alreadyReturned);
            $unitPrice = (float) ($item->price / $purchasedQty);
            $isFullyReturned = ($availableQty <= 0);

            return [
                'order_item_id' => $item->id,
                'item_id'       => $item->item_id,
                'item_type'     => $itemType,
                'type'          => $type,
                'type_label'    => $typeLabel,
                'name'          => $name,
                'category'      => $service?->category?->name ?? null,
                'purchased_qty' => $purchasedQty,
                'already_returned' => $alreadyReturned,
                'original_qty'  => $availableQty, // Available returnable quantity
                'return_qty'    => $availableQty,
                'unit_price'    => $unitPrice,
                'line_total'    => (float) ($unitPrice * $availableQty),
                'is_deal'       => $isDeal,
                'deal_services' => $dealServices,
                'barcode'       => $deal?->barcode ?? $service?->barcode ?? $product?->barcode ?? '',
                'is_fully_returned' => $isFullyReturned,
            ];
        });

        $hasReturnableItems = $returnItems->contains(fn($item) => ! $item['is_fully_returned']);
        $orderTypeInfo = $order->getOrderTypeInfo();

        $paymentAccounts = PaymentMethod::where('status', true)
            ->where('type', '!=', 'cash')
            ->orderByRaw("FIELD(type, 'bank', 'card', 'wallet', 'other')")
            ->orderBy('name')
            ->get();

        return view('sale_returns.pos', compact('order', 'returnItems', 'orderTypeInfo', 'hasReturnableItems', 'paymentAccounts'));
    }

    /**
     * Process and record the Sale Return in database.
     */
    public function processReturn(Request $request, SrbInvoiceService $srbInvoiceService): JsonResponse
    {
        $this->ensureTablesExist();

        $request->validate([
            'order_id'             => 'required|exists:orders,id',
            'items'                => 'required|array|min:1',
            'items.*.order_item_id'=> 'required|exists:order_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'refund_method'        => 'required|string',
            'card_holder_name'     => 'nullable|string|max:255',
            'card_last_four'       => 'nullable|string|max:4',
            'receiving_account_id' => 'nullable|integer|exists:payment_methods,id',
            'return_reason'        => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        if (strtolower((string) $request->refund_method) === 'card') {
            $request->validate([
                'receiving_account_id' => 'required|integer|exists:payment_methods,id',
                'card_last_four'       => 'required|string|size:4',
            ], [
                'receiving_account_id.required' => 'Please select the bank account from which refund is being sent.',
                'receiving_account_id.exists'   => 'Selected bank account was not found.',
                'card_last_four.required'       => 'Please enter the last 4 digits of the card.',
                'card_last_four.size'           => 'Card last digits must be exactly 4 digits.',
            ]);
        }

        try {
            $saleReturn = DB::transaction(function () use ($request, $srbInvoiceService) {
                $order = Order::with('items')->findOrFail($request->order_id);

                // Auto-generate Return number: RET-0001
                $dailyReturnCount = SaleReturn::whereDate('created_at', now()->toDateString())->lockForUpdate()->count();
                $returnNo = 'RET-' . date('ymHI') . '-' . str_pad((string) ($dailyReturnCount + 1), 3, '0', STR_PAD_LEFT);

                $refundSubtotal = 0;
                $returnedItemsPayload = [];

                foreach ($request->input('items', []) as $returnedItemInput) {
                    $orderItem = $order->items->firstWhere('id', $returnedItemInput['order_item_id']);
                    if (!$orderItem) {
                        continue;
                    }

                    $alreadyReturned = (int) SaleReturnItem::where('order_item_id', $orderItem->id)->sum('quantity');
                    $availableQty = max(0, (int) $orderItem->quantity - $alreadyReturned);
                    $returnQty = min((int) $returnedItemInput['quantity'], $availableQty);
                    if ($returnQty <= 0) {
                        continue;
                    }

                    $unitPrice = (float) ($orderItem->price / max(1, $orderItem->quantity));
                    $itemRefund = $unitPrice * $returnQty;
                    $refundSubtotal += $itemRefund;

                    $returnedItemsPayload[] = [
                        'order_item_id' => $orderItem->id,
                        'item_id'       => $orderItem->item_id,
                        'item_type'     => $orderItem->item_type,
                        'item_name'     => $orderItem->item_name ?: 'Item',
                        'quantity'      => $returnQty,
                        'unit_price'    => $unitPrice,
                        'refund_amount' => $itemRefund,
                    ];

                    // If physical product, restore stock inventory
                    if ($orderItem->item_type === 0 && $orderItem->item_id) {
                        $product = Product::find($orderItem->item_id);
                        if ($product) {
                            $product->increment('quantity', $returnQty);
                        }
                    }
                }

                if (empty($returnedItemsPayload)) {
                    throw new \Exception('No valid items were selected for return.');
                }

                // Pro-rate tax and discount if applicable
                $taxPercent = (float) ($order->tax_percent ?? 0);
                $refundTax = $taxPercent > 0 ? ($refundSubtotal * $taxPercent / 100) : 0;

                $discountPercent = (float) ($order->discount_percent ?? 0);
                $refundDiscount = $discountPercent > 0 ? ($refundSubtotal * $discountPercent / 100) : 0;

                $refundTotal = max(0, $refundSubtotal + $refundTax - $refundDiscount);

                $isCard = strtolower((string) $request->input('refund_method', 'cash')) === 'card';
                $cardHolderName = $isCard ? trim((string) $request->input('card_holder_name', '')) : null;
                $cardLastFour = $isCard ? trim((string) $request->input('card_last_four', '')) : null;
                $receivingAccountId = $isCard ? $request->input('receiving_account_id') : null;

                // Create SaleReturn
                $saleReturn = SaleReturn::create([
                    'order_id'             => $order->id,
                    'customer_id'          => $order->customer_id,
                    'user_id'              => auth()->id() ?? $order->user_id,
                    'branch_id'            => session('pos_branch_id') ?? $order->branch_id,
                    'counter_id'           => session('pos_counter_id') ?? $order->counter_id,
                    'return_no'            => $returnNo,
                    'refund_subtotal'      => $refundSubtotal,
                    'refund_tax'           => $refundTax,
                    'refund_discount'      => $refundDiscount,
                    'refund_total'         => $refundTotal,
                    'refund_method'        => $request->input('refund_method', 'cash'),
                    'card_holder_name'     => $cardHolderName,
                    'card_last_four'       => $cardLastFour,
                    'receiving_account_id' => $receivingAccountId,
                    'return_reason'        => $request->input('return_reason'),
                    'notes'                => $request->input('notes'),
                    'created_at'           => Carbon::now('Asia/Karachi'),
                ]);

                // Create SaleReturnItems and record in stocks ledger
                foreach ($returnedItemsPayload as $ritem) {
                    $saleReturn->items()->create($ritem);

                    if ((int) $ritem['item_type'] === 0 && !empty($ritem['item_id'])) {
                        $avgCost = \App\Models\Stock::calculateAverageCost($ritem['item_id']);
                        \App\Models\Stock::create([
                            'item_id'      => $ritem['item_id'],
                            'voucher_no'   => $saleReturn->return_no,
                            'voucher_type' => \App\Models\Stock::TYPE_SALE_RETURN,
                            'voucher_date' => $saleReturn->created_at ? $saleReturn->created_at->toDateString() : now()->toDateString(),
                            'customer_id'  => $saleReturn->customer_id,
                            'branch_id'    => $saleReturn->branch_id,
                            'qty'          => (float) $ritem['quantity'],
                            'rate'         => $avgCost,
                            'amount'       => round((float) $ritem['quantity'] * $avgCost, 2),
                            'status'       => 1,
                            'notes'        => 'Sale Return #' . $saleReturn->return_no,
                        ]);
                    }
                }

                // Record negative refund payment on order
                $order->payments()->create([
                    'amount'               => -$refundTotal,
                    'user_id'              => auth()->id() ?? $order->user_id,
                    'payment_method'       => strtolower((string) $request->input('refund_method', 'cash')),
                    'card_holder_name'     => $cardHolderName,
                    'card_last_four'       => $cardLastFour,
                    'receiving_account_id' => $receivingAccountId,
                ]);

                return $saleReturn;
            });

            // Submit return to SRB outside the database transaction so refund records are never rolled back
            if ((bool) config('settings.srb_enabled', false)) {
                try {
                    $srbInvoiceService->submitReturn($saleReturn);
                } catch (\Throwable $srbEx) {
                    \Illuminate\Support\Facades\Log::warning("SRB return submission warning for return #{$saleReturn->id}: " . $srbEx->getMessage());
                }
            }

            $saleReturn = $saleReturn->fresh(['items', 'customer', 'branch', 'counter', 'user', 'order', 'receivingAccount']);

            return response()->json([
                'success'          => true,
                'message'          => 'Sale return processed successfully! Refund Amount: ' . config('settings.currency_symbol', 'PKR') . ' ' . number_format($saleReturn->refund_total, 2),
                'return_no'        => $saleReturn->return_no,
                'refund_total'     => number_format($saleReturn->refund_total, 2),
                'sale_return'      => $saleReturn,
                'card_holder_name' => $saleReturn->card_holder_name,
                'card_last_four'   => $saleReturn->card_last_four,
                'receiving_account'=> $saleReturn->receivingAccount?->name,
                'srb_invoice_id'   => $saleReturn->srb_invoice_id,
                'srb_qr_code_link' => $saleReturn->srb_qr_code_link,
                'redirect'         => route('sale-returns.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage(),
            ], 422);
        }
    }
}
