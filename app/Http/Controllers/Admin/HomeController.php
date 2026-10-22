<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:dashboard.view');
    }

    /**
     * Show the application dashboard.
     */
    public function __invoke(Request $request): Factory|View|\Illuminate\View\View
    {
        $user = $request->user();
        $isAdmin = (bool) ($user && $user->isSuperAdmin());

        // Branches accessible to the user
        if ($isAdmin) {
            $branches = Branch::withCount('counters')->orderBy('name')->get();
            $canViewConsolidated = true;
        } else {
            $branches = $user
                ? $user->branches()->withCount('counters')->where('is_active', true)->orderBy('name')->get()
                : collect();
            $canViewConsolidated = false;
        }

        $allowedBranchIds = $branches->pluck('id')->all();

        // Determine selected branch: Admin can view all or specific; regular user only assigned
        if ($isAdmin) {
            $selectedBranchId = $request->filled('branch_id') && $request->input('branch_id') !== 'all'
                ? (int) $request->input('branch_id')
                : null;
        } else {
            if ($request->filled('branch_id') && in_array((int) $request->input('branch_id'), $allowedBranchIds, true)) {
                $selectedBranchId = (int) $request->input('branch_id');
            } else {
                $selectedBranchId = $branches->first()?->id;
            }
        }

        $selectedBranch = $selectedBranchId ? $branches->firstWhere('id', $selectedBranchId) : null;

        // Fetch orders scoped to accessible branches
        $ordersQuery = Order::with(['items.product', 'items.service', 'items.deal', 'payments', 'branch', 'counter', 'customer']);

        if (! $isAdmin) {
            $ordersQuery->whereIn('branch_id', $allowedBranchIds);
        }

        $allOrders = $ordersQuery->get();

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $salesTotal = fn ($order): float => (float) ($order->total_amount ?: $order->total());
        $paidTotal = fn ($order): float => min($order->receivedAmount(), $salesTotal($order));
        $allSalesTotal = (float) $allOrders->sum($salesTotal);

        // ── Branch-wise Comparison Metrics ────────────────────────
        $branchMetrics = $branches->map(function ($branch) use ($allOrders, $salesTotal, $paidTotal, $allSalesTotal, $today, $monthStart) {
            $branchOrders = $allOrders->filter(fn ($o) => (int) $o->branch_id === (int) $branch->id);
            $bOrdersToday = $branchOrders->filter(fn ($o) => $o->created_at?->gte($today));
            $bOrdersMonth = $branchOrders->filter(fn ($o) => $o->created_at?->gte($monthStart));
            $branchTotalSales = (float) $branchOrders->sum($salesTotal);
            $branchOrdersCount = $branchOrders->count();

            // Counter details for this branch
            $countersData = $branch->counters->map(function ($c) use ($branchOrders, $salesTotal, $today, $monthStart) {
                $cOrders = $branchOrders->filter(fn ($o) => (int) $o->counter_id === (int) $c->id);
                $cToday = $cOrders->filter(fn ($o) => $o->created_at?->gte($today));
                $cMonth = $cOrders->filter(fn ($o) => $o->created_at?->gte($monthStart));
                return [
                    'id'           => $c->id,
                    'name'         => $c->name,
                    'code'         => $c->code,
                    'is_active'    => (bool) $c->is_active,
                    'sales_today'  => (float) $cToday->sum($salesTotal),
                    'orders_today' => $cToday->count(),
                    'sales_month'  => (float) $cMonth->sum($salesTotal),
                    'orders_month' => $cMonth->count(),
                    'total_sales'  => (float) $cOrders->sum($salesTotal),
                    'orders_count' => $cOrders->count(),
                ];
            });

            return [
                'id'               => $branch->id,
                'name'             => $branch->name,
                'code'             => $branch->code,
                'address'          => $branch->address,
                'phone'            => $branch->phone,
                'counters'         => $countersData,
                'counters_count'   => $branch->counters_count,
                'orders_count'     => $branchOrdersCount,
                'orders_today'     => $bOrdersToday->count(),
                'orders_month'     => $bOrdersMonth->count(),
                'sales_today'      => (float) $bOrdersToday->sum($salesTotal),
                'sales_month'      => (float) $bOrdersMonth->sum($salesTotal),
                'total_sales'      => $branchTotalSales,
                'average_order'    => $branchOrdersCount > 0 ? $branchTotalSales / $branchOrdersCount : 0,
                'share_percentage' => $allSalesTotal > 0 ? round(($branchTotalSales / $allSalesTotal) * 100, 1) : 0,
                'paid_amount'      => (float) $branchOrders->sum($paidTotal),
                'recent_orders'    => $branchOrders->sortByDesc('id')->take(6)->values(),
            ];
        });

        // ── Active Working Orders (Filtered if branch selected) ──
        if ($selectedBranchId) {
            $orders = $allOrders->filter(fn ($order): bool => (int) $order->branch_id === $selectedBranchId);
        } else {
            $orders = $allOrders;
        }

        // ── Counter Breakdown (When a specific branch is selected) ─
        $counterMetrics = collect();
        if ($selectedBranch) {
            $selectedBranch->load('counters');
            $counterMetrics = $selectedBranch->counters->map(function ($counter) use ($orders, $salesTotal, $today, $monthStart) {
                $cOrders = $orders->filter(fn ($o) => (int) $o->counter_id === (int) $counter->id);
                $cOrdersToday = $cOrders->filter(fn ($o) => $o->created_at?->gte($today));
                $cOrdersMonth = $cOrders->filter(fn ($o) => $o->created_at?->gte($monthStart));
                $counterTotalSales = (float) $cOrders->sum($salesTotal);

                return [
                    'id'           => $counter->id,
                    'name'         => $counter->name,
                    'code'         => $counter->code,
                    'is_active'    => (bool) $counter->is_active,
                    'orders_today' => $cOrdersToday->count(),
                    'sales_today'  => (float) $cOrdersToday->sum($salesTotal),
                    'orders_month' => $cOrdersMonth->count(),
                    'sales_month'  => (float) $cOrdersMonth->sum($salesTotal),
                    'total_sales'  => $counterTotalSales,
                    'orders_count' => $cOrders->count(),
                ];
            });
        }

        $ordersToday = $orders->filter(fn ($order): bool => $order->created_at?->gte($today));
        $ordersThisWeek = $orders->filter(fn ($order): bool => $order->created_at?->gte($weekStart));
        $ordersThisMonth = $orders->filter(fn ($order): bool => $order->created_at?->gte($monthStart));

        $monthlySales = collect(range(11, 0))->map(function (int $monthsAgo) use ($orders, $salesTotal): array {
            $date = Carbon::now()->subMonths($monthsAgo);
            $key = $date->format('Y-m');
            $monthOrders = $orders->filter(fn ($order): bool => $order->created_at?->format('Y-m') === $key);
            return [
                'label'  => $date->format('M Y'),
                'sales'  => round($monthOrders->sum($salesTotal), 2),
                'orders' => $monthOrders->count(),
            ];
        });

        // Helper to resolve human-readable item name
        $resolveItemName = function ($item): string {
            $name = $item->attributes['item_name'] ?? null;
            if (empty($name) || is_numeric($name)) {
                $resolved = $item->item();
                $name = $resolved?->name ?: ('Item #' . ($item->item_id ?? $item->id));
            }
            return (string) $name;
        };

        // Service / Product / Deal sales breakdown for doughnut chart
        $serviceSales = $orders->flatMap->items
            ->groupBy($resolveItemName)
            ->map(fn ($items): float => round($items->sum(fn ($i) => (float) ($i->price * $i->quantity)), 2))
            ->sortDesc()
            ->take(6);

        $recentOrdersQuery = Order::with(['customer', 'items', 'payments', 'branch', 'counter'])
            ->latest('id');

        if (! $isAdmin) {
            $recentOrdersQuery->whereIn('branch_id', $allowedBranchIds);
        }

        if ($selectedBranchId) {
            $recentOrdersQuery->where('branch_id', $selectedBranchId);
        }

        $recentOrders = $recentOrdersQuery->take(6)->get();

        $ordersLastMonth = $orders->filter(fn ($order): bool => $order->created_at?->between($lastMonthStart, $lastMonthEnd));
        $salesLastMonth = $ordersLastMonth->sum($salesTotal);
        $currentMonthSales = $ordersThisMonth->sum($salesTotal);
        $momGrowth = $salesLastMonth > 0 ? round((($currentMonthSales - $salesLastMonth) / $salesLastMonth) * 100, 1) : 12.5;

        $topSellingItems = $orders->flatMap->items
            ->groupBy($resolveItemName)
            ->map(function ($items, $name) {
                return [
                    'name'     => $name,
                    'quantity' => $items->sum('quantity'),
                    'revenue'  => $items->sum(fn ($i) => $i->price * $i->quantity),
                    'type'     => $items->first()->item_type ?? 0,
                ];
            })
            ->sortByDesc('quantity')
            ->take(5);

        // ── Staff & Counter Sales Breakdown ─────────────────────────
        $todayStr = $today->toDateTimeString();
        $monthStartStr = $monthStart->toDateTimeString();

        $staffSalesQuery = Order::with(['user', 'branch', 'counter'])
            ->select(
                'branch_id',
                'counter_id',
                'user_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_sales'),
                DB::raw("COALESCE(SUM(CASE WHEN created_at >= '{$todayStr}' THEN total_amount ELSE 0 END), 0) as sales_today"),
                DB::raw("COUNT(CASE WHEN created_at >= '{$todayStr}' THEN id ELSE NULL END) as orders_today"),
                DB::raw("COALESCE(SUM(CASE WHEN created_at >= '{$monthStartStr}' THEN total_amount ELSE 0 END), 0) as sales_month"),
                DB::raw("COUNT(CASE WHEN created_at >= '{$monthStartStr}' THEN id ELSE NULL END) as orders_month"),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->groupBy('branch_id', 'counter_id', 'user_id');

        // Scoping for regular user (Non-admin sees ONLY their own sales)
        if (!$isAdmin) {
            $staffSalesQuery->where('user_id', $user?->id ?? 0);
        }

        $staffSalesRaw = $staffSalesQuery->get();

        // Returns per branch / counter / user
        $returnsMap = collect();
        if (Schema::hasTable('sale_returns')) {
            $returnsQuery = \App\Models\SaleReturn::select(
                'branch_id',
                'counter_id',
                'user_id',
                DB::raw('COUNT(id) as total_returns'),
                DB::raw('COALESCE(SUM(refund_total), 0) as total_refunded'),
                DB::raw("COALESCE(SUM(CASE WHEN created_at >= '{$todayStr}' THEN refund_total ELSE 0 END), 0) as returns_today")
            )->groupBy('branch_id', 'counter_id', 'user_id');

            if (!$isAdmin) {
                $returnsQuery->where('user_id', $user?->id ?? 0);
            }

            $returnsMap = $returnsQuery->get()->keyBy(function ($r) {
                return "{$r->branch_id}_{$r->counter_id}_{$r->user_id}";
            });
        }

        $staffSalesData = $staffSalesRaw->map(function ($row) use ($returnsMap, $user) {
            $key = "{$row->branch_id}_{$row->counter_id}_{$row->user_id}";
            $returnInfo = $returnsMap->get($key);

            $grossTotal = (float) $row->total_sales;
            $refundTotal = $returnInfo ? (float) $returnInfo->total_refunded : 0.0;
            $netTotal = max(0, $grossTotal - $refundTotal);

            $todaySales = (float) $row->sales_today;
            $todayReturns = $returnInfo ? (float) $returnInfo->returns_today : 0.0;
            $todayNet = max(0, $todaySales - $todayReturns);

            return [
                'branch_id'      => $row->branch_id,
                'branch_name'    => $row->branch?->name ?? 'Main Branch',
                'branch_code'    => $row->branch?->code ?? 'BR01',
                'counter_id'     => $row->counter_id,
                'counter_name'   => $row->counter?->name ?? 'Terminal 1',
                'counter_code'   => $row->counter?->code ?? 'C1',
                'user_id'        => $row->user_id,
                'user_name'      => $row->user ? $row->user->getFullname() : 'Staff Member',
                'user_email'     => $row->user?->email ?? '',
                'user_avatar'    => $row->user ? $row->user->getAvatar() : '',
                'is_me'          => (int) $row->user_id === (int) ($user?->id ?? 0),
                'orders_count'   => (int) $row->total_orders,
                'orders_today'   => (int) $row->orders_today,
                'orders_month'   => (int) $row->orders_month,
                'sales_today'    => $todaySales,
                'net_today'      => $todayNet,
                'sales_month'    => (float) $row->sales_month,
                'gross_sales'    => $grossTotal,
                'returns_count'  => $returnInfo ? (int) $returnInfo->total_returns : 0,
                'returns_total'  => $refundTotal,
                'net_sales'      => $netTotal,
                'avg_order'      => $row->total_orders > 0 ? $grossTotal / $row->total_orders : 0.0,
                'last_order_at'  => $row->last_order_at ? Carbon::parse($row->last_order_at) : null,
            ];
        })->sortByDesc('gross_sales')->values();

        // If non-admin has no orders yet, still present a zero-state row with their details
        if (!$isAdmin && $staffSalesData->isEmpty() && $user) {
            $userBranch = $user->branches->first() ?? Branch::first();
            $userCounter = $user->counters->first();
            $staffSalesData = collect([[
                'branch_id'      => $userBranch?->id,
                'branch_name'    => $userBranch?->name ?? 'Main Branch',
                'branch_code'    => $userBranch?->code ?? 'BR01',
                'counter_id'     => $userCounter?->id,
                'counter_name'   => $userCounter?->name ?? 'Terminal 1',
                'counter_code'   => $userCounter?->code ?? 'C1',
                'user_id'        => $user->id,
                'user_name'      => $user->getFullname(),
                'user_email'     => $user->email,
                'user_avatar'    => $user->getAvatar(),
                'is_me'          => true,
                'orders_count'   => 0,
                'orders_today'   => 0,
                'orders_month'   => 0,
                'sales_today'    => 0.0,
                'net_today'      => 0.0,
                'sales_month'    => 0.0,
                'gross_sales'    => 0.0,
                'returns_count'  => 0,
                'returns_total'  => 0.0,
                'net_sales'      => 0.0,
                'avg_order'      => 0.0,
                'last_order_at'  => null,
            ]]);
        }

        // Summary aggregates for staff sales
        $staffSalesSummary = [
            'total_gross'    => (float) $staffSalesData->sum('gross_sales'),
            'total_returns'  => (float) $staffSalesData->sum('returns_total'),
            'total_net'      => (float) $staffSalesData->sum('net_sales'),
            'total_orders'   => (int) $staffSalesData->sum('orders_count'),
            'today_sales'    => (float) $staffSalesData->sum('sales_today'),
            'today_orders'   => (int) $staffSalesData->sum('orders_today'),
            'month_sales'    => (float) $staffSalesData->sum('sales_month'),
            'users_count'    => $staffSalesData->pluck('user_id')->filter()->unique()->count(),
            'counters_count' => $staffSalesData->pluck('counter_id')->filter()->unique()->count(),
            'branches_count' => $staffSalesData->pluck('branch_id')->filter()->unique()->count(),
        ];

        return view('home', [
            'isAdmin'               => $isAdmin,
            'canViewConsolidated'   => $canViewConsolidated,
            'branches'              => $branches,
            'selectedBranchId'      => $selectedBranchId,
            'selectedBranch'        => $selectedBranch,
            'branchMetrics'         => $branchMetrics,
            'counterMetrics'        => $counterMetrics,
            'staffSalesData'        => $staffSalesData,
            'staffSalesSummary'     => $staffSalesSummary,
            'orders_count'          => $orders->count(),
            'income'                => $orders->sum($paidTotal),
            'income_today'          => $ordersToday->sum($paidTotal),
            'sales_today'           => $ordersToday->sum($salesTotal),
            'sales_week'            => $ordersThisWeek->sum($salesTotal),
            'sales_month'           => $ordersThisMonth->sum($salesTotal),
            'orders_today'          => $ordersToday->count(),
            'orders_week'           => $ordersThisWeek->count(),
            'orders_month'          => $ordersThisMonth->count(),
            'monthly_sales'         => $monthlySales,
            'service_sales'         => $serviceSales,
            'pos_summary'           => [
                'items_sold'    => $ordersThisMonth->flatMap->items->sum('quantity'),
                'average_order' => $ordersThisMonth->count() ? $ordersThisMonth->sum($salesTotal) / $ordersThisMonth->count() : 0,
                'tax'           => $ordersThisMonth->sum('tax_amount'),
                'discount'      => $ordersThisMonth->sum('discount_amount'),
            ],
            'customers_count'       => Customer::count(),
            'products_count'        => Product::count(),
            'low_stock_products'    => Product::lowStock()->take(5)->get(),
            'best_selling_products' => Product::bestSelling()->take(5)->get(),
            'recent_orders'         => $recentOrders,
            'mom_growth'            => $momGrowth,
            'top_selling_items'     => $topSellingItems,
        ]);
    }
}
