<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpeningStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return JSON data for AJAX-based opening stock listing and live filtering.
     */
    public function data(Request $request): JsonResponse
    {
        $query = Stock::with(['product.category', 'branch'])
            ->where('voucher_type', Stock::TYPE_OPENING)
            ->latest('voucher_date')
            ->latest('id');

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
        }

        // Summary KPI stats computed on filtered set
        $totalVouchers = (clone $query)->distinct('voucher_no')->count('voucher_no');
        $totalItems = (clone $query)->count();
        $totalQty = (float) (clone $query)->sum('qty');
        $totalAmount = (float) (clone $query)->sum('amount');

        $perPage = (int) ($request->get('per_page', 20));
        $paginator = $query->paginate($perPage);

        $currency = config('settings.currency_symbol', 'Rs.');

        $rows = collect($paginator->items())->map(function ($stock, $idx) use ($paginator, $currency) {
            $vDate = $stock->voucher_date ? ($stock->voucher_date instanceof \DateTimeInterface ? $stock->voucher_date->format('d/m/Y') : \Carbon\Carbon::parse($stock->voucher_date)->format('d/m/Y')) : '—';
            return [
                'index'        => $paginator->firstItem() + $idx,
                'id'           => $stock->id,
                'voucher_no'   => $stock->voucher_no,
                'voucher_date' => $vDate,
                'product_id'   => $stock->item_id,
                'product_name' => $stock->product?->name ?: ('Item #' . $stock->item_id),
                'product_code' => $stock->product?->barcode ?: '',
                'category_name'=> $stock->product?->category?->name ?: '',
                'branch_id'    => $stock->branch_id,
                'branch_name'  => $stock->branch?->name ?: '—',
                'qty'          => (float) $stock->qty,
                'qty_formatted'=> '+' . number_format((float) $stock->qty, 2),
                'rate'         => (float) $stock->rate,
                'rate_formatted' => $currency . number_format((float) $stock->rate, 2),
                'amount'       => (float) $stock->amount,
                'amount_formatted' => $currency . number_format((float) $stock->amount, 2),
                'notes'        => $stock->notes ?: '—',
                'show_url'     => route('opening-stocks.show', $stock->voucher_no),
            ];
        });

        return response()->json([
            'success'    => true,
            'rows'       => $rows,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'kpi' => [
                'total_vouchers' => $totalVouchers,
                'total_items'    => $totalItems,
                'total_qty'      => number_format($totalQty, 2),
                'total_amount'   => $currency . number_format($totalAmount, 2),
            ],
        ]);
    }

    /**
     * Display a listing of opening stock vouchers.
     */
    public function index(Request $request): View
    {
        $query = Stock::with(['product.category', 'branch'])
            ->where('voucher_type', Stock::TYPE_OPENING)
            ->latest('voucher_date')
            ->latest('id');

        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
                  });
            });
        }

        // Summary KPI stats
        $totalVouchers = (clone $query)->distinct('voucher_no')->count('voucher_no');
        $totalItems = (clone $query)->count();
        $totalQty = (float) (clone $query)->sum('qty');
        $totalAmount = (float) (clone $query)->sum('amount');

        $openingStocks = $query->paginate(20)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('opening_stocks.index', compact(
            'openingStocks',
            'branches',
            'products',
            'totalVouchers',
            'totalItems',
            'totalQty',
            'totalAmount'
        ));
    }

    /**
     * Show the multi-row form to create opening inventory.
     */
    public function create(): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $todayDate = now()->toDateString();
        $dateStr = date('Ymd');
        $countToday = Stock::where('voucher_type', Stock::TYPE_OPENING)
            ->whereDate('created_at', now()->toDateString())
            ->distinct('voucher_no')
            ->count('voucher_no');

        $suggestedVoucherNo = 'OPN-' . $dateStr . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);

        return view('opening_stocks.create', compact('branches', 'products', 'todayDate', 'suggestedVoucherNo'));
    }

    /**
     * Store newly created opening inventory items (only submits into the stocks table).
     */
    public function store(Request $request): RedirectResponse
    {
        // Filter out blank rows where item_id is not selected or qty is empty
        $rawItems = $request->input('items', []);
        if (is_array($rawItems)) {
            $filteredItems = array_values(array_filter($rawItems, function ($item) {
                return !empty($item['item_id']) && isset($item['qty']) && (float) $item['qty'] > 0;
            }));
            $request->merge(['items' => $filteredItems]);
        }

        $request->validate([
            'voucher_date'        => 'required|date',
            'default_branch_id'   => 'required|exists:branches,id',
            'items'               => 'required|array|min:1',
            'items.*.item_id'     => 'required|exists:products,id',
            'items.*.branch_id'   => 'required|exists:branches,id',
            'items.*.qty'         => 'required|numeric|min:0.001',
            'items.*.rate'        => 'nullable|numeric|min:0',
            'items.*.amount'      => 'nullable|numeric|min:0',
            'items.*.notes'       => 'nullable|string|max:255',
            'notes'               => 'nullable|string|max:500',
        ], [
            'items.required'      => 'Please add at least one opening inventory item.',
            'items.min'           => 'Please add at least one opening inventory item.',
            'items.*.item_id.required'   => 'Product item is required for each row.',
            'items.*.branch_id.required' => 'Branch is required for each row.',
            'items.*.qty.required'       => 'Closing quantity is required.',
            'items.*.qty.min'            => 'Quantity must be greater than zero.',
        ]);

        try {
            DB::beginTransaction();

            // Auto-generate voucher number: OPN-YYYYMMDD-001
            $voucherDate = $request->voucher_date ?: now()->toDateString();
            $dateStr = date('Ymd', strtotime($voucherDate));

            $countToday = Stock::where('voucher_type', Stock::TYPE_OPENING)
                ->whereDate('voucher_date', $voucherDate)
                ->distinct('voucher_no')
                ->count('voucher_no');

            $voucherNo = 'OPN-' . $dateStr . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);
            while (Stock::where('voucher_no', $voucherNo)->exists()) {
                $countToday++;
                $voucherNo = 'OPN-' . $dateStr . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);
            }

            $submittedCount = 0;
            $batchNotes = $request->notes ?: 'Opening Inventory Setup';

            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                $rate = isset($item['rate']) && $item['rate'] !== '' ? (float) $item['rate'] : 0;
                $amount = isset($item['amount']) && $item['amount'] !== '' ? (float) $item['amount'] : 0;

                // If amount is provided but rate is zero, compute rate
                if ($amount > 0 && $rate <= 0 && $qty > 0) {
                    $rate = round($amount / $qty, 4);
                } elseif ($rate > 0 && $amount <= 0 && $qty > 0) {
                    $amount = round($qty * $rate, 2);
                }

                $branchId = !empty($item['branch_id']) ? (int) $item['branch_id'] : (int) $request->default_branch_id;
                $rowNotes = !empty($item['notes']) ? trim($item['notes']) : $batchNotes;

                // 1. Submit strictly into the stocks table with TYPE_OPENING (0)
                Stock::create([
                    'item_id'      => (int) $item['item_id'],
                    'voucher_no'   => $voucherNo,
                    'voucher_type' => Stock::TYPE_OPENING, // 0: Opening
                    'voucher_date' => $voucherDate,
                    'branch_id'    => $branchId,
                    'qty'          => $qty,
                    'rate'         => $rate,
                    'amount'       => $amount,
                    'status'       => 1,
                    'notes'        => $rowNotes,
                ]);

                // 2. Synchronize product on-hand quantity & purchase_price if zero
                $product = Product::find($item['item_id']);
                if ($product) {
                    $product->quantity += (int) $qty;
                    if ((float) $product->purchase_price <= 0 && $rate > 0) {
                        $product->purchase_price = $rate;
                    }
                    $product->save();
                }

                $submittedCount++;
            }

            DB::commit();

            return redirect()->route('opening-stocks.index')
                ->with('success', "Opening inventory voucher {$voucherNo} recorded successfully with {$submittedCount} items!");

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save opening inventory: ' . $e->getMessage());
        }
    }

    /**
     * Show details of a specific opening inventory voucher.
     */
    public function show(string $voucher_no): View
    {
        $items = Stock::with(['product.category', 'branch'])
            ->where('voucher_type', Stock::TYPE_OPENING)
            ->where('voucher_no', $voucher_no)
            ->orderBy('id', 'asc')
            ->get();

        if ($items->isEmpty()) {
            abort(404, 'Opening stock voucher not found.');
        }

        $voucherDate = $items->first()->voucher_date;
        $totalQty = $items->sum('qty');
        $totalAmount = $items->sum('amount');
        $branches = $items->pluck('branch.name')->unique()->filter()->implode(', ');

        return view('opening_stocks.show', compact('voucher_no', 'items', 'voucherDate', 'totalQty', 'totalAmount', 'branches'));
    }
}
