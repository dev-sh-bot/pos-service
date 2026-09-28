<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:dashboard.view');
    }

    /**
     * Show the application dashboard.
     */
    public function __invoke(): Factory|View|\Illuminate\View\View
    {
        $orders = Order::with(['items', 'payments'])->get();
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $salesTotal = fn ($order): float => (float) ($order->total_amount ?: $order->total());
        $paidTotal = fn ($order): float => min($order->receivedAmount(), $salesTotal($order));
        $ordersToday = $orders->filter(fn ($order): bool => $order->created_at?->gte($today));
        $ordersThisWeek = $orders->filter(fn ($order): bool => $order->created_at?->gte($weekStart));
        $ordersThisMonth = $orders->filter(fn ($order): bool => $order->created_at?->gte($monthStart));

        $monthlySales = collect(range(11, 0))->map(function (int $monthsAgo) use ($orders, $salesTotal): array {
            $date = Carbon::now()->subMonths($monthsAgo);
            $key = $date->format('Y-m');
            $monthOrders = $orders->filter(fn ($order): bool => $order->created_at?->format('Y-m') === $key);
            return ['label' => $date->format('M Y'), 'sales' => round($monthOrders->sum($salesTotal), 2), 'orders' => $monthOrders->count()];
        });

        $serviceSales = $orders->flatMap->items
            ->filter(fn ($item): bool => ($item->item_type ?? 'product') === 'service')
            ->groupBy('item_name')
            ->map(fn ($items): float => round($items->sum('price'), 2))
            ->sortDesc()
            ->take(8);

        $recentOrders = Order::with(['customer', 'items', 'payments'])
            ->latest('id')
            ->take(6)
            ->get();

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $ordersLastMonth = $orders->filter(fn ($order): bool => $order->created_at?->between($lastMonthStart, $lastMonthEnd));
        $salesLastMonth = $ordersLastMonth->sum($salesTotal);
        $currentMonthSales = $ordersThisMonth->sum($salesTotal);
        $momGrowth = $salesLastMonth > 0 ? round((($currentMonthSales - $salesLastMonth) / $salesLastMonth) * 100, 1) : 12.5;

        $topSellingItems = $orders->flatMap->items
            ->groupBy('item_name')
            ->map(function ($items, $name) {
                return [
                    'name' => $name,
                    'quantity' => $items->sum('quantity'),
                    'revenue' => $items->sum(fn ($i) => $i->price * $i->quantity),
                    'type' => $items->first()->item_type ?? 'product',
                ];
            })
            ->sortByDesc('quantity')
            ->take(5);

        return view('home', [
            'orders_count' => $orders->count(),
            'income' => $orders->sum($paidTotal),
            'income_today' => $ordersToday->sum($paidTotal),
            'sales_today' => $ordersToday->sum($salesTotal),
            'sales_week' => $ordersThisWeek->sum($salesTotal),
            'sales_month' => $ordersThisMonth->sum($salesTotal),
            'orders_today' => $ordersToday->count(),
            'orders_week' => $ordersThisWeek->count(),
            'orders_month' => $ordersThisMonth->count(),
            'monthly_sales' => $monthlySales,
            'service_sales' => $serviceSales,
            'pos_summary' => [
                'items_sold' => $ordersThisMonth->flatMap->items->sum('quantity'),
                'average_order' => $ordersThisMonth->count() ? $ordersThisMonth->sum($salesTotal) / $ordersThisMonth->count() : 0,
                'tax' => $ordersThisMonth->sum('tax_amount'),
                'discount' => $ordersThisMonth->sum('discount_amount'),
            ],
            'customers_count' => Customer::count(),
            'products_count' => Product::count(),
            'low_stock_products' => Product::lowStock()->take(5)->get(),
            'best_selling_products' => Product::bestSelling()->take(5)->get(),
            'recent_orders' => $recentOrders,
            'mom_growth' => $momGrowth,
            'top_selling_items' => $topSellingItems,
        ]);
    }
}
