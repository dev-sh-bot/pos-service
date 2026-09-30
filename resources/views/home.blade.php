@extends('layouts.admin')
@section('content-header', '')
@section('content')
<style>
    /* Luxury Dashboard Design System */
    .dashboard-container {
        padding: 0 0 24px;
    }

    .welcome-banner {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px 26px;
        margin-bottom: 24px;
        border: 1.5px solid var(--snd-border-strong);
        box-shadow: 0 10px 30px rgba(151, 134, 238, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .welcome-title h2 {
        font-size: 1.55rem;
        font-weight: 600;
        color: var(--snd-ink);
        margin: 0 0 4px;
        letter-spacing: -0.03em;
    }

    .welcome-title p {
        margin: 0;
        font-size: 0.88rem;
        color: var(--snd-primary-deep);
    }

    .quick-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-quick-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .btn-pos-quick {
        background: var(--snd-primary);
        color: var(--snd-button-primary-text) !important;
        box-shadow: 0 6px 18px rgba(103, 88, 189, 0.22);
    }
    .btn-pos-quick:hover {
        transform: translateY(-2px);
        background: var(--snd-primary);
        box-shadow: 0 8px 24px rgba(103, 88, 189, 0.28);
    }

    .btn-secondary-quick {
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep) !important;
        border: 1.5px solid var(--snd-border-strong);
    }
    .btn-secondary-quick:hover {
        background: var(--snd-primary-soft);
        border-color: var(--snd-primary-deep);
        color: var(--snd-primary-deep) !important;
        transform: translateY(-2px);
    }

    /* ''�''�� Metric Cards ''�''�� */
    .metric-card {
        border-radius: 20px;
        padding: 22px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 34px rgba(151, 134, 238, 0.2);
    }

    .metric-card.card-today {
        background: #ffffff;
        border: 1.5px solid rgba(151, 134, 238, 0.3);
        color: var(--snd-ink);
    }

    .metric-card.card-week {
        background: #ffffff;
        border: 1.5px solid rgba(151, 134, 238, 0.3);
        color: var(--snd-ink);
    }

    .metric-card.card-month {
        background: #ffffff;
        border: 1.5px solid rgba(151, 134, 238, 0.3);
        color: var(--snd-ink);
    }

    .metric-card.card-customers {
        background: #ffffff;
        border: 1.5px solid rgba(151, 134, 238, 0.3);
        color: var(--snd-ink);
    }

    .metric-card.card-week h3,
    .metric-card.card-week p,
    .metric-card.card-customers h3,
    .metric-card.card-customers p {
        color: var(--snd-ink);
    }

    .metric-card.card-week .icon-bubble, .metric-card.card-today .icon-bubble,.metric-card.card-customers .icon-bubble,
    .metric-card.card-month .icon-bubble {
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
    }

    .metric-card.card-week .metric-pill, .metric-card.card-today .metric-pill, .metric-card.card-customers .metric-pill,
    .metric-card.card-month .metric-pill {
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .metric-header .icon-bubble {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .metric-pill {
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.22);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .metric-card h3 {
        font-size: 1.7rem;
        font-weight: 600;
        margin: 0 0 4px;
        letter-spacing: -0.02em;
    }

    .metric-card p {
        margin: 0;
        font-size: 0.88rem;
        opacity: 0.92;
        font-weight: 500;
    }

    /* ''�''�� Standard Widget Card ''�''�� */
    .widget-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1.5px solid var(--snd-border-strong);
        box-shadow: 0 10px 30px rgba(151, 134, 238, 0.06);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .widget-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--snd-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
    }

    .widget-header h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--snd-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .widget-header .badge-tag {
        font-size: 0.72rem;
        font-weight: 500;
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
        border: 1px solid var(--snd-border-strong);
        padding: 4px 10px;
        border-radius: 8px;
    }

    .chart-container-lg {
        height: 320px;
        padding: 18px 20px;
        position: relative;
    }

    .chart-container-sm {
        height: 320px;
        padding: 18px 20px;
        position: relative;
    }

    /* ''�''�� Data Tables ''�''�� */
    .table-modern {
        margin-bottom: 0;
    }

    .table-modern thead th {
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
        font-size: 0.74rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-top: none;
        border-bottom: 1px solid var(--snd-border-strong);
        padding: 12px 18px;
    }

    .table-modern tbody td {
        padding: 14px 18px;
        font-size: 0.86rem;
        color: var(--snd-ink);
        vertical-align: middle;
        border-bottom: 1px solid var(--snd-border);
    }

    .table-modern tbody tr:last-child td {
        border-bottom: none;
    }

    .table-modern tbody tr:hover {
        background-color: var(--snd-primary-soft);
    }

    .avatar-badge {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--snd-primary-soft);
        border: 1px solid var(--snd-border-strong);
        color: var(--snd-primary-deep);
        font-weight: 600;
        font-size: 0.82rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .status-pill {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.02em;
        display: inline-block;
    }

    .status-pill.paid {
        background: var(--snd-success-soft);
        color: var(--snd-success);
        border: 1px solid rgba(31,122,61,.22);
    }

    .status-pill.srb {
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
        border: 1px solid var(--snd-border-strong);
        font-size: 0.7rem;
    }

    .progress-bar-custom {
        height: 6px;
        border-radius: 4px;
        background: var(--snd-border-strong);
        overflow: hidden;
        margin-top: 6px;
    }

    .progress-fill-orange {
        height: 100%;
        background: var(--snd-primary);
        border-radius: 4px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid var(--snd-border);
    }
    .summary-item:last-child {
        border-bottom: none;
    }
    .summary-item .label {
        font-size: 0.86rem;
        color: var(--snd-primary-deep);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .summary-item .value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--snd-ink);
    }

    /* Dashboard overview layout: KPI rail, centered analytics, quick options. */
    .dashboard-container {
        padding-bottom: 32px;
    }

    .dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 16px;
        padding: 22px 24px;
        border: 1px solid var(--snd-border);
        border-radius: var(--snd-radius-xl);
        background: linear-gradient(135deg, rgba(255, 255, 255, .92), rgba(243, 247, 255, .86));
        box-shadow: var(--snd-shadow);
    }

    .dashboard-hero-copy { min-width: 0; }
    .dashboard-eyebrow {
        margin-bottom: 7px;
        color: var(--snd-primary-deep);
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
    }
    .dashboard-hero h2 {
        margin: 0 0 4px;
        color: var(--snd-ink);
        font-size: 1.55rem;
        font-weight: 600;
        letter-spacing: -.035em;
    }
    .dashboard-hero p {
        max-width: 680px;
        margin: 0;
        color: var(--snd-muted);
        font-size: .78rem;
    }
    .dashboard-hero-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
        padding: 8px 11px;
        border: 1px solid rgba(151, 134, 238, .2);
        border-radius: 10px;
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
        font-size: .66rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .dashboard-quick-options {
        margin-bottom: 16px;
        overflow: hidden;
    }
    .dashboard-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 18px 12px;
        border-bottom: 1px solid var(--snd-border);
    }
    .dashboard-section-header h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--snd-ink);
        font-size: .85rem;
        font-weight: 600;
    }
    .dashboard-section-header h3 svg { color: var(--snd-primary-deep); }
    .dashboard-section-header p {
        margin: 3px 0 0;
        color: var(--snd-muted);
        font-size: .68rem;
    }
    .dashboard-section-tag {
        padding: 5px 9px;
        border: 1px solid rgba(151, 134, 238, .22);
        border-radius: 8px;
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
        font-size: .6rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .dashboard-quick-options-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        padding: 14px 16px 16px;
    }
    .dashboard-quick-action {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        min-height: 68px;
        padding: 11px 12px;
        border: 1px solid var(--snd-border);
        border-radius: var(--snd-radius-md);
        background: rgba(255, 255, 255, .72);
        color: var(--snd-ink-soft) !important;
        transition: border-color .16s ease, background .16s ease, transform .16s ease, box-shadow .16s ease;
    }
    .dashboard-quick-action:hover {
        border-color: rgba(151, 134, 238, .38);
        background: var(--snd-primary-soft);
        box-shadow: 0 8px 18px rgba(151, 134, 238, .1);
        color: var(--snd-ink) !important;
        transform: translateY(-1px);
    }
    .dashboard-quick-action-primary {
        border-color: transparent;
        background: var(--snd-primary);
        color: var(--snd-button-primary-text) !important;
        box-shadow: 0 8px 18px rgba(103, 88, 189, .2);
    }
    .dashboard-quick-action-primary:hover {
        border-color: transparent;
        background: var(--snd-primary);
        color: var(--snd-button-primary-text) !important;
    }
    .dashboard-quick-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        border-radius: 10px;
        background: var(--snd-primary-soft);
        color: var(--snd-primary-deep);
    }
    .dashboard-quick-action-primary .dashboard-quick-action-icon {
        background: rgba(255, 255, 255, .2);
        color: var(--snd-button-primary-text);
    }
    .dashboard-quick-action-copy {
        display: flex;
        flex: 1 1 auto;
        min-width: 0;
        flex-direction: column;
        gap: 2px;
    }
    .dashboard-quick-action-copy strong {
        overflow: hidden;
        color: inherit;
        font-size: .72rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dashboard-quick-action-primary .dashboard-quick-action-copy strong {
        color: var(--snd-button-primary-text) !important;
    }
    .dashboard-quick-action-copy small {
        overflow: hidden;
        color: var(--snd-muted);
        font-size: .6rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dashboard-quick-action-primary .dashboard-quick-action-copy small { color: var(--snd-button-primary-text) !important; }
    .dashboard-quick-arrow { flex: 0 0 auto; color: var(--snd-muted); }
    .dashboard-quick-action-primary .dashboard-quick-arrow { color: var(--snd-button-primary-text); }

    .dashboard-overview-grid {
        display: grid;
        grid-template-columns: 220px minmax(0, 1.5fr) minmax(320px, 1fr);
        gap: 16px;
        align-items: stretch;
        margin-bottom: 16px;
    }
    .dashboard-main-grid { display: contents; }
    .dashboard-overview-grid .dashboard-hero {
        grid-column: 2;
        grid-row: 1;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        margin-bottom: 0;
    }
    .dashboard-overview-grid .dashboard-quick-options {
        grid-column: 3;
        grid-row: 1;
        margin-bottom: 0 !important;
    }
    .dashboard-overview-grid .dashboard-quick-options-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        padding: 12px;
    }
    .dashboard-overview-grid .dashboard-quick-action { min-height: 60px; padding: 8px; gap: 7px; }
    .dashboard-overview-grid .dashboard-quick-action-copy strong,
    .dashboard-overview-grid .dashboard-quick-action-copy small { white-space: normal; }
    .dashboard-overview-grid .dashboard-quick-arrow { display: none; }
    .dashboard-kpi-rail {
        display: grid;
        grid-column: 1;
        grid-row: 1 / 3;
        grid-template-rows: auto repeat(4, minmax(112px, 1fr));
        gap: 10px;
        min-width: 0;
    }
    .dashboard-kpi-rail-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 42px;
        padding: 0 13px;
        border: 1px solid var(--snd-border);
        border-radius: var(--snd-radius-md);
        background: var(--snd-surface-solid);
        box-shadow: var(--snd-shadow);
        color: var(--snd-ink);
        font-size: .78rem;
        font-weight: 600;
    }
    .dashboard-kpi-rail-caption {
        color: var(--snd-muted);
        font-size: .58rem;
        font-weight: 500;
    }
    .dashboard-kpi-card {
        min-height: 112px;
        height: auto;
        padding: 15px 16px;
        border: 1px solid var(--snd-border) !important;
        border-radius: var(--snd-radius-lg);
        background: var(--snd-surface-solid) !important;
        box-shadow: var(--snd-shadow);
        color: var(--snd-ink) !important;
    }
    .dashboard-kpi-card:hover {
        border-color: rgba(151, 134, 238, .28) !important;
        box-shadow: var(--snd-shadow-hover);
        transform: translateY(-2px);
    }
    .dashboard-kpi-card .metric-header { margin-bottom: 12px; align-items: center; }
    .dashboard-kpi-card .icon-bubble {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: var(--snd-primary-soft) !important;
        color: var(--snd-primary-deep) !important;
        font-size: .95rem;
    }
    .dashboard-kpi-card .metric-pill {
        padding: 4px 8px;
        border-radius: 7px;
        background: var(--snd-primary-soft) !important;
        color: var(--snd-primary-deep) !important;
        font-size: .56rem;
        letter-spacing: .06em;
    }
    .dashboard-kpi-card h3 {
        margin-bottom: 3px;
        color: var(--snd-ink) !important;
        font-size: 1.28rem;
        font-weight: 600;
    }
    .dashboard-kpi-card p {
        color: var(--snd-muted) !important;
        font-size: .67rem;
        font-weight: 500;
    }
    .dashboard-analytics-column,
    .dashboard-category-column { min-width: 0; }
    .dashboard-analytics-column { grid-column: 2; grid-row: 2; }
    .dashboard-category-column { grid-column: 3; grid-row: 2; }
    .dashboard-main-grid .widget-card { height: 100%; margin-bottom: 0 !important; display: flex; flex-direction: column; }
    .dashboard-main-grid .widget-header { min-height: 70px; gap: 10px; flex-shrink: 0; }
    .dashboard-main-grid .widget-header { padding: 15px 17px; }
    .dashboard-main-grid .widget-header h4 { font-size: .82rem; }
    .dashboard-main-grid .chart-container-lg,
    .dashboard-main-grid .chart-container-sm {
        flex: 1 1 auto;
        height: 377px;
        min-height: 377px;
        padding: 18px 16px 16px;
    }

    .dashboard-equal-row > [class*="col-"] { display: flex; min-width: 0; }
    .dashboard-equal-row .widget-card { width: 100%; display: flex; flex-direction: column; }
    .dashboard-equal-row .widget-header { min-height: 58px; flex-shrink: 0; gap: 10px; }
    .dashboard-equal-row .widget-card > .p-3:not(.border-top) { flex: 1; }
    .dashboard-financial-row .snd-table-scroll { max-height: 350px; overflow: auto; }
    .dashboard-financial-row .widget-card > .p-3 { display: flex; flex-direction: column; justify-content: space-between; }

    @media (max-width: 1399.98px) {
        .dashboard-overview-grid { grid-template-columns: 200px minmax(0, 1fr) minmax(0, 1fr); }
        .dashboard-analytics-column { grid-column: 2 / 4; }
        .dashboard-category-column { grid-column: 2 / 4; grid-row: 3; }
        .dashboard-overview-grid .dashboard-quick-options-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767.98px) {
        .dashboard-overview-grid { grid-template-columns: 1fr; }
        .dashboard-overview-grid .dashboard-hero,
        .dashboard-overview-grid .dashboard-quick-options,
        .dashboard-kpi-rail,
        .dashboard-analytics-column,
        .dashboard-category-column { grid-column: 1; grid-row: auto; }
        .dashboard-hero { align-items: flex-start; flex-direction: column; padding: 18px; }
        .dashboard-hero-status { align-self: flex-start; }
        .dashboard-overview-grid .dashboard-quick-options-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dashboard-kpi-rail { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: auto; }
        .dashboard-kpi-rail-heading { grid-column: 1 / -1; }
    }
    @media (max-width: 575.98px) {
        .dashboard-overview-grid .dashboard-quick-options-grid,
        .dashboard-kpi-rail { grid-template-columns: 1fr; }
        .dashboard-section-header { align-items: flex-start; }
        .dashboard-section-tag { display: none; }
        .dashboard-main-grid .chart-container-lg,
        .dashboard-main-grid .chart-container-sm { height: 300px; min-height: 300px; }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-overview-grid">

    {{-- 1. Dashboard welcome panel --}}
    <section class="dashboard-hero" aria-labelledby="dashboard-welcome-title">
        <div class="dashboard-hero-copy">
            <div class="dashboard-eyebrow">{{ \App\Helpers\CommonHelper::getBrandName() }} � Operations overview</div>
            <h2 id="dashboard-welcome-title">Welcome back, {{ auth()->user()->first_name ?? 'Admin' }}!</h2>
            <p>Here's your service sales, deals, and counter performance at a glance.</p>
        </div>
        <div class="dashboard-hero-status">
            <x-snd-icon name="chart-line" />
            <span>Live dashboard</span>
        </div>
    </section>

    {{-- 2. Quick options --}}
    <section class="dashboard-quick-options widget-card" aria-labelledby="dashboard-quick-options-title">
        <div class="dashboard-section-header">
            <div>
                <h3 id="dashboard-quick-options-title"><x-snd-icon name="zap" /> Quick Options</h3>
                <p>Jump into your most-used POS workflows.</p>
            </div>
            <span class="dashboard-section-tag">Shortcuts</span>
        </div>
        <div class="dashboard-quick-options-grid">
            <a href="{{ route('cart.index') }}" class="dashboard-quick-action dashboard-quick-action-primary">
                <span class="dashboard-quick-action-icon"><x-snd-icon name="shopping-cart" /></span>
                <span class="dashboard-quick-action-copy"><strong>Open POS Terminal</strong><small>Start a new sale</small></span>
                <x-snd-icon name="chevron-right" class="dashboard-quick-arrow" />
            </a>
            <a href="{{ route('products.create') }}" class="dashboard-quick-action">
                <span class="dashboard-quick-action-icon"><x-snd-icon name="plus" /></span>
                <span class="dashboard-quick-action-copy"><strong>Add Product</strong><small>Add a catalog item</small></span>
                <x-snd-icon name="chevron-right" class="dashboard-quick-arrow" />
            </a>
            <a href="{{ route('customers.create') }}" class="dashboard-quick-action">
                <span class="dashboard-quick-action-icon"><x-snd-icon name="user-plus" /></span>
                <span class="dashboard-quick-action-copy"><strong>New Customer</strong><small>Register a customer</small></span>
                <x-snd-icon name="chevron-right" class="dashboard-quick-arrow" />
            </a>
            <a href="{{ route('orders.index') }}" class="dashboard-quick-action">
                <span class="dashboard-quick-action-icon"><x-snd-icon name="receipt" /></span>
                <span class="dashboard-quick-action-copy"><strong>Orders List</strong><small>Review recent sales</small></span>
                <x-snd-icon name="chevron-right" class="dashboard-quick-arrow" />
            </a>
        </div>
    </section>

    {{-- 3. KPI rail and centered analytics --}}
    <section class="dashboard-main-grid" aria-label="Dashboard overview">
        <aside class="dashboard-kpi-rail" aria-label="Sales overview">
            <div class="dashboard-kpi-rail-heading">
                <span>Sales overview</span>
                <span class="dashboard-kpi-rail-caption">Live</span>
            </div>

            <div class="metric-card dashboard-kpi-card card-today">
                <div class="metric-header">
                    <div class="icon-bubble"><x-snd-icon name="zap" /></div>
                    <span class="metric-pill">Today</span>
                </div>
                <div>
                    <h3>{{ config('settings.currency_symbol') }} {{ number_format($sales_today, 2) }}</h3>
                    <p>{{ $orders_today }} orders today</p>
                </div>
            </div>

            <div class="metric-card dashboard-kpi-card card-week">
                <div class="metric-header">
                    <div class="icon-bubble"><x-snd-icon name="chart-line" /></div>
                    <span class="metric-pill">This week</span>
                </div>
                <div>
                    <h3>{{ config('settings.currency_symbol') }} {{ number_format($sales_week, 2) }}</h3>
                    <p>{{ $orders_week }} orders this week</p>
                </div>
            </div>

            <div class="metric-card dashboard-kpi-card card-month">
                <div class="metric-header">
                    <div class="icon-bubble"><x-snd-icon name="calendar-days" /></div>
                    <span class="metric-pill">This month</span>
                </div>
                <div>
                    <h3>{{ config('settings.currency_symbol') }} {{ number_format($sales_month, 2) }}</h3>
                    <p>{{ $orders_month }} orders this month</p>
                </div>
            </div>

            <div class="metric-card dashboard-kpi-card card-customers">
                <div class="metric-header">
                    <div class="icon-bubble"><x-snd-icon name="users" /></div>
                    <span class="metric-pill">Customers</span>
                </div>
                <div>
                    <h3>{{ $customers_count }}</h3>
                    <p>{{ $orders_count }} completed orders</p>
                </div>
            </div>
        </aside>

        <div class="dashboard-analytics-column">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="chart-area" style="color: var(--snd-primary-deep);" /> 12-Month Revenue & Orders Trajectory</h4>
                    <span class="badge-tag">Past 12 Months Analytics</span>
                </div>
                <div class="chart-container-lg">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-category-column">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="chart-pie" style="color: var(--snd-primary-deep);" /> Service Sales Share</h4>
                    <span class="badge-tag">Volume Distribution</span>
                </div>
                <div class="chart-container-sm">
                    @if ($service_sales->isNotEmpty())
                        <canvas id="serviceSalesChart"></canvas>
                    @else
                        <div class="dashboard-chart-empty" role="status">
                            <span class="dashboard-chart-empty-icon"><x-snd-icon name="chart-pie" /></span>
                            <strong>No service sales yet</strong>
                            <span>Service sales will appear here as service orders are recorded.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    </div>

    {{-- ''�''�� 4. Real-time Operations Row ''�''�� --}}
    <div class="row dashboard-equal-row">
        {{-- Recent Orders Feed --}}
        <div class="col-lg-7">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="shopping-bag" style="color: var(--snd-primary-deep);" /> Latest Transactions</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-xs" style="background: var(--snd-primary-soft); border: 1px solid var(--snd-border-strong); color: var(--snd-primary-deep); border-radius: 8px; font-weight: 500;">View All Orders</a>
                </div>
                <div class="table-responsive snd-table-scroll">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_orders as $ord)
                                <tr>
                                    <td>
                                        <span style="font-weight: 500; color: var(--snd-ink);">#ORD-{{ $ord->id }}</span>
                                        @if($ord->srb_invoice_id)
                                            <div class="status-pill srb">{{ $ord->srb_invoice_id }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-badge">
                                                {{ strtoupper(substr($ord->customer->first_name ?? 'Walk-in', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--snd-ink);">
                                                    {{ $ord->customer ? $ord->customer->first_name . ' ' . $ord->customer->last_name : 'Walk-in Customer' }}
                                                </div>
                                                <small style="color: var(--snd-primary-deep);">{{ $ord->customer->phone ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light" style="font-size: 0.8rem; font-weight: 600; background: var(--snd-primary-soft); border: 1px solid var(--snd-border-strong); color: var(--snd-primary-deep);">{{ $ord->items->sum('quantity') }} items</span>
                                    </td>
                                    <td>
                                        <strong style="color: var(--snd-primary-deep); font-size: 0.92rem; font-weight: 600;">
                                            {{ config('settings.currency_symbol') }} {{ number_format($ord->total_amount, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="status-pill paid"><x-snd-icon name="circle-check" class="mr-1" /> Paid</span>
                                    </td>
                                    <td style="color: var(--snd-primary-deep); font-size: 0.8rem;">
                                        {{ $ord->created_at ? $ord->created_at->format('d M, h:i A') : '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No recent transactions recorded</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Selling & Inventory Health --}}
        <div class="col-lg-5">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="flame" style="color: var(--snd-primary-deep);" /> Top Selling Items</h4>
                    <span class="badge-tag">High Demand</span>
                </div>
                <div class="p-3">
                    @forelse($top_selling_items as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-weight: 600; font-size: 0.88rem; color: var(--snd-ink);">{{ $item['name'] }}</span>
                                <span style="font-weight: 500; font-size: 0.86rem; color: var(--snd-primary-deep);">{{ $item['quantity'] }} sold</span>
                            </div>
                            <div class="progress-bar-custom">
                                <div class="progress-fill-orange" style="width: {{ min(100, max(20, $item['quantity'] * 2)) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No sales items recorded</div>
                    @endforelse
                </div>

                @if($low_stock_products->isNotEmpty())
                    <div class="p-3 border-top" style="background: var(--snd-primary-soft);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size: 0.82rem; font-weight: 600; color: #dc2626;"><x-snd-icon name="triangle-alert" class="mr-1" /> Low Stock Alert</span>
                            <a href="{{ route('products.index') }}" class="small" style="color: #dc2626; font-weight: 600;">Manage Inventory</a>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($low_stock_products as $lsp)
                                <span class="badge badge-danger p-2" style="border-radius: 6px; font-weight: 500; font-size: 0.76rem;">
                                    {{ $lsp->name }} ({{ $lsp->quantity }} left)
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ''�''�� 5. Financial Breakdown & POS Summary Row ''�''�� --}}
    <div class="row dashboard-equal-row dashboard-financial-row">
        {{-- Detailed Monthly Breakdown --}}
        <div class="col-lg-8">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="table" style="color: var(--snd-primary-deep);" /> Monthly Financial Breakdown</h4>
                    <span class="badge-tag">Audited Log</span>
                </div>
                <div class="table-responsive snd-table-scroll">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Billing Month</th>
                                <th>Total Orders</th>
                                <th>Gross Revenue</th>
                                <th>Avg Ticket Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthly_sales as $month)
                                <tr>
                                    <td><strong>{{ $month['label'] }}</strong></td>
                                    <td><span class="badge badge-light" style="font-size: 0.82rem; font-weight: 600; background: var(--snd-primary-soft); border: 1px solid var(--snd-border-strong); color: var(--snd-primary-deep);">{{ $month['orders'] }} orders</span></td>
                                    <td><strong style="color: var(--snd-ink);">{{ config('settings.currency_symbol') }} {{ number_format($month['sales'], 2) }}</strong></td>
                                    <td style="color: var(--snd-primary-deep);">{{ config('settings.currency_symbol') }} {{ number_format($month['orders'] ? $month['sales'] / $month['orders'] : 0, 2) }}</td>
                                    <td>
                                        <span class="status-pill paid"><x-snd-icon name="check-check" class="mr-1" /> Closed</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Monthly POS Summary Metric Widget --}}
        <div class="col-lg-4">
            <div class="widget-card">
                <div class="widget-header">
                    <h4><x-snd-icon name="calculator" style="color: var(--snd-primary-deep);" /> Current Month POS Audit</h4>
                    <span class="badge-tag">{{ now()->format('M Y') }}</span>
                </div>
                <div class="p-3">
                    <div class="summary-item">
                        <span class="label"><x-snd-icon name="box" style="color: var(--snd-primary-deep);" /> Items Dispatched</span>
                        <span class="value">{{ number_format($pos_summary['items_sold']) }} units</span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><x-snd-icon name="receipt" style="color: var(--snd-primary-deep);" /> Average Basket Size</span>
                        <span class="value">{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['average_order'], 2) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><x-snd-icon name="percent" style="color: var(--snd-primary-deep);" /> Tax Collected (SRB)</span>
                        <span class="value" style="color: var(--snd-success);">{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['tax'], 2) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label"><x-snd-icon name="tag" style="color: var(--snd-primary-deep);" /> Discounts Claimed</span>
                        <span class="value" style="color: var(--snd-primary-deep);">{{ config('settings.currency_symbol') }} {{ number_format($pos_summary['discount'], 2) }}</span>
                    </div>
                    <div class="summary-item" style="background: var(--snd-primary-soft); border: 1px solid var(--snd-border-strong); padding: 12px 14px; border-radius: 12px; margin-top: 10px;">
                        <span class="label" style="font-weight: 600; color: var(--snd-ink);"><x-snd-icon name="wallet" style="color: var(--snd-primary-deep);" /> Total Net Inflow</span>
                        <span class="value" style="color: var(--snd-primary-deep); font-size: 1.1rem; font-weight: 600;">{{ config('settings.currency_symbol') }} {{ number_format($sales_month, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    // 1. Monthly Sales & Orders Combined Bar + Area Chart
    const ctxMonthly = document.getElementById('monthlySalesChart');
    if (ctxMonthly) {
        const labels = monthly.map(m => m.label);
        const salesData = monthly.map(m => m.sales);
        const ordersData = monthly.map(m => m.orders);

        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gross Sales (' + currency + ')',
                        data: salesData,
                        backgroundColor: 'rgba(151, 134, 238, 0.85)',
                        borderColor: themeColor('--snd-primary'),
                        borderWidth: 1,
                        borderRadius: 8,
                        barPercentage: 0.6,
                        yAxisID: 'ySales'
                    },
                    {
                        label: 'Orders Count',
                        data: ordersData,
                        type: 'line',
                        borderColor: themeColor('--snd-primary-hover'),
                        backgroundColor: 'rgba(151, 134, 238, 0.12)',
                        borderWidth: 3,
                        pointBackgroundColor: themeColor('--snd-primary-hover'),
                        pointBorderColor: '#ffffff',
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
                            font: { family: 'system-ui', size: 12, weight: '600' },
                            color: themeColor('--snd-muted'),
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 26, 26, 0.94)',
                        titleFont: { family: 'system-ui', size: 13, weight: '700' },
                        bodyFont: { family: 'system-ui', size: 12 },
                        padding: 12,
                        cornerRadius: 10,
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
                        ticks: { font: { family: 'system-ui', size: 11 }, color: themeColor('--snd-muted') }
                    },
                    ySales: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: themeColor('--snd-workspace-deep') },
                        ticks: {
                            font: { family: 'system-ui', size: 11 },
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
                            font: { family: 'system-ui', size: 11 },
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

        const chartColors = [
            themeColor('--snd-primary'), themeColor('--snd-primary-hover'), themeColor('--snd-primary-deep'), themeColor('--snd-info'),
            themeColor('--snd-success'), themeColor('--snd-warning'), themeColor('--snd-danger'), '#5ac8fa'
        ];

        new Chart(ctxService, {
            type: 'doughnut',
            data: {
                labels: serviceKeys,
                datasets: [{
                    data: serviceValues,
                    backgroundColor: chartColors.slice(0, serviceKeys.length),
                    borderWidth: 2,
                    borderColor: '#ffffff',
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
                                family: 'system-ui',
                                size: 11,
                                weight: '500'
                            },
                            color: '#626b7b',
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 26, 26, 0.94)',
                        padding: 12,
                        cornerRadius: 10,
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
});
</script>
@endsection


