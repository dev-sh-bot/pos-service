<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing of stock transfers.
     */
    public function index(Request $request): View
    {
        $query = Stock::with(['product.category', 'fromBranch', 'toBranch'])
            ->where('voucher_type', Stock::TYPE_STOCK_TRANSFER)
            ->latest('voucher_date')
            ->latest('id');

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_branch_id', $request->branch_id)
                  ->orWhere('to_branch_id', $request->branch_id);
            });
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('date_from')) {
            $query->where('voucher_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('voucher_date', '<=', $request->date_to);
        }

        $transfers = $query->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('stock_transfers.index', compact('transfers', 'branches', 'products'));
    }

    /**
     * Show form to create stock transfer.
     */
    public function create(): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', true)->with('category')->orderBy('name')->get();

        return view('stock_transfers.create', compact('branches', 'products'));
    }

    /**
     * Store newly created stock transfer.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id'   => 'required|exists:branches,id|different:from_branch_id',
            'item_id'        => 'required|exists:products,id',
            'quantity'       => 'required|numeric|min:0.01',
            'transfer_date'  => 'required|date',
            'notes'          => 'nullable|string|max:500',
        ], [
            'to_branch_id.different' => 'Destination branch must be different from source branch.',
            'item_id.required'       => 'Please select a product to transfer.',
            'quantity.required'      => 'Transfer quantity is required.',
        ]);

        $product = Product::findOrFail($request->item_id);

        if ((float) $product->quantity < (float) $request->quantity) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Insufficient stock! Available stock for {$product->name} is {$product->quantity}.");
        }

        try {
            DB::beginTransaction();

            $fromBranch = Branch::find($request->from_branch_id);
            $toBranch   = Branch::find($request->to_branch_id);

            // Auto-generate voucher number: TRF-YYYYMMDD-001
            $datePrefix = date('Ymd', strtotime($request->transfer_date));
            $dailyCount = Stock::where('voucher_type', Stock::TYPE_STOCK_TRANSFER)
                ->whereDate('voucher_date', $request->transfer_date)
                ->count();
            $voucherNo = 'TRF-' . $datePrefix . '-' . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

            while (Stock::where('voucher_no', $voucherNo)->exists()) {
                $dailyCount++;
                $voucherNo = 'TRF-' . $datePrefix . '-' . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);
            }

            $avgCost = Stock::calculateAverageCost($product->id);
            $amount  = round((float) $request->quantity * $avgCost, 2);

            Stock::create([
                'item_id'        => $product->id,
                'voucher_no'     => $voucherNo,
                'voucher_type'   => Stock::TYPE_STOCK_TRANSFER, // 5
                'voucher_date'   => $request->transfer_date,
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id'   => $request->to_branch_id,
                'branch_id'      => $request->to_branch_id,
                'qty'            => (float) $request->quantity,
                'rate'           => $avgCost,
                'amount'         => $amount,
                'status'         => 1,
                'notes'          => $request->notes ?: "Transfer from {$fromBranch->name} to {$toBranch->name}",
            ]);

            DB::commit();

            return redirect()->route('stock-transfers.index')
                ->with('success', "Stock transfer {$voucherNo} recorded successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to process stock transfer: ' . $e->getMessage());
        }
    }
}
