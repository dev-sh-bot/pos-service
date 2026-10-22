<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\PurchaseStoreRequest;
use App\Http\Requests\Purchase\PurchaseUpdateRequest;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:purchases.view')->only(['index', 'data', 'show', 'receipt', 'returnReceipt', 'returnsList']);
        $this->middleware('can:purchases.create')->only(['create', 'store', 'showReturn', 'processReturn']);
        $this->middleware('can:purchases.edit')->only(['update']);
        $this->middleware('can:purchases.delete')->only(['destroy']);
    }

    /**
     * Display a listing of purchases with filters
     */
    public function index(Request $request): View
    {
        $purchases = Purchase::with(['supplier', 'user', 'branch', 'items.product', 'returns'])
            ->filter($request->only(['status', 'supplier_id', 'date_from', 'date_to', 'search']))
            ->orderBy($request->get('sort_by', 'purchase_date'), $request->get('sort_order', 'desc'))
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::orderBy('first_name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'branches'  => $branches,
        ]);
    }

    /**
     * Get purchases data as JSON for AJAX filtering
     */
    public function data(Request $request): JsonResponse
    {
        try {
            $purchases = Purchase::with(['supplier', 'user', 'branch', 'items.product', 'returns'])
                ->withCount('items')
                ->filter($request->only(['status', 'supplier_id', 'date_from', 'date_to', 'search']))
                ->orderBy($request->get('sort_by', 'purchase_date'), $request->get('sort_order', 'desc'))
                ->paginate(15);

            return response()->json($purchases);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Show the POS-style form for creating a new purchase
     */
    public function create(): View
    {
        $suppliers = Supplier::orderBy('first_name')->get();
        $categories = Category::where('status', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->with(['counters' => fn($q) => $q->where('is_active', true)->orderBy('name')])->orderBy('name')->get();

        return view('purchases.create', [
            'suppliers'  => $suppliers,
            'categories' => $categories,
            'branches'   => $branches,
        ]);
    }

    /**
     * Store a newly created purchase (supports JSON POS submission & Form redirect)
     */
    public function store(PurchaseStoreRequest $request): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            // Auto-generate purchase_no: PUR-YYYYMMDD-001
            $dateStr = date('Ymd', strtotime($request->purchase_date ?: now()->toDateString()));
            $countToday = Purchase::whereDate('created_at', now()->toDateString())->count();
            $purchaseNo = 'PUR-' . $dateStr . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);
            while (Purchase::where('purchase_no', $purchaseNo)->exists()) {
                $countToday++;
                $purchaseNo = 'PUR-' . $dateStr . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);
            }

            // Calculations
            $subtotal = (float) ($request->subtotal ?? 0);
            if ($subtotal <= 0) {
                $subtotal = collect($request->items)->sum(fn($i) => (float)$i['quantity'] * (float)$i['purchase_price']);
            }
            $taxPercent      = max(0, (float) ($request->tax_percent ?? 0));
            $taxAmount       = (float) ($request->tax_amount !== null ? $request->tax_amount : ($subtotal * $taxPercent / 100));
            $discountPercent = max(0, (float) ($request->discount_percent ?? 0));
            $discountAmount  = (float) ($request->discount_amount ?? 0);
            $totalAmount     = max(0, (float) ($request->total_amount ?? ($subtotal + $taxAmount - $discountAmount)));
            $status          = $request->status ?? 'completed';

            // Branch determination
            $branchId = $request->branch_id ?? session('pos_branch_id');
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

            $counterId = $request->counter_id ?? session('pos_counter_id');
            if (!$counterId && $branchId) {
                $counter = \App\Models\Counter::where('branch_id', $branchId)->first();
                if ($counter) {
                    $counterId = $counter->id;
                }
            }

            // Create purchase
            $purchase = Purchase::create([
                'purchase_no'      => $purchaseNo,
                'supplier_id'      => $request->supplier_id,
                'user_id'          => Auth::id() ?? 1,
                'branch_id'        => $branchId,
                'counter_id'       => $counterId,
                'purchase_date'    => $request->purchase_date,
                'subtotal'         => $subtotal,
                'tax_percent'      => $taxPercent,
                'tax_amount'       => $taxAmount,
                'discount_percent' => $discountPercent,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $totalAmount,
                'payment_method'   => $request->payment_method ?? 'cash',
                'status'           => $status,
                'notes'            => $request->notes,
            ]);

            // Create purchase items & stock ledger entries
            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                ]);

                // When purchase is completed, increase stock and record in stocks ledger (voucher_type = 1)
                if ($status === 'completed') {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->quantity += (int) $item['quantity'];
                        $product->purchase_price = (float) $item['purchase_price'];
                        $product->save();

                        Stock::create([
                            'item_id'      => $product->id,
                            'voucher_no'   => $purchase->purchase_no,
                            'voucher_type' => Stock::TYPE_PURCHASE, // 1: Purchase
                            'voucher_date' => $purchase->purchase_date,
                            'supplier_id'  => $purchase->supplier_id,
                            'branch_id'    => $purchase->branch_id,
                            'qty'          => (float) $item['quantity'],
                            'rate'         => (float) $item['purchase_price'],
                            'amount'       => round((float) $item['quantity'] * (float) $item['purchase_price'], 2),
                            'status'       => 1,
                            'notes'        => 'Purchase ' . $purchase->purchase_no,
                        ]);
                    }
                }
            }

            DB::commit();

            // Clear purchase cart
            if ($request->user()) {
                $request->user()->purchaseCart()->detach();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'     => true,
                    'message'     => __('Purchase created successfully!'),
                    'purchase_id' => $purchase->id,
                    'purchase'    => $purchase->fresh(['supplier', 'user', 'branch', 'items.product']),
                ], 201);
            }

            return redirect()->route('purchases.index')
                ->with('success', __('Purchase created successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to create purchase: ') . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', __('Failed to create purchase: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'branch', 'items.product', 'returns.items.product']);

        return view('purchases.show', ['purchase' => $purchase]);
    }

    /**
     * Show the Purchase Return form for a specific purchase
     */
    public function showReturn(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'branch', 'items.product', 'returns.items']);

        // Calculate already returned quantities
        $returnedQuantities = PurchaseReturnItem::whereIn('purchase_item_id', $purchase->items->pluck('id'))
            ->groupBy('purchase_item_id')
            ->selectRaw('purchase_item_id, SUM(quantity) as total_returned')
            ->pluck('total_returned', 'purchase_item_id')
            ->all();

        $itemsWithAvailable = $purchase->items->map(function ($item) use ($returnedQuantities) {
            $alreadyReturned = (float) ($returnedQuantities[$item->id] ?? 0);
            $availableQty = max(0, (float) $item->quantity - $alreadyReturned);

            return [
                'id'               => $item->id,
                'product_id'       => $item->product_id,
                'product_name'     => $item->product?->name ?? 'Product',
                'barcode'          => $item->product?->barcode ?? '',
                'current_stock'    => $item->product?->quantity ?? 0,
                'purchased_qty'    => (float) $item->quantity,
                'already_returned' => $alreadyReturned,
                'available_qty'    => $availableQty,
                'purchase_price'   => (float) $item->purchase_price,
            ];
        });

        return view('purchases.return', [
            'purchase'            => $purchase,
            'itemsWithAvailable'  => $itemsWithAvailable,
        ]);
    }

    /**
     * Process and record a Purchase Return
     */
    public function processReturn(Request $request, Purchase $purchase): RedirectResponse|JsonResponse
    {
        $request->validate([
            'return_date'          => 'required|date',
            'refund_method'        => 'required|string',
            'return_reason'        => 'nullable|string',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.quantity'     => 'required|numeric|min:0.01',
            'items.*.return_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchase->load('items.product');

            // Auto-generate return number: PRET-YYYYMMDD-001
            $dateStr = date('Ymd', strtotime($request->return_date));
            $dailyCount = PurchaseReturn::whereDate('created_at', now()->toDateString())->count();
            $returnNo = 'PRET-' . $dateStr . '-' . str_pad((string)($dailyCount + 1), 3, '0', STR_PAD_LEFT);
            while (PurchaseReturn::where('return_no', $returnNo)->exists()) {
                $dailyCount++;
                $returnNo = 'PRET-' . $dateStr . '-' . str_pad((string)($dailyCount + 1), 3, '0', STR_PAD_LEFT);
            }

            $subtotalRefund = 0;
            $validReturnItems = [];

            foreach ($request->items as $inputItem) {
                $pItem = $purchase->items->firstWhere('id', $inputItem['purchase_item_id']);
                if (!$pItem) {
                    continue;
                }

                $alreadyReturned = (float) PurchaseReturnItem::where('purchase_item_id', $pItem->id)->sum('quantity');
                $availableQty = max(0, (float) $pItem->quantity - $alreadyReturned);
                $returnQty = min((float) $inputItem['quantity'], $availableQty);

                if ($returnQty <= 0) {
                    continue;
                }

                $returnPrice = (float) ($inputItem['return_price'] ?? $pItem->purchase_price);
                $lineTotal = round($returnQty * $returnPrice, 2);
                $subtotalRefund += $lineTotal;

                $validReturnItems[] = [
                    'purchase_item_id' => $pItem->id,
                    'product_id'       => $pItem->product_id,
                    'quantity'         => $returnQty,
                    'return_price'     => $returnPrice,
                    'total_amount'     => $lineTotal,
                ];
            }

            if (empty($validReturnItems)) {
                throw new \Exception(__('No valid return quantities specified.'));
            }

            // Create PurchaseReturn
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id'     => $purchase->id,
                'supplier_id'     => $purchase->supplier_id,
                'user_id'         => Auth::id() ?? 1,
                'branch_id'       => $purchase->branch_id,
                'return_no'       => $returnNo,
                'return_date'     => $request->return_date,
                'subtotal'        => $subtotalRefund,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'total_amount'    => $subtotalRefund,
                'refund_method'   => $request->refund_method,
                'return_reason'   => $request->return_reason,
                'notes'           => $request->notes,
            ]);

            // Create PurchaseReturnItems & record in stocks table (voucher_type = 2: Purchase Return)
            foreach ($validReturnItems as $vItem) {
                $purchaseReturn->items()->create($vItem);

                // Decrease product stock
                $product = Product::find($vItem['product_id']);
                if ($product) {
                    $product->decrement('quantity', $vItem['quantity']);

                    // Insert into stocks ledger: voucher_type = 2
                    Stock::create([
                        'item_id'      => $product->id,
                        'voucher_no'   => $purchaseReturn->return_no,
                        'voucher_type' => Stock::TYPE_PURCHASE_RETURN, // 2: Purchase Return
                        'voucher_date' => $purchaseReturn->return_date,
                        'supplier_id'  => $purchaseReturn->supplier_id,
                        'branch_id'    => $purchaseReturn->branch_id,
                        'qty'          => (float) $vItem['quantity'],
                        'rate'         => (float) $vItem['return_price'],
                        'amount'       => (float) $vItem['total_amount'],
                        'status'       => 1,
                        'notes'        => 'Purchase Return ' . $purchaseReturn->return_no,
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => __('Purchase return created successfully!'),
                    'return_id' => $purchaseReturn->id,
                    'return_no' => $purchaseReturn->return_no,
                ]);
            }

            return redirect()->route('purchases.returns')
                ->with('success', "Purchase return {$returnNo} processed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', __('Failed to process purchase return: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display listing of all purchase returns
     */
    public function returnsList(Request $request): View
    {
        $query = PurchaseReturn::with(['purchase', 'supplier', 'user', 'branch', 'items.product'])
            ->latest('return_date')
            ->latest('id');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_from')) {
            $query->where('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('return_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('first_name')->get();

        return view('purchases.returns_list', compact('returns', 'suppliers'));
    }

    /**
     * Generate 80mm thermal receipt PDF for Purchase Return
     */
    public function returnReceipt(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'supplier', 'user', 'branch', 'items.product']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('purchases.return_receipt', ['purchaseReturn' => $purchaseReturn]);
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream("purchase-return-receipt-{$purchaseReturn->id}.pdf");
    }

    /**
     * Update the specified purchase
     */
    public function update(PurchaseUpdateRequest $request, Purchase $purchase): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $oldStatus = $purchase->status;
            $newStatus = $request->status;

            // Update purchase
            $purchase->update([
                'supplier_id'   => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'total_amount'  => $request->total_amount,
                'status'        => $newStatus,
                'notes'         => $request->notes,
            ]);

            // Handle stock changes based on status transition
            if ($oldStatus !== $newStatus) {
                foreach ($purchase->items as $item) {
                    $product = $item->product;

                    if ($oldStatus === 'completed' && in_array($newStatus, ['pending', 'cancelled'])) {
                        $product->quantity -= $item->quantity;
                        $product->save();
                    }

                    if (in_array($oldStatus, ['pending', 'cancelled']) && $newStatus === 'completed') {
                        $product->quantity += $item->quantity;
                        $product->purchase_price = $item->purchase_price;
                        $product->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', __('Purchase updated successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Failed to update purchase: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        try {
            DB::beginTransaction();

            if ($purchase->status === 'completed') {
                foreach ($purchase->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->quantity -= $item->quantity;
                        $product->save();
                    }
                }
                // Void stock entries for this purchase
                Stock::where('voucher_no', $purchase->purchase_no)->delete();
            }

            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', __('Purchase deleted successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('Failed to delete purchase: ') . $e->getMessage());
        }
    }

    /**
     * Generate 80mm thermal receipt PDF for Purchase
     */
    public function receipt(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'branch', 'items.product']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('purchases.receipt', ['purchase' => $purchase]);
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->stream("purchase-receipt-{$purchase->id}.pdf");
    }
}
