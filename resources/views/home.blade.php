@extends('layouts.admin')
@section('title', __('dashboard.title') . ' - Knowhere POS')
@section('content-header', '')
@section('content')
<div class="snd-dashboard">
    <header class="dashboard-heading">
        <div class="dashboard-heading-copy">
            <span class="dashboard-eyebrow"><span class="dashboard-live-dot" aria-hidden="true"></span> {{ __('Operations overview') }}</span>
            <h1>{{ __('Welcome back,') }} {{ auth()->user()->first_name ?? 'Admin' }}<span class="dashboard-heading-period">.</span></h1>
            <p>{{ __('Your sales, services, and counters. All in focus.') }}</p>
        </div>
        <div class="dashboard-heading-actions">
            <span class="dashboard-date"><x-snd-icon name="calendar-days" /> {{ now()->format('d M Y') }}</span>
        </div>
    </header>

    <section class="dashboard-overview" aria-label="{{ __('Sales overview') }}">
        <div class="dashboard-kpi-grid">
            <article class="dashboard-kpi dashboard-kpi-featured dashboard-kpi-focus">
                <div class="dashboard-kpi-top"><span>{{ __('Sales today') }}</span><x-snd-icon name="zap" /></div>
                <h2><small>{{ config('settings.currency_symbol') }}</small> {{ number_format($sales_today, 2) }}</h2>
                <div class="dashboard-focus-footer"><span><strong>{{ $orders_today }}</strong> {{ __('orders today') }}</span><span class="dashboard-kpi-period">{{ __('Today') }}</span></div>
            </article>
            <article class="dashboard-kpi dashboard-kpi-secondary">
                <div class="dashboard-kpi-top"><span>{{ __('Weekly revenue') }}</span><x-snd-icon name="chart-line" /></div>
                <h2><small>{{ config('settings.currency_symbol') }}</small> {{ number_format($sales_week, 2) }}</h2>
                <div class="dashboard-kpi-bottom"><span>{{ $orders_week }} {{ __('orders this week') }}</span><span class="dashboard-kpi-period">{{ __('This week') }}</span></div>
            </article>
            <article class="dashboard-kpi dashboard-kpi-secondary">
                <div class="dashboard-kpi-top"><span>{{ __('Monthly revenue') }}</span><x-snd-icon name="calendar-days" /></div>
                <h2><small>{{ config('settings.currency_symbol') }}</small> {{ number_format($sales_month, 2) }}</h2>
                <div class="dashboard-kpi-bottom"><span>{{ $orders_month }} {{ __('orders this month') }}</span><span class="dashboard-kpi-period">{{ __('This month') }}</span></div>
            </article>
            <article class="dashboard-kpi dashboard-kpi-secondary">
                <div class="dashboard-kpi-top"><span>{{ __('Customers') }}</span><x-snd-icon name="users" /></div>
                <h2>{{ number_format($customers_count) }}</h2>
                <div class="dashboard-kpi-bottom"><span>{{ number_format($orders_count) }} {{ __('completed orders') }}</span><span class="dashboard-kpi-period">{{ __('All time') }}</span></div>
            </article>
        </div>
        <nav class="dashboard-shortcuts" aria-label="{{ __('Quick Options') }}">
            <span>{{ __('Quick actions') }}</span>
            <a href="{{ route('products.create') }}"><x-snd-icon name="plus" /> {{ __('Add Product') }} <x-snd-icon name="arrow-up-right" /></a>
            <a href="{{ route('customers.create') }}"><x-snd-icon name="user-plus" /> {{ __('New Customer') }} <x-snd-icon name="arrow-up-right" /></a>
            <a href="{{ route('orders.index') }}"><x-snd-icon name="receipt" /> {{ __('Orders List') }} <x-snd-icon name="arrow-up-right" /></a>
        </nav>
    </section>

    <section class="dashboard-analytics-grid" aria-label="{{ __('Sales analytics') }}">
        <article class="dashboard-panel dashboard-revenue-panel">
            <header class="dashboard-panel-header">
                <div><span class="dashboard-panel-label">{{ __('Performance') }}</span><h2>{{ __('Revenue & orders') }}</h2></div>
                <span class="dashboard-panel-tag">{{ __('Last 12 months') }}</span>
            </header>
            <div class="dashboard-chart-main"><canvas id="monthlySalesChart" aria-label="{{ __('Monthly revenue and orders') }}" role="img"></canvas></div>
        </article>
        <article class="dashboard-panel dashboard-service-panel">
            <header class="dashboard-panel-header">
                <div><span class="dashboard-panel-label">{{ __('Distribution') }}</span><h2>{{ __('Service sales share') }}</h2></div>
                <x-snd-icon name="chart-pie" />
            </header>
            <div class="dashboard-chart-service">
                @if ($service_sales->isNotEmpty())
                    <canvas id="serviceSalesChart" aria-label="{{ __('Service sales distribution') }}" role="img"></canvas>
                @else
                    <div class="dashboard-empty" role="status"><span class="dashboard-empty-icon"><x-snd-icon name="chart-pie" /></span><strong>{{ __('No service sales yet') }}</strong><p>{{ __('Service sales will appear here as service orders are recorded.') }}</p></div>
                @endif
            </div>
        </article>
    </section>

    <section class="dashboard-operations-grid" aria-label="{{ __('Recent activity and inventory') }}">
        <article class="dashboard-panel dashboard-transactions-panel">
            <header class="dashboard-panel-header">
                <div><span class="dashboard-panel-label">{{ __('Activity') }}</span><h2>{{ __('Latest transactions') }}</h2></div>
                <a href="{{ route('orders.index') }}" class="dashboard-text-link">{{ __('View all orders') }} <x-snd-icon name="arrow-up-right" /></a>
            </header>
            <div class="table-responsive snd-table-scroll">
                <table class="table dashboard-table">
                    <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('order.Customer_Name') }}</th><th>{{ __('Items') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
                    <tbody>
                    @forelse($recent_orders as $ord)
                        <tr>
                            <td><span class="dashboard-invoice">#ORD-{{ $ord->id }}</span>@if($ord->srb_invoice_id)<span class="dashboard-fiscal-id">{{ $ord->srb_invoice_id }}</span>@endif</td>
                            <td><div class="dashboard-customer"><span class="dashboard-avatar">{{ strtoupper(substr($ord->customer->first_name ?? 'Walk-in', 0, 1)) }}</span><span><strong>{{ $ord->customer ? $ord->customer->first_name . ' ' . $ord->customer->last_name : __('Walk-in Customer') }}</strong><small>{{ $ord->customer->phone ?? '' }}</small></span></div></td>
                            <td><span class="dashboard-item-count">{{ $ord->items->sum('quantity') }} {{ __('items') }}</span></td>
                            <td class="dashboard-money">{{ config('settings.currency_symbol') }} {{ number_format($ord->total_amount, 2) }}</td>
                            <td><span class="dashboard-status"><span aria-hidden="true"></span>{{ __('Paid') }}</span></td>
                            <td class="dashboard-time">{{ $ord->created_at ? $ord->created_at->format('d M, h:i A') : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="dashboard-empty dashboard-empty-compact"><x-snd-icon name="receipt" /><strong>{{ __('No recent transactions recorded') }}</strong></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
        <article class="dashboard-panel dashboard-stock-panel">
            <header class="dashboard-panel-header"><div><span class="dashboard-panel-label">{{ __('Demand') }}</span><h2>{{ __('Top selling items') }}</h2></div><x-snd-icon name="flame" /></header>
            <div class="dashboard-top-items">
                @forelse($top_selling_items as $item)
                    <div class="dashboard-top-item"><div><span class="dashboard-item-rank">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $item['name'] }}</strong><span class="dashboard-item-quantity">{{ $item['quantity'] }} {{ __('sold') }}</span></div><div class="dashboard-progress"><span style="width: {{ min(100, max(20, $item['quantity'] * 2)) }}%;"></span></div></div>
                @empty
                    <div class="dashboard-empty dashboard-empty-compact"><x-snd-icon name="package" /><strong>{{ __('No sales items recorded') }}</strong></div>
                @endforelse
            </div>
            @if($low_stock_products->isNotEmpty())
                <div class="dashboard-stock-alert"><div class="dashboard-stock-alert-title"><strong><x-snd-icon name="triangle-alert" /> {{ __('Low Stock Alert') }}</strong><a href="{{ route('products.index') }}">{{ __('Manage Inventory') }} <x-snd-icon name="arrow-up-right" /></a></div><div class="dashboard-stock-list">@foreach($low_stock_products as $lsp)<span>{{ $lsp->name }} <b>{{ $lsp->quantity }} {{ __('left') }}</b></span>@endforeach</div></div>
            @endif
        </article>
    </section>

    <section class="dashboard-financial-grid" aria-label="{{ __('Financial breakdown') }}">
        <article class="dashboard-panel">
            <header class="dashboard-panel-header"><div><span class="dashboard-panel-label">{{ __('Financials') }}</span><h2>{{ __('Monthly financial breakdown') }}</h2></div><span class="dashboard-panel-tag">{{ __('Audited log') }}</span></header>
            <div class="table-responsive snd-table-scroll">
                <table class="table dashboard-table">
                    <thead><tr><th>{{ __('Billing month') }}</th><th>{{ __('Total orders') }}</th><th>{{ __('Gross revenue') }}</th><th>{{ __('Avg ticket value') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>@foreach($monthly_sales as $month)<tr><td><strong>{{ $month['label'] }}</strong></td><td><span class="dashboard-item-count">{{ $month['orders'] }} {{ __('orders') }}</span></td><td class="dashboard-money">{{ config('settings.currency_symbol') }} {{ number_format($month['sales'], 2) }}</td><td class="dashboard-time">{{ config('settings.currency_symbol') }} {{ number_format($month['orders'] ? $month['sales'] / $month['orders'] : 0, 2) }}</td><td><span class="dashboard-status"><x-snd-icon name="check-check" /> {{ __('Closed') }}</span></td></tr>@endforeach</tbody>
                </table>
            </div>
        </article>
        <article class="dashboard-panel dashboard-summary-panel">
            <header class="dashboard-panel-header"><div><span class="dashboard-panel-label">{{ __('POS summary') }}</span><h2>{{ __('Monthly counter audit') }}</h2></div><span class="dashboard-panel-tag">{{ now()->format('M Y') }}</span></header>
            <dl class="dashboard-summary-list">
                <div><dt><x-snd-icon name="box" /> {{ __('Items dispatched') }}</dt><dd>{{ number_format($pos_summary['items_sold']) }} {{ __('units') }}</dd></div>
                <div><dt><x-snd-icon name="receipt" /> {{ __('Average basket size') }}</dt><dd>{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['average_order'], 2) }}</dd></div>
                <div><dt><x-snd-icon name="percent" /> {{ __('Tax collected (SRB)') }}</dt><dd>{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['tax'], 2) }}</dd></div>
                <div><dt><x-snd-icon name="tag" /> {{ __('Discounts claimed') }}</dt><dd>{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['discount'], 2) }}</dd></div>
            </dl>
            <div class="dashboard-summary-total"><span>{{ __('Total net inflow') }}</span><strong><small>{{ config('settings.currency_symbol') }}</small> {{ number_format($sales_month, 2) }}</strong><x-snd-icon name="arrow-up-right" /></div>
        </article>
    </section>
</div>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const currency = @json(config('settings.currency_symbol'));
    const monthly = @json($monthly_sales);
    const services = @json($service_sales);
    const themeColor = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    const serviceChartColors = () => [
        themeColor('--snd-primary'), themeColor('--snd-info'), themeColor('--snd-success'), themeColor('--snd-warning'),
        themeColor('--snd-danger'), '#8b5cf6', '#f97316', '#14b8a6'
    ];
    let monthlyChart = null;
    let serviceChart = null;

    // 1. Monthly Sales & Orders Combined Bar + Area Chart
    const ctxMonthly = document.getElementById('monthlySalesChart');
    if (ctxMonthly) {
        const labels = monthly.map(m => m.label);
        const salesData = monthly.map(m => m.sales);
        const ordersData = monthly.map(m => m.orders);

        monthlyChart = new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gross Sales (' + currency + ')',
                        data: salesData,
                        backgroundColor: themeColor('--snd-primary'),
                        borderColor: themeColor('--snd-primary'),
                        borderWidth: 1,
                        borderRadius: 2,
                        barPercentage: 0.6,
                        yAxisID: 'ySales'
                    },
                    {
                        label: 'Orders Count',
                        data: ordersData,
                        type: 'line',
                        borderColor: themeColor('--snd-primary-hover'),
                        backgroundColor: themeColor('--snd-primary-soft'),
                        borderWidth: 3,
                        pointBackgroundColor: themeColor('--snd-primary-hover'),
                        pointBorderColor: themeColor('--snd-surface'),
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.38,
                        fill: true,
                        yAxisID: 'yOrders'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Archivo, sans-serif', size: 12, weight: '600' },
                            color: themeColor('--snd-muted'),
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: themeColor('--snd-surface'),
                        titleColor: themeColor('--snd-ink'),
                        bodyColor: themeColor('--snd-ink-soft'),
                        borderColor: themeColor('--snd-border-strong'),
                        borderWidth: 1,
                        titleFont: { family: 'Archivo, sans-serif', size: 13, weight: '700' },
                        bodyFont: { family: 'Archivo, sans-serif', size: 12 },
                        padding: 12,
                        cornerRadius: 4,
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'ySales') {
                                    return 'Sales: ' + currency + ' ' + context.parsed.y.toLocaleString();
                                }
                                return 'Orders: ' + context.parsed.y + ' orders';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Archivo, sans-serif', size: 11 }, color: themeColor('--snd-muted') }
                    },
                    ySales: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: themeColor('--snd-border') },
                        ticks: {
                            font: { family: 'Archivo, sans-serif', size: 11 },
                            color: themeColor('--snd-muted'),
                            callback: value => currency + ' ' + (value >= 1000 ? (value/1000) + 'k' : value)
                        }
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { family: 'Archivo, sans-serif', size: 11 },
                            color: themeColor('--snd-muted'),
                            stepSize: 10
                        }
                    }
                }
            }
        });
    }

    // 2. Category & Service Doughnut Chart
   const ctxService = document.getElementById('serviceSalesChart');
    if (ctxService) {
        const serviceKeys = Object.keys(services);
        const serviceValues = Object.values(services);

        serviceChart = new Chart(ctxService, {
            type: 'doughnut',
            data: {
                labels: serviceKeys,
                datasets: [{
                    data: serviceValues,
                    backgroundColor: serviceChartColors().slice(0, serviceKeys.length),
                    borderWidth: 2,
                    borderColor: themeColor('--snd-surface'),
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Archivo, sans-serif',
                                size: 11,
                                weight: '500'
                            },
                            color: themeColor('--snd-muted'),
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: {
                        backgroundColor: themeColor('--snd-surface'),
                        titleColor: themeColor('--snd-ink'),
                        bodyColor: themeColor('--snd-ink-soft'),
                        borderColor: themeColor('--snd-border-strong'),
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 4,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    currency + ' ' +
                                    Number(context.parsed).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    const updateDashboardChartTheme = () => {
        if (monthlyChart) {
            const [salesDataset, ordersDataset] = monthlyChart.data.datasets;
            salesDataset.backgroundColor = themeColor('--snd-primary');
            salesDataset.borderColor = themeColor('--snd-primary');
            ordersDataset.borderColor = themeColor('--snd-primary-hover');
            ordersDataset.backgroundColor = themeColor('--snd-primary-soft');
            ordersDataset.pointBackgroundColor = themeColor('--snd-primary-hover');
            ordersDataset.pointBorderColor = themeColor('--snd-surface');
            monthlyChart.options.plugins.legend.labels.color = themeColor('--snd-muted');
            monthlyChart.options.plugins.tooltip.backgroundColor = themeColor('--snd-surface');
            monthlyChart.options.plugins.tooltip.titleColor = themeColor('--snd-ink');
            monthlyChart.options.plugins.tooltip.bodyColor = themeColor('--snd-ink-soft');
            monthlyChart.options.plugins.tooltip.borderColor = themeColor('--snd-border-strong');
            monthlyChart.options.scales.x.ticks.color = themeColor('--snd-muted');
            monthlyChart.options.scales.ySales.grid.color = themeColor('--snd-border');
            monthlyChart.options.scales.ySales.ticks.color = themeColor('--snd-muted');
            monthlyChart.options.scales.yOrders.ticks.color = themeColor('--snd-muted');
            monthlyChart.update('none');
        }

        if (serviceChart) {
            const colors = serviceChartColors();
            serviceChart.data.datasets[0].backgroundColor = serviceChart.data.labels.map((_, index) => colors[index % colors.length]);
            serviceChart.data.datasets[0].borderColor = themeColor('--snd-surface');
            serviceChart.options.plugins.legend.labels.color = themeColor('--snd-muted');
            serviceChart.options.plugins.tooltip.backgroundColor = themeColor('--snd-surface');
            serviceChart.options.plugins.tooltip.titleColor = themeColor('--snd-ink');
            serviceChart.options.plugins.tooltip.bodyColor = themeColor('--snd-ink-soft');
            serviceChart.options.plugins.tooltip.borderColor = themeColor('--snd-border-strong');
            serviceChart.update('none');
        }
    };

    document.addEventListener('snd:themechange', updateDashboardChartTheme);
});
</script>
@endsection
