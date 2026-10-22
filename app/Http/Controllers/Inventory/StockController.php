<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the 1-row-per-item stock summary report.
     */
    public function index(Request $request): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('stocks.index', compact('branches', 'categories'));
    }

    /**
     * AJAX endpoint returning 1-row-per-item stock summary data with branch filtering.
     */
    public function data(Request $request): JsonResponse
    {
        $branchId = $request->filled('branch_id') && $request->branch_id !== 'all' ? (int) $request->branch_id : null;
        $categoryId = $request->filled('category_id') && $request->category_id !== 'all' ? (int) $request->category_id : null;
        $search = trim($request->get('search', ''));

        // Load active branches
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Query products
        $productQuery = Product::with('category')
            ->where('status', true);

        if ($categoryId) {
            $productQuery->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $productQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $productQuery->orderBy('name')->get();

        // Fetch all stock movements for these products
        $productIds = $products->pluck('id')->toArray();
        $stockMovements = DB::table('stocks')
            ->whereIn('item_id', $productIds)
            ->select('item_id', 'branch_id', 'from_branch_id', 'to_branch_id', 'voucher_type', 'qty', 'rate', 'amount')
            ->get();

        // Calculate branch-wise and total stock for each product
        $movementsByItem = $stockMovements->groupBy('item_id');

        $rows = [];
        $totalStockQty = 0;
        $totalValuation = 0;
        $lowStockCount = 0;

        foreach ($products as $idx => $product) {
            $itemMovements = $movementsByItem->get($product->id, collect());

            // Calculate stock per branch from movements
            $branchStocks = [];
            $computedTotalQty = 0;

            foreach ($branches as $branch) {
                $bId = $branch->id;
                $incoming = $itemMovements->filter(function ($m) use ($bId) {
                    return ($m->branch_id == $bId && in_array((int)$m->voucher_type, [Stock::TYPE_OPENING, Stock::TYPE_PURCHASE, Stock::TYPE_SALE_RETURN]))
                        || ($m->to_branch_id == $bId && (int)$m->voucher_type === Stock::TYPE_STOCK_TRANSFER);
                })->sum('qty');

                $outgoing = $itemMovements->filter(function ($m) use ($bId) {
                    return ($m->branch_id == $bId && in_array((int)$m->voucher_type, [Stock::TYPE_PURCHASE_RETURN, Stock::TYPE_SALE]))
                        || ($m->from_branch_id == $bId && (int)$m->voucher_type === Stock::TYPE_STOCK_TRANSFER);
                })->sum('qty');

                $bQty = (float) max(0, $incoming - $outgoing);
                $branchStocks[$bId] = [
                    'branch_id'   => $bId,
                    'branch_name' => $branch->name,
                    'qty'         => $bQty,
                ];
                $computedTotalQty += $bQty;
            }

            // If the item had no ledger entries yet, use product->quantity as general/first branch stock
            if ($itemMovements->isEmpty()) {
                $productInitialQty = (float) ($product->quantity ?? 0);
                $computedTotalQty = $productInitialQty;
                if ($branches->isNotEmpty()) {
                    $firstBranchId = $branches->first()->id;
                    $branchStocks[$firstBranchId]['qty'] = $productInitialQty;
                }
            }

            // In-stock quantity for display: if branch filter is applied, show that branch's stock, else total
            $displayQty = $branchId ? ($branchStocks[$branchId]['qty'] ?? 0) : $computedTotalQty;
            $unitCost = (float) ($product->purchase_price > 0 ? $product->purchase_price : ($product->price > 0 ? $product->price : 0));
            $valuation = round($displayQty * $unitCost, 2);

            if ($displayQty <= 5) {
                $lowStockCount++;
            }

            $totalStockQty += $displayQty;
            $totalValuation += $valuation;

            $rows[] = [
                's_no'           => $idx + 1,
                'id'             => $product->id,
                'name'           => $product->name,
                'barcode'        => $product->barcode ?: '—',
                'category_name'  => $product->category?->name ?: 'General',
                'in_stock_qty'   => (float) $displayQty,
                'total_qty'      => (float) $computedTotalQty,
                'branch_stocks'  => array_values($branchStocks),
                'cost_rate'      => $unitCost,
                'valuation'      => $valuation,
            ];
        }

        return response()->json([
            'data'  => $rows,
            'stats' => [
                'total_items'     => count($rows),
                'total_qty'       => (float) $totalStockQty,
                'total_valuation' => (float) $totalValuation,
                'low_stock_count' => $lowStockCount,
            ],
            'selected_branch_id' => $branchId,
        ]);
    }

    /**
     * AJAX endpoint returning the detailed stock ledger (Inventory Card) for an item
     * Formatted exactly matching Image 2:
     * Voucher | Voucher Type (in red) | Voucher Date | Ware House | QTY | Rate. | Amount | Balance QTY | Balance Amount | Average Rate | Amount
     */
    public function ledger(Request $request, Product $product): JsonResponse
    {
        $branchId = $request->filled('branch_id') && $request->branch_id !== 'all' ? (int) $request->branch_id : null;

        $query = Stock::with(['branch', 'fromBranch', 'toBranch', 'customer', 'supplier'])
            ->where('item_id', $product->id)
            ->orderBy('voucher_date', 'asc')
            ->orderBy('id', 'asc');

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            });
        }

        $movements = $query->get();

        $runningBalanceQty = 0;
        $runningBalanceAmount = 0;
        $runningAverageRate = (float) ($product->purchase_price > 0 ? $product->purchase_price : 0);

        $records = [];

        foreach ($movements as $m) {
            $qty = (float) $m->qty;
            $rate = (float) $m->rate;
            $lineAmount = (float) ($m->amount > 0 ? $m->amount : ($qty * $rate));

            // Determine if transaction is incoming or outgoing
            $isIncoming = false;
            $isOutgoing = false;

            switch ((int) $m->voucher_type) {
                case Stock::TYPE_OPENING:
                    $voucherTypeName = 'Opening Balance';
                    $isIncoming = true;
                    break;
                case Stock::TYPE_PURCHASE:
                    $voucherTypeName = 'Goods Receipt Note';
                    $isIncoming = true;
                    break;
                case Stock::TYPE_PURCHASE_RETURN:
                    $voucherTypeName = 'Purchase Return Note';
                    $isOutgoing = true;
                    break;
                case Stock::TYPE_SALE:
                    $voucherTypeName = 'Sales / Goods Issue';
                    $isOutgoing = true;
                    break;
                case Stock::TYPE_SALE_RETURN:
                    $voucherTypeName = 'Sale Return Note';
                    $isIncoming = true;
                    break;
                case Stock::TYPE_STOCK_TRANSFER:
                    if ($branchId && $m->to_branch_id == $branchId) {
                        $voucherTypeName = 'Stock Transfer In';
                        $isIncoming = true;
                    } elseif ($branchId && $m->from_branch_id == $branchId) {
                        $voucherTypeName = 'Stock Transfer Out';
                        $isOutgoing = true;
                    } else {
                        $voucherTypeName = 'Stock Transfer';
                    }
                    break;
                default:
                    $voucherTypeName = 'Adjustment';
                    $isIncoming = true;
                    break;
            }

            // Running balance calculation (Weighted Average Cost formula matching Image 2)
            if ($isIncoming) {
                $runningBalanceQty += $qty;
                $runningBalanceAmount += $lineAmount;
                if ($runningBalanceQty > 0) {
                    $runningAverageRate = round($runningBalanceAmount / $runningBalanceQty, 4);
                }
            } elseif ($isOutgoing) {
                $runningBalanceQty = max(0, $runningBalanceQty - $qty);
                // When issuing/selling out, deduct at current average rate
                $deductAmount = round($qty * $runningAverageRate, 2);
                $runningBalanceAmount = max(0, $runningBalanceAmount - $deductAmount);
                if ($runningBalanceQty > 0) {
                    $runningAverageRate = round($runningBalanceAmount / $runningBalanceQty, 4);
                } else {
                    $runningBalanceAmount = 0;
                }
            }

            $currentValuation = round($runningBalanceQty * $runningAverageRate, 2);

            // Warehouse / Branch display
            $warehouse = '';
            if ($m->branch) {
                $warehouse = $m->branch->name;
            } elseif ($m->fromBranch && $m->toBranch) {
                $warehouse = $m->fromBranch->name . ' → ' . $m->toBranch->name;
            }

            $records[] = [
                'voucher'        => $m->voucher_no ?: ('#' . $m->id),
                'voucher_type'   => $voucherTypeName,
                'voucher_date'   => $m->voucher_date ? $m->voucher_date->format('d-m-Y') : '—',
                'warehouse'      => $warehouse,
                'qty'            => number_format($qty, 2),
                'rate'           => number_format($rate, 2),
                'amount'         => number_format($lineAmount, 2),
                'balance_qty'    => number_format($runningBalanceQty, 2),
                'balance_amount' => number_format($runningBalanceAmount, 2),
                'average_rate'   => number_format($runningAverageRate, 3),
                'running_amount' => number_format($currentValuation, 2),
            ];
        }

        return response()->json([
            'product' => [
                'id'             => $product->id,
                'name'           => $product->name,
                'barcode'        => $product->barcode ?: '—',
                'category_name'  => $product->category?->name ?: 'General',
                'purchase_price' => (float) $product->purchase_price,
                'stock_qty'      => (float) $runningBalanceQty,
            ],
            'records' => $records,
        ]);
    }
}
