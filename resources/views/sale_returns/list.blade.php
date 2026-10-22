@extends('layouts.admin')

@section('title', 'Sale Return Records')
@section('content-header', 'Sale Return List')

@section('content-actions')
    @can('sale_returns.view')
    <a href="{{ route('sale-returns.index') }}" class="btn btn-primary mr-2" style="border-radius:10px;font-weight:700;">
        <i class="fas fa-undo-alt mr-1"></i> Process New Return
    </a>
    @endcan
    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;font-weight:600;">
        <i class="fas fa-cash-register mr-1"></i> POS Screen
    </a>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 Styling */
.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1.5px solid var(--snd-border-strong) !important;
    border-radius: 10px !important;
    padding: 5px 10px !important;
    background: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.2s ease !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--snd-primary) !important;
    box-shadow: 0 0 0 3px var(--snd-primary-ring) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1a1a2e !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    line-height: normal !important;
    padding-left: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    right: 8px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: var(--snd-primary-deep) transparent transparent transparent !important;
    border-width: 6px 5px 0 5px !important;
}
.select2-dropdown {
    border: 1.5px solid var(--snd-border-strong) !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(15,23,42,0.12) !important;
    overflow: hidden !important;
    z-index: 9999 !important;
}
.select2-search--dropdown {
    padding: 8px !important;
}
.select2-search--dropdown .select2-search__field {
    border: 1.5px solid var(--snd-border-strong) !important;
    border-radius: 8px !important;
    padding: 6px 10px !important;
    font-size: 0.85rem !important;
}
.select2-results__option {
    padding: 8px 12px !important;
    font-size: 0.86rem !important;
    font-weight: 600 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: var(--snd-button-primary) !important;
    color: var(--snd-button-primary-text) !important;
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: var(--snd-primary-wash) !important;
    color: var(--snd-primary-deep) !important;
    font-weight: 700 !important;
}

/* Stat Cards */
.stat-pill-card {
    background: #ffffff;
    border: 1px solid var(--snd-border);
    border-radius: 14px;
    padding: 16px 20px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-pill-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.sale-return-list-filter-card,
.sale-return-results-card {
    border: 1px solid var(--snd-border) !important;
    border-radius: 16px !important;
    box-shadow: var(--snd-shadow);
}
.sale-return-list-filter-card .card-header {
    display: flex;
    align-items: center;
    min-height: 58px;
    padding: 14px 18px;
    background: #fff;
    border-bottom: 1px solid var(--snd-border);
    border-radius: 16px 16px 0 0;
}
.sale-return-list-filter-heading {
    margin: 0;
    color: var(--snd-ink);
    font-size: .9rem;
    font-weight: 700;
}
.sale-return-list-filter-heading .snd-icon,
.sale-return-list-filter-heading i {
    margin-right: 7px;
    color: var(--snd-primary-deep);
}
.sale-return-list-filter-subtitle {
    margin: 3px 0 0 22px;
    color: var(--snd-muted);
    font-size: .72rem;
}
.sale-return-list-filter-card .card-body {
    padding: 16px 18px 18px;
}
.sale-return-list-filter-form {
    display: grid;
    grid-template-columns: minmax(220px, 1.15fr) minmax(300px, 1.55fr) minmax(150px, .85fr) minmax(150px, .85fr) auto;
    align-items: end;
    gap: 12px;
    margin: 0;
}
.sale-return-list-filter-field {
    min-width: 0;
}
.sale-return-list-filter-field > label,
.sale-return-list-filter-action-label {
    display: block;
    margin: 0 0 6px;
    color: var(--snd-muted);
    font-size: .72rem;
    font-weight: 700;
}
.sale-return-list-filter-form > .sale-return-list-filter-field > .form-control {
    height: 40px;
    min-height: 40px;
}
.sale-return-list-filter-form .select2-container--default .select2-selection--single {
    height: 40px !important;
}
.sale-return-list-filter-form .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}
.sale-return-list-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
}
.sale-return-list-filter-actions .btn-filter-returns {
    min-width: 88px;
    height: 40px;
    border-radius: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.sale-return-list-filter-actions .btn-clear-return-filters {
    display: inline-flex;
    flex: 0 0 40px;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    margin: 0;
    padding: 0;
    border-radius: 10px;
}
@media (max-width: 1199.98px) {
    .sale-return-list-filter-form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .sale-return-list-filter-actions {
        grid-column: 1 / -1;
        justify-content: flex-start;
    }
}
@media (max-width: 575.98px) {
    .sale-return-list-filter-card .card-body {
        padding: 14px;
    }
    .sale-return-list-filter-form {
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }
    .sale-return-list-filter-actions {
        grid-column: auto;
    }
    .sale-return-list-filter-actions .btn-filter-returns {
        flex: 1 1 auto;
    }
}

/* Responsive Table & Action Buttons */
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}
th.col-actions,
td.col-actions {
    min-width: 95px !important;
    width: 95px !important;
    white-space: nowrap !important;
    text-align: center !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
}
/* Modal styling */
body.modal-open .main-sidebar {
    z-index: 1030 !important;
}
.modal-backdrop {
    z-index: 1040 !important;
    background-color: rgba(15, 23, 42, 0.65) !important;
    backdrop-filter: blur(3px);
}
.modal {
    z-index: 1055 !important;
}
.return-modal-header {
    background: linear-gradient(135deg, #6758bd 0%, #6758bd 100%) !important;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 1.25rem 1.5rem !important;
}
.return-meta-box {
    background: var(--snd-surface-soft);
    border: 1px solid var(--snd-border);
    border-radius: 10px;
    padding: 10px 14px;
    height: 100%;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(! $isAdmin && $branches->isEmpty())
        <div class="alert alert-warning mb-3" style="border-radius: 10px;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Notice:</strong> Your account does not have any branches assigned. Please contact the administrator to grant branch access.
        </div>
    @endif

    {{-- Top Summary Stats --}}
    <div class="row mb-3">
        <!-- <div class="col-xl-4 col-md-6 col-12 mb-2 mb-xl-0">
            <div class="stat-pill-card">
                <div class="stat-icon-wrapper" style="background:#f0edff;color:#6758bd;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <div class="text-muted font-weight-bold text-uppercase" style="font-size:0.75rem;letter-spacing:0.04em;">Total Return Orders</div>
                    <div class="font-weight-bold" style="font-size:1.45rem;color:#1a1a2e;">
                        {{ number_format($totalReturns) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 col-12 mb-2 mb-xl-0">
            <div class="stat-pill-card">
                <div class="stat-icon-wrapper" style="background:#fff0ef;color:#c9342d;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="text-muted font-weight-bold text-uppercase" style="font-size:0.75rem;letter-spacing:0.04em;">Total Refunded Amount</div>
                    <div class="font-weight-bold" style="font-size:1.45rem;color:#c9342d;font-family:monospace;">
                        {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($totalRefunds, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12 col-12">
            <div class="stat-pill-card">
                <div class="stat-icon-wrapper" style="background:#eaf9ee;color:#16a34a;">
                    <i class="fas fa-building"></i>
                </div>
                <div class="w-100 overflow-hidden">
                    <div class="text-muted font-weight-bold text-uppercase" style="font-size:0.75rem;letter-spacing:0.04em;">Branch Scope</div>
                    <div class="font-weight-bold text-truncate" style="font-size:1.1rem;color:#1f7a3d;" title="{{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Specific Branch') : ($isAdmin ? 'All Branches' : 'All Assigned Branches') }}">
                        @if($selectedBranchId)
                            {{ $branches->firstWhere('id', $selectedBranchId)?->name ?? 'Assigned Branch' }}
                        @else
                            {{ $isAdmin ? '🏢 All Branches (Consolidated)' : '🏢 All My Assigned Branches' }}
                        @endif
                    </div>
                </div>
            </div>
        </div> -->
    </div>

    {{-- Dedicated return filters --}}
    <section class="card sale-return-list-filter-card mb-3" aria-labelledby="sale-return-list-filter-heading">
        <div class="card-header">
            <div>
                <h2 class="sale-return-list-filter-heading" id="sale-return-list-filter-heading"><i class="fas fa-filter"></i> Filter Returns</h2>
                <p class="sale-return-list-filter-subtitle">Narrow return records by branch, reference, or date.</p>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sale-returns.list') }}" class="sale-return-list-filter-form">
                {{-- Branch Filter --}}
                <div class="sale-return-list-filter-field">
                    <label for="branch-select">Branch</label>
                    <select name="branch_id" id="branch-select" class="form-control select2" style="width: 100%;">
                        @if($isAdmin)
                            <option value="all" {{ ($selectedBranchId === null || request('branch_id') === 'all') ? 'selected' : '' }}>
                                🏢 All Branches (Consolidated)
                            </option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                            @endforeach
                        @else
                            @if($branches->count() > 1)
                                <option value="all" {{ ($selectedBranchId === null || request('branch_id') === 'all') ? 'selected' : '' }}>
                                    🏢 All My Assigned Branches
                                </option>
                            @endif
                            @forelse($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                            @empty
                                <option value="" disabled selected>No branch assigned</option>
                            @endforelse
                        @endif
                    </select>
                </div>

                {{-- Search Filter --}}
                <div class="sale-return-list-filter-field">
                    <label for="return-list-search">Search</label>
                    <div class="snd-search-control">
                        <input id="return-list-search" type="text"
                               name="search"
                               class="form-control"
                               placeholder="Return #, SRB invoice #, order #, phone..."
                               aria-label="Search returns by return number, invoice, order, or phone"
                               value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary snd-search-action" aria-label="Search returns" title="Search returns">
                            <x-snd-icon name="search" />
                        </button>
                    </div>
                </div>

                {{-- Date Range --}}
                <div class="sale-return-list-filter-field">
                    <label for="return-list-start-date">Date From</label>
                    <input type="date"
                           id="return-list-start-date"
                           name="start_date"
                           class="form-control"
                           value="{{ request('start_date') }}"
                           title="Start Date"
                           style="border-radius:10px;font-size:0.88rem;">
                </div>
                <div class="sale-return-list-filter-field">
                    <label for="return-list-end-date">Date To</label>
                    <input type="date"
                           id="return-list-end-date"
                           name="end_date"
                           class="form-control"
                           value="{{ request('end_date') }}"
                           title="End Date"
                           style="border-radius:10px;font-size:0.88rem;">
                </div>

                {{-- Action Buttons --}}
                <div class="sale-return-list-filter-field">
                    <span class="sale-return-list-filter-action-label">&nbsp;</span>
                    <div class="sale-return-list-filter-actions">
                    <button type="submit" class="btn btn-primary btn-filter-returns">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    @if(request('search') || request('start_date') || request('end_date') || (request('branch_id') && request('branch_id') !== 'all'))
                        <a href="{{ route('sale-returns.list') }}" class="btn btn-secondary btn-clear-return-filters" title="Clear filters" aria-label="Clear filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Return records --}}
    <div class="card sale-return-results-card shadow-sm" style="overflow:hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width:960px;">
                    <thead style="background:var(--snd-surface-soft);color:#505469;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.04em;">
                        <tr>
                            <th class="pl-4 py-3">Return # / SRB</th>
                            <th class="py-3">Original Order</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Branch & Counter</th>
                            <th class="py-3">Returned Items</th>
                            <th class="py-3 text-right">Refund Total</th>
                            <th class="py-3 text-center">Method</th>
                            <th class="py-3">Cashier & Date</th>
                            <th class="pr-3 py-3 text-center col-actions" style="width: 100px; min-width: 95px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $ret)
                            @php
                                $totalReturnedQty = $ret->items->sum('quantity');
                                $itemsCount = $ret->items->count();

                                // Format refund method badge
                                $method = strtolower($ret->refund_method ?? 'cash');
                                $methodBadge = match($method) {
                                    'card' => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'label' => 'Card Reversal'],
                                    'credit' => ['bg' => '#fff5e6', 'color' => '#b45309', 'label' => 'Store Credit'],
                                    default => ['bg' => '#eaf9ee', 'color' => '#166534', 'label' => 'Cash Refund'],
                                };

                                // Package return data for modal & print
                                $returnJson = [
                                    'id' => $ret->id,
                                    'return_no' => $ret->return_no,
                                    'created_at' => $ret->created_at->format('d M, Y h:i A'),
                                    'order_invoice_no' => $ret->order ? $ret->order->getInvoiceNumber() : ('#' . $ret->order_id),
                                    'customer_name' => $ret->getCustomerName(),
                                    'customer_phone' => $ret->customer?->phone ?? 'N/A',
                                    'branch_name' => $ret->branch?->name ?? 'Main Branch',
                                    'counter_name' => $ret->counter?->name ?? 'Counter 1',
                                    'cashier_name' => $ret->user ? ($ret->user->first_name . ' ' . $ret->user->last_name) : 'Staff',
                                    'refund_subtotal' => number_format($ret->refund_subtotal, 2),
                                    'refund_tax' => number_format($ret->refund_tax, 2),
                                    'refund_discount' => number_format($ret->refund_discount, 2),
                                    'refund_total' => number_format($ret->refund_total, 2),
                                    'refund_method' => $methodBadge['label'],
                                    'card_holder_name' => $ret->card_holder_name,
                                    'card_last_four' => $ret->card_last_four,
                                    'receiving_account_name' => $ret->receivingAccount?->name,
                                    'return_reason' => $ret->return_reason ?? 'Not specified',
                                    'notes' => $ret->notes ?? '',
                                    'srb_invoice_id' => $ret->srb_invoice_id,
                                    'srb_qr_code_link' => $ret->srb_qr_code_link,
                                    'items' => $ret->items->map(function ($it) {
                                        return [
                                            'item_name' => $it->item_name,
                                            'item_type' => $it->item_type == 2 ? 'Deal' : ($it->item_type == 1 ? 'Service' : 'Product'),
                                            'quantity' => $it->quantity,
                                            'unit_price' => number_format($it->unit_price, 2),
                                            'refund_amount' => number_format($it->refund_amount, 2),
                                        ];
                                    })->values()->all(),
                                ];
                            @endphp
                            <tr style="border-bottom:1px solid var(--snd-surface-soft); transition: background 0.15s ease;">
                                {{-- Return # & SRB Badge --}}
                                <td class="pl-4 py-3 align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2 text-primary font-weight-bold" style="font-size:0.92rem;font-family:monospace;">
                                            <i class="fas fa-undo-alt mr-1 text-muted"></i>{{ $ret->return_no }}
                                        </div>
                                    </div>
                                    @if($ret->srb_invoice_id)
                                        <div class="mt-1">
                                            <span class="badge px-2 py-1 font-weight-bold" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;border-radius:6px;font-size:0.75rem;" title="SRB Fiscal Invoice ID: {{ $ret->srb_invoice_id }}">
                                                <i class="fas fa-check-circle mr-1"></i> SRB: {{ $ret->srb_invoice_id }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Original Order --}}
                                <td class="py-3 align-middle">
                                    <div class="font-weight-bold" style="color:#1a1a2e;">
                                        {{ $ret->order ? $ret->order->getInvoiceNumber() : ('#' . $ret->order_id) }}
                                    </div>
                                    @if($ret->order)
                                        <small class="text-muted">
                                            Inv Total: {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($ret->order->total(), 2) }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Customer --}}
                                <td class="py-3 align-middle">
                                    <div class="font-weight-bold text-dark">
                                        {{ $ret->getCustomerName() }}
                                    </div>
                                    @if($ret->customer && $ret->customer->phone)
                                        <small class="text-muted"><i class="fas fa-phone mr-1" style="font-size:0.75rem;"></i>{{ $ret->customer->phone }}</small>
                                    @else
                                        <small class="text-muted">Walk-in</small>
                                    @endif
                                </td>

                                {{-- Branch & Counter --}}
                                <td class="py-3 align-middle">
                                    <div class="font-weight-bold text-dark" style="font-size:0.86rem;">
                                        <i class="fas fa-store-alt text-primary mr-1" style="font-size:0.8rem;"></i>{{ $ret->branch ? $ret->branch->name : 'N/A' }}
                                    </div>
                                    @if($ret->counter)
                                        <small class="text-muted"><i class="fas fa-desktop mr-1" style="font-size:0.75rem;"></i>{{ $ret->counter->name }}</small>
                                    @endif
                                </td>

                                {{-- Returned Items --}}
                                <td class="py-3 align-middle">
                                    <span class="badge px-2 py-1" style="background:#f0edff;color:#6758bd;border:1px solid #d9d4f5;border-radius:6px;font-size:0.8rem;font-weight:700;">
                                        <i class="fas fa-boxes mr-1"></i> {{ $totalReturnedQty }} Item{{ $totalReturnedQty > 1 ? 's' : '' }} ({{ $itemsCount }} type{{ $itemsCount > 1 ? 's' : '' }})
                                    </span>
                                    <div class="text-truncate text-muted mt-1" style="max-width:210px;font-size:0.78rem;" title="{{ $ret->items->pluck('item_name')->implode(', ') }}">
                                        {{ $ret->items->pluck('item_name')->implode(', ') }}
                                    </div>
                                </td>

                                {{-- Refund Total --}}
                                <td class="py-3 align-middle text-right">
                                    <div class="font-weight-bold" style="font-size:0.98rem;color:#c9342d;font-family:monospace;">
                                        - {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($ret->refund_total, 2) }}
                                    </div>
                                    @if($ret->refund_tax > 0)
                                        <small class="text-muted d-block" style="font-size:0.72rem;">Incl. Tax: {{ number_format($ret->refund_tax, 2) }}</small>
                                    @endif
                                </td>

                                {{-- Method --}}
                                <td class="py-3 align-middle text-center">
                                    @if($method === 'card')
                                        <span class="badge px-2.5 py-1" style="background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;border-radius:20px;font-size:0.76rem;font-weight:700;">
                                            <i class="fas fa-credit-card mr-1"></i> Card
                                            @if($ret->card_last_four)
                                                (••{{ $ret->card_last_four }})
                                            @endif
                                        </span>
                                        @if($ret->receivingAccount)
                                            <div class="text-muted small mt-1" style="font-size:0.68rem;line-height:1.2;">
                                                <i class="fas fa-university text-primary mr-1"></i>{{ $ret->receivingAccount->name }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge px-2.5 py-1" style="background:{{ $methodBadge['bg'] }};color:{{ $methodBadge['color'] }};border-radius:20px;font-size:0.76rem;font-weight:700;">
                                            {{ $methodBadge['label'] }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Cashier & Date --}}
                                <td class="py-3 align-middle">
                                    <div class="text-dark font-weight-bold" style="font-size:0.84rem;">
                                        <i class="fas fa-user-check text-muted mr-1"></i>{{ $ret->user ? ($ret->user->first_name . ' ' . $ret->user->last_name) : 'Staff' }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="far fa-clock mr-1"></i>{{ $ret->created_at->format('d M, Y h:i A') }}
                                    </small>
                                </td>

                                {{-- Actions --}}
                                <td class="pr-3 py-3 align-middle text-center col-actions">
                                    <div class="snd-table-actions">
                                        {{-- View Details Modal --}}
                                        <button type="button"
                                                class="btn snd-table-action snd-table-action--secondary btn-view-return-details"
                                                data-return='@json($returnJson)'
                                                title="View Return Details">
                                            <x-snd-icon name="eye" />
                                        </button>

                                        {{-- Print Receipt --}}
                                        <button type="button"
                                                class="btn snd-table-action btn-print-return-direct"
                                                data-return='@json($returnJson)'
                                                title="Print Thermal Return Receipt">
                                            <x-snd-icon name="printer" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div style="font-size:3rem;color:var(--snd-border-strong);"><i class="fas fa-clipboard-check"></i></div>
                                    <h5 class="mt-3 font-weight-bold" style="color:#626b7b;">No Sale Returns Found</h5>
                                    <p class="mb-3 text-muted" style="font-size:0.88rem;">
                                        @if(request('search') || request('start_date') || request('end_date') || (request('branch_id') && request('branch_id') !== 'all'))
                                            No records match your selected filter criteria. Try clearing filters.
                                        @else
                                            No returns have been processed yet for this branch.
                                        @endif
                                    </p>
                                    <a href="{{ route('sale-returns.index') }}" class="btn btn-primary btn-sm px-3" style="border-radius:8px;font-weight:700;">
                                        <i class="fas fa-undo-alt mr-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($returns->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 snd-pagination-inline" style="border-top:1px solid var(--snd-surface-soft);border-radius:0 0 16px 16px;">
                <div class="text-muted" style="font-size:0.84rem;">
                    Showing <strong>{{ $returns->firstItem() }}</strong> to <strong>{{ $returns->lastItem() }}</strong> of <strong>{{ $returns->total() }}</strong> returns
                </div>
                <div>
                    {{ $returns->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Return Details Modal --}}
<div class="modal fade" id="returnDetailsModal" tabindex="-1" role="dialog" aria-labelledby="returnDetailsModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="modal-header return-modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title font-weight-bold mb-1" id="returnDetailsModalTitle">
                        <i class="fas fa-undo-alt mr-2"></i> Sale Return Details: <span id="modalReturnNo"></span>
                    </h5>
                    <div style="font-size:0.85rem;opacity:0.9;">
                        Original Order Invoice: <span id="modalOrderNo" class="font-weight-bold"></span> &bull; Date: <span id="modalDate"></span>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:0.9;font-size:1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4" style="background:#ffffff;">
                {{-- Meta Information Grid --}}
                <div class="row mb-3">
                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <div class="return-meta-box">
                            <span class="text-muted font-weight-bold text-uppercase d-block" style="font-size:0.7rem;letter-spacing:0.04em;">Customer</span>
                            <div id="modalCustomerName" class="font-weight-bold text-dark" style="font-size:0.92rem;"></div>
                            <small id="modalCustomerPhone" class="text-muted"></small>
                        </div>
                    </div>
                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <div class="return-meta-box">
                            <span class="text-muted font-weight-bold text-uppercase d-block" style="font-size:0.7rem;letter-spacing:0.04em;">Branch & Counter</span>
                            <div id="modalBranchName" class="font-weight-bold text-dark" style="font-size:0.92rem;"></div>
                            <small id="modalCounterName" class="text-muted"></small>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="return-meta-box">
                            <span class="text-muted font-weight-bold text-uppercase d-block" style="font-size:0.7rem;letter-spacing:0.04em;">Cashier / Processed By</span>
                            <div id="modalCashierName" class="font-weight-bold text-dark" style="font-size:0.92rem;"></div>
                            <small id="modalRefundMethod" class="badge badge-light px-2 py-0.5 mt-1 border"></small>
                        </div>
                    </div>
                </div>

                {{-- SRB Fiscal Badge Section if present --}}
                <div id="modalSrbSection" class="p-3 mb-3" style="display:none;background:#eaf9ee;border:1px solid #c7ebd0;border-radius:12px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <img src="{{ asset('images/srb.jfif') }}" alt="SRB Logo" style="width:45px;height:45px;object-fit:contain;margin-right:12px;">
                            <div>
                                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size:0.78rem;">
                                    <i class="fas fa-check-circle mr-1"></i> SRB Fiscalised Return
                                </span>
                                <div class="font-weight-bold mt-1" style="font-size:0.9rem;color:#1f7a3d;">
                                    SRB Invoice ID: <span id="modalSrbInvoiceId" style="font-family:monospace;"></span>
                                </div>
                            </div>
                        </div>
                        <div id="modalSrbQrWrapper" style="display:none;">
                            <img id="modalSrbQrCode" src="" alt="QR" style="width:60px;height:60px;border-radius:6px;border:1px solid var(--snd-border-strong);">
                        </div>
                    </div>
                </div>

                {{-- Returned Items Table --}}
                <div class="mb-3">
                    <h6 class="font-weight-bold text-dark mb-2" style="font-size:0.9rem;">
                        <i class="fas fa-list-ul mr-1 text-primary"></i> Returned Items Breakdown
                    </h6>
                    <div class="table-responsive" style="border:1px solid var(--snd-border);border-radius:10px;overflow:hidden;">
                        <table class="table table-sm table-striped mb-0" style="font-size:0.86rem;">
                            <thead style="background:var(--snd-surface-soft);color:#505469;">
                                <tr>
                                    <th class="py-2 pl-3">Item Name</th>
                                    <th class="py-2 text-center">Type</th>
                                    <th class="py-2 text-center">Returned Qty</th>
                                    <th class="py-2 text-right">Unit Rate</th>
                                    <th class="py-2 pr-3 text-right">Refund Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsTableBody">
                                {{-- Injected via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Reason & Financial Summary --}}
                <div class="row align-items-center">
                    <div class="col-md-7 col-12 mb-3 mb-md-0">
                        <div class="p-3" style="background:#fafafa;border:1px solid #e5e7eb;border-radius:10px;">
                            <div class="mb-2">
                                <span class="text-muted font-weight-bold" style="font-size:0.75rem;text-transform:uppercase;">Reason for Return:</span>
                                <div id="modalReturnReason" class="font-weight-bold text-dark mt-0.5" style="font-size:0.88rem;"></div>
                            </div>
                            <div id="modalNotesWrapper" style="display:none;">
                                <span class="text-muted font-weight-bold" style="font-size:0.75rem;text-transform:uppercase;">Notes:</span>
                                <div id="modalNotes" class="text-secondary mt-0.5" style="font-size:0.84rem;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 col-12">
                        <div class="p-3" style="background:var(--snd-surface-soft);border:1px solid var(--snd-border-strong);border-radius:12px;">
                            <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                                <span class="text-muted">Items Subtotal:</span>
                                <span class="font-weight-bold">{{ config('settings.currency_symbol', 'PKR') }} <span id="modalRefundSubtotal">0.00</span></span>
                            </div>
                            <div id="modalDiscountRow" class="d-flex justify-content-between mb-1 text-danger" style="font-size:0.85rem;display:none;">
                                <span>Discount Adj.:</span>
                                <span class="font-weight-bold">- {{ config('settings.currency_symbol', 'PKR') }} <span id="modalRefundDiscount">0.00</span></span>
                            </div>
                            <div id="modalTaxRow" class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;display:none;">
                                <span>Tax Refunded:</span>
                                <span class="font-weight-bold">+ {{ config('settings.currency_symbol', 'PKR') }} <span id="modalRefundTax">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top align-items-baseline">
                                <span class="font-weight-bold text-dark" style="font-size:0.95rem;">Total Refund:</span>
                                <span class="font-weight-bold" style="font-size:1.35rem;color:#c9342d;font-family:monospace;">
                                    {{ config('settings.currency_symbol', 'PKR') }} <span id="modalRefundTotal">0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius:8px;font-weight:600;">
                    Close
                </button>
                <button type="button" id="modalBtnPrint" class="btn btn-primary" style="border-radius:8px;font-weight:700;">
                    <i class="fas fa-print mr-1"></i> Print Return Slip
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Thermal Print Area for Receipt Printing --}}
<div id="thermal-print-template" style="display:none;">
    <div id="printAreaContent" style="text-align:center;font-family:monospace;padding:10px;font-size:12px;color:#000;">
        <h3 style="margin:0;font-size:16px;font-weight:bold;">{{ \App\Helpers\CommonHelper::getBrandName() }}</h3>
        <p style="margin:2px 0;">{{ \App\Helpers\CommonHelper::getBrandSubtitle() }}</p>
        <p style="margin:2px 0;" id="prtBranchName"></p>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">
        <h4 style="margin:4px 0;font-size:14px;font-weight:bold;">*** SALE RETURN RECEIPT ***</h4>
        <p style="margin:2px 0;">Return #: <span id="prtReturnNo"></span></p>
        <p style="margin:2px 0;">Original Inv #: <span id="prtOrderNo"></span></p>
        <p style="margin:2px 0;">Date: <span id="prtDate"></span></p>
        <p style="margin:2px 0;">Customer: <span id="prtCustomer"></span></p>
        <p style="margin:2px 0;">Cashier: <span id="prtCashier"></span></p>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">
        <table style="width:100%;font-size:11px;text-align:left;">
            <thead>
                <tr>
                    <th style="padding:2px 0;">Item</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody id="prtItemsBody"></tbody>
        </table>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">
        <div style="display:flex;justify-content:space-between;margin:2px 0;">
            <span>Subtotal:</span>
            <span id="prtSubtotal"></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin:2px 0;font-weight:bold;font-size:14px;">
            <span>TOTAL REFUND:</span>
            <span id="prtTotal"></span>
        </div>
        <p style="margin:6px 0 2px;">Method: <span id="prtMethod"></span></p>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">

        {{-- SRB Fiscal Section --}}
        <div id="prtSrbSection" style="display:none;margin-top:8px;padding-top:6px;border-top:1px dashed #000;text-align:center;">
            <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:6px;">
                <img src="{{ asset('images/srb.jfif') }}" alt="SRB Logo" style="width:70px;height:70px;object-fit:contain;display:block;">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div style="font-size:10px;font-weight:bold;text-transform:uppercase;">SRB Return Invoice</div>
                    <div id="prtSrbInvoiceId" style="font-size:9.5px;font-weight:bold;margin:2px 0;word-break:break-all;"></div>
                    <div id="prtSrbQrWrapper">
                        <img id="prtSrbQrCode" src="" alt="QR" style="width:70px;height:70px;display:block;margin:3px auto;">
                    </div>
                </div>
            </div>
            <p style="margin:2px 0;font-size:9px;">Scan to verify return with SRB</p>
        </div>

        <p style="margin:4px 0;font-size:11px;">Refund Approved & Processed</p>
        <p style="margin:2px 0;font-size:10px;">Thank You for Visiting!</p>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    const currency = "{{ config('settings.currency_symbol', 'PKR') }}";
    let activeModalReturnData = null;

    // Initialize Select2
    $('#branch-select').select2({
        minimumResultsForSearch: 6,
        dropdownAutoWidth: true,
        width: '100%'
    });

    // Auto-submit form when branch changes
    $('#branch-select').on('change', function () {
        $(this).closest('form').submit();
    });

    // Function to populate and print thermal receipt
    function triggerPrintReceipt(data) {
        if (!data) return;

        $('#prtBranchName').text(data.branch_name + (data.counter_name ? ' - ' + data.counter_name : ''));
        $('#prtReturnNo').text(data.return_no);
        $('#prtOrderNo').text(data.order_invoice_no);
        $('#prtDate').text(data.created_at);
        $('#prtCustomer').text(data.customer_name);
        $('#prtCashier').text(data.cashier_name);
        $('#prtSubtotal').text(currency + ' ' + data.refund_subtotal);
        $('#prtTotal').text(currency + ' ' + data.refund_total);
        if (data.card_last_four) {
            let methodStr = 'CARD (••' + data.card_last_four + ')';
            if (data.receiving_account_name) {
                methodStr += '<br><small style="font-size:10px;">Bank: ' + data.receiving_account_name + '</small>';
            }
            $('#prtMethod').html(methodStr);
        } else {
            $('#prtMethod').text(data.refund_method.toUpperCase());
        }

        const prtBody = $('#prtItemsBody');
        prtBody.empty();
        if (data.items && data.items.length) {
            data.items.forEach(function (it) {
                prtBody.append(`
                    <tr>
                        <td style="padding:2px 0;">${it.item_name}</td>
                        <td style="text-align:center;">${it.quantity}</td>
                        <td style="text-align:right;">${currency} ${it.refund_amount}</td>
                    </tr>
                `);
            });
        }

        // SRB receipt section
        if (data.srb_invoice_id) {
            $('#prtSrbSection').show();
            $('#prtSrbInvoiceId').text(data.srb_invoice_id);
            if (data.srb_qr_code_link) {
                const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(data.srb_qr_code_link);
                $('#prtSrbQrCode').attr('src', qrUrl);
                $('#prtSrbQrWrapper').show();
            } else {
                $('#prtSrbQrWrapper').hide();
            }
        } else {
            $('#prtSrbSection').hide();
        }

        const printContents = document.getElementById('printAreaContent').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=400');
        printWindow.document.write('<html><head><title>Sale Return Receipt - ' + data.return_no + '</title></head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
            printWindow.close();
        }, 250);
    }

    // Direct Print Button Click
    $(document).on('click', '.btn-print-return-direct', function () {
        const retData = $(this).data('return');
        triggerPrintReceipt(retData);
    });

    // View Return Details Modal Click
    $(document).on('click', '.btn-view-return-details', function () {
        const data = $(this).data('return');
        activeModalReturnData = data;

        $('#modalReturnNo').text(data.return_no);
        $('#modalOrderNo').text(data.order_invoice_no);
        $('#modalDate').text(data.created_at);

        $('#modalCustomerName').text(data.customer_name);
        $('#modalCustomerPhone').text(data.customer_phone);

        $('#modalBranchName').text(data.branch_name);
        $('#modalCounterName').text(data.counter_name);

        $('#modalCashierName').text(data.cashier_name);
        if (data.card_last_four) {
            let cardInfo = 'CARD (••' + data.card_last_four + ')';
            if (data.card_holder_name) {
                cardInfo += ' • ' + data.card_holder_name;
            }
            if (data.receiving_account_name) {
                cardInfo += ' &bull; Bank: ' + data.receiving_account_name;
            }
            $('#modalRefundMethod').html('<i class="fas fa-credit-card mr-1 text-primary"></i> ' + cardInfo);
        } else {
            $('#modalRefundMethod').text(data.refund_method);
        }

        // SRB Section
        if (data.srb_invoice_id) {
            $('#modalSrbSection').show();
            $('#modalSrbInvoiceId').text(data.srb_invoice_id);
            if (data.srb_qr_code_link) {
                const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(data.srb_qr_code_link);
                $('#modalSrbQrCode').attr('src', qrUrl);
                $('#modalSrbQrWrapper').show();
            } else {
                $('#modalSrbQrWrapper').hide();
            }
        } else {
            $('#modalSrbSection').hide();
        }

        // Items table
        const tbody = $('#modalItemsTableBody');
        tbody.empty();
        if (data.items && data.items.length) {
            data.items.forEach(function (it) {
                const typeBadgeClass = it.item_type === 'Deal' ? 'badge-primary' : (it.item_type === 'Service' ? 'badge-info' : 'badge-secondary');
                tbody.append(`
                    <tr>
                        <td class="pl-3 py-2 font-weight-bold text-dark">${it.item_name}</td>
                        <td class="py-2 text-center">
                            <span class="badge ${typeBadgeClass} px-2 py-0.5" style="font-size:0.75rem;">${it.item_type}</span>
                        </td>
                        <td class="py-2 text-center font-weight-bold">${it.quantity}</td>
                        <td class="py-2 text-right" style="font-family:monospace;">${currency} ${it.unit_price}</td>
                        <td class="py-2 pr-3 text-right font-weight-bold" style="font-family:monospace;color:#c9342d;">${currency} ${it.refund_amount}</td>
                    </tr>
                `);
            });
        }

        // Reason & Notes
        $('#modalReturnReason').text(data.return_reason);
        if (data.notes && data.notes.trim().length > 0) {
            $('#modalNotes').text(data.notes);
            $('#modalNotesWrapper').show();
        } else {
            $('#modalNotesWrapper').hide();
        }

        // Totals
        $('#modalRefundSubtotal').text(data.refund_subtotal);
        if (parseFloat(data.refund_discount) > 0) {
            $('#modalRefundDiscount').text(data.refund_discount);
            $('#modalDiscountRow').show();
        } else {
            $('#modalDiscountRow').hide();
        }

        if (parseFloat(data.refund_tax) > 0) {
            $('#modalRefundTax').text(data.refund_tax);
            $('#modalTaxRow').show();
        } else {
            $('#modalTaxRow').hide();
        }

        $('#modalRefundTotal').text(data.refund_total);

        $('#returnDetailsModal').modal('show');
    });

    // Print button from inside Modal
    $('#modalBtnPrint').on('click', function () {
        if (activeModalReturnData) {
            triggerPrintReceipt(activeModalReturnData);
        }
    });
});
</script>
@endsection
