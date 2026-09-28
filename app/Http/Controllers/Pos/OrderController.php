<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Services\SrbInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $orders = Order::query()
            ->with(['items' => function($query) {
                $query->with(['product', 'service', 'deal']);
            }, 'payments', 'customer', 'branch', 'counter'])
            ->when($request->input('start_date'), function ($query, $startDate): void {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, string $endDate): void {
                $query->where('created_at', '<=', $endDate . ' 23:59:59');
            })
            ->latest()
            ->paginate(10);

        $total = $orders->sum(fn($order) => $order->total());
        $receivedAmount = $orders->sum(fn($order) => $order->receivedAmount());

        return view('orders.index', ['orders' => $orders, 'total' => $total, 'receivedAmount' => $receivedAmount]);
    }

    public function store(OrderStoreRequest $request, SrbInvoiceService $srbInvoiceService): \Illuminate\Http\JsonResponse
    {
        try {
            $order = DB::transaction(function () use ($request, $srbInvoiceService) {
                // Create order
                $subtotal = (float) $request->subtotal;
                $taxPercent = $request->tax_percent !== null
                    ? (float) $request->tax_percent
                    : ((bool) config('settings.tax_enabled', false) ? 8 : 0);
                $taxAmount = $request->tax_amount !== null
                    ? (float) $request->tax_amount
                    : $subtotal * $taxPercent / 100;
                $discountAmount = (float) ($request->discount_amount ?? 0);
                $order = Order::create([
                    'customer_id' => $request->customer_id,
                    'user_id'     => $request->user()->id,
                    'branch_id'   => $request->session()->get('pos_branch_id'),
                    'counter_id'  => $request->session()->get('pos_counter_id'),
                    'subtotal' => $subtotal,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'discount_percent' => $request->discount_percent ?? 0,
                    'discount_amount' => $discountAmount,
                    'total_amount' => max(0, $subtotal + $taxAmount - $discountAmount),
                ]);
                $dailyInvoiceNumber = Order::whereDate('created_at', now()->toDateString())
                    ->lockForUpdate()
                    ->count();
                $order->update(['invoice_no' => 'POS-' . str_pad((string) max(1, $dailyInvoiceNumber), 4, '0', STR_PAD_LEFT)]);

                // Get cart items
                $cartItems = $this->cartItems($request->user()->id);

                if ($cartItems->isEmpty()) {
                    throw new \Exception(__('cart.empty'));
                }

                // Create order items and update product quantities
                foreach ($cartItems as $item) {
                    $this->createOrderItem($order, $item, $request->input('item_rates', []));
                    $this->reduceProductStock($item);
                }

                // Clear cart
                DB::table('user_cart')->where('user_id', $request->user()->id)->delete();

                // Create payment
                $order->payments()->create([
                    'amount' => $request->amount,
                    'user_id' => $request->user()->id,
                ]);

                if ((bool) config('settings.srb_enabled', false)) {
                    $srbInvoiceService->submit($order->load('customer'));
                }

                return $order->refresh();
            });

            return response()->json([
                'success' => true,
                'message' => __('order.created_successfully'),
                'order_id' => $order->id,
                'order' => $order->load(['items' => function($query) {
                    $query->with(['product', 'service', 'deal']);
                }, 'payments', 'customer', 'user', 'branch', 'counter']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function partialPayment(Request $request)
    {
        $order = Order::findOrFail($request->input('order_id'));
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
     * Reduce product stock based on cart quantity.
     */
    private function reduceProductStock($item): void
    {
        if ($item instanceof \App\Models\Product) {
            $item->decrement('quantity', $item->pivot->quantity);
        }
    }
}
