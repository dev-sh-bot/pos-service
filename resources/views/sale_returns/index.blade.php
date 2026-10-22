@extends('layouts.admin')

@section('title', 'Sale Return - Orders List')
@section('content-header', 'Sale Return')

@section('content-actions')
    <a href="{{ route('cart.index') }}" class="btn btn-primary" style="border-radius:10px;font-weight:700;">
        <i class="fas fa-cash-register mr-1"></i> POS Screen
    </a>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Custom Select2 Styling for Branch Filter */
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

/* Global modal & sidebar backdrop fix */
body.modal-open .main-sidebar,
body.swal2-shown .main-sidebar {
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

/* Modal styling */
.order-modal-header {
    background: linear-gradient(135deg, #6758bd 0%, #6758bd 100%) !important;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 1.25rem 1.5rem !important;
}
.order-meta-card {
    background: #ffffff;
    border: 1px solid var(--snd-border);
    border-radius: 12px;
    padding: 12px 14px;
    height: 100%;
    transition: box-shadow 0.15s ease;
}
.order-meta-card:hover {
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
}
.deal-services-box {
    background: #f0edff;
    border: 1px solid #f0edff;
    border-radius: 10px;
    padding: 8px 12px;
    margin-top: 6px;
}
.sale-return-filter-card {
    overflow: visible;
}
.sale-return-filter-card .card-header {
    display: flex;
    align-items: center;
    min-height: 54px;
    padding: 12px 18px !important;
    border-bottom: 1px solid var(--snd-border) !important;
    border-radius: 16px 16px 0 0 !important;
}
.sale-return-filter-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0;
    color: var(--snd-ink);
    font-size: .82rem;
    font-weight: 700;
}
.sale-return-filter-title i {
    color: var(--snd-primary-deep);
}
.sale-return-filter-subtitle {
    margin: 3px 0 0 23px;
    color: var(--snd-muted);
    font-size: .68rem;
}
.sale-return-filter-card .card-body {
    padding: 14px 18px 8px;
}
.sale-return-filter-card form.sale-return-filter-form {
    display: grid;
    grid-template-columns: minmax(220px, 1.25fr) minmax(240px, 1.25fr) minmax(150px, .9fr) minmax(150px, .9fr) auto;
    gap: 12px;
    align-items: center;
    margin: 0;
}
.sale-return-filter-field {
    min-width: 0;
}
.sale-return-filter-form .input-group {
    width: 100%;
}
.sale-return-filter-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}
.sale-return-filter-actions .btn {
    flex: 0 0 auto;
    white-space: nowrap;
}
@media (max-width: 1199.98px) {
    .sale-return-filter-card form.sale-return-filter-form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .sale-return-filter-actions {
        grid-column: 1 / -1;
    }
}
@media (max-width: 575.98px) {
    .sale-return-filter-card form.sale-return-filter-form {
        grid-template-columns: minmax(0, 1fr);
    }
    .sale-return-filter-actions {
        grid-column: auto;
    }
    .sale-return-filter-actions .btn-primary {
        flex: 1 1 auto;
    }
}
.sale-return-results-card {
    overflow: hidden;
}

/* Responsive Table & Action Icon Buttons */
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

@media (max-width: 991.98px) {
    .card-header form .d-flex {
        width: 100%;
    }
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

    {{-- Filters --}}
    <section class="card shadow-sm sale-return-filter-card mb-3" aria-labelledby="sale-return-filter-heading" style="border-radius:16px;border:1px solid var(--snd-border);">
        <div class="card-header bg-white">
            <div>
                <h2 class="sale-return-filter-title" id="sale-return-filter-heading"><i class="fas fa-filter"></i> Filter Orders</h2>
                <p class="sale-return-filter-subtitle">Narrow results by branch, invoice, customer, or date.</p>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sale-returns.index') }}" class="sale-return-filter-form">
                <div class="sale-return-filter-field">
                    <select name="branch_id" id="branch-select" class="form-control select2" style="width: 100%;">
                        @if($isAdmin)
                            <option value="all" {{ ($selectedBranchId === null || request('branch_id') === 'all') ? 'selected' : '' }}>
                                🏢 All Branches (Consolidated Sales)
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
                <div class="sale-return-filter-field">
                    <input type="text" name="search" class="form-control" placeholder="Search invoice #, customer or phone..." value="{{ request('search') }}" aria-label="Search orders by invoice, customer, or phone">
                </div>
                <div class="sale-return-filter-field">
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" title="Start Date" style="border-radius:10px;font-size:0.88rem;">
                </div>
                <div class="sale-return-filter-field">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" title="End Date" style="border-radius:10px;font-size:0.88rem;">
                </div>
                <div class="sale-return-filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius:8px;font-weight:700;height:38px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    @if(request('search') || request('start_date') || request('end_date') || (request('branch_id') && request('branch_id') !== 'all'))
                        <a href="{{ route('sale-returns.index') }}" class="btn btn-secondary btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="border-radius:8px;height:38px;" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    {{-- Orders Card --}}
    <div class="card shadow-sm sale-return-results-card" style="border-radius:16px;border:1px solid var(--snd-border);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width:860px;">
                    <thead style="background:var(--snd-surface-soft);color:#505469;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.04em;">
                        <tr>
                            <th class="pl-4 py-3">Order / Invoice #</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Branch & Counter</th>
                            <th class="py-3">Services / Deals</th>
                            <th class="py-3 text-right">Total Amount</th>
                            <th class="py-3 text-right">Paid</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3">Date</th>
                            <th class="pr-3 py-3 text-center col-actions" style="width: 100px; min-width: 95px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $orderTotal = $order->total();
                                $orderReceived = $order->receivedAmount();
                                $orderRemaining = $orderTotal - $orderReceived;
                                $typeInfo = $order->getOrderTypeInfo();

                                $servicesCount = 0;
                                $dealsCount = 0;
                                $productsCount = 0;
                                foreach ($order->items as $orderItem) {
                                    $qty = max(1, (int) $orderItem->quantity);
                                    if ((int) $orderItem->item_type === 2) {
                                        $dealsCount += $qty;
                                    } elseif ((int) $orderItem->item_type === 1) {
                                        $servicesCount += $qty;
                                    } else {
                                        $productsCount += $qty;
                                    }
                                }

                                // Prepare rich item data for modal
                                $itemsData = $order->items->map(function ($item) {
                                    $itemType = (int) $item->item_type;
                                    $deal = $itemType === 2 ? $item->deal : null;
                                    $service = $itemType === 1 ? $item->service : null;
                                    $product = $itemType === 0 ? $item->product : null;

                                    $isDeal = $itemType === 2;
                                    $isService = $itemType === 1;

                                    $type = $isDeal ? 'deal' : ($isService ? 'service' : 'product');
                                    $typeLabel = $isDeal ? 'Deal Package' : ($isService ? 'Service' : 'Product');
                                    $name = $item->item_name ?: ($deal?->name ?? $service?->name ?? $product?->name ?? 'Item');

                                    $dealServices = [];
                                    if ($isDeal && $deal) {
                                        $dealServices = $deal->services->map(function ($s) {
                                            return [
                                                'name' => $s->name,
                                                'rate' => number_format((float) $s->rate, 2),
                                            ];
                                        })->values()->toArray();
                                    }

                                    $categoryName = $service?->category?->name;
                                    $qty = max(1, (int) $item->quantity);
                                    $unitPrice = (float) ($item->price / $qty);
                                    $itemReturnedQty = $item->getReturnedQuantity();
                                    $itemIsFullyReturned = ($itemReturnedQty >= $qty);

                                    return [
                                        'id' => $item->id,
                                        'name' => $name,
                                        'type' => $type,
                                        'type_label' => $typeLabel,
                                        'category' => $categoryName,
                                        'quantity' => $qty,
                                        'returned_qty' => $itemReturnedQty,
                                        'is_fully_returned' => $itemIsFullyReturned,
                                        'unit_price' => number_format($unitPrice, 2),
                                        'total' => number_format((float) $item->price, 2),
                                        'is_deal' => $isDeal,
                                        'deal_services' => $dealServices,
                                    ];
                                });

                                $isFullyReturned = $order->isFullyReturned();
                                $isPartiallyReturned = $order->isPartiallyReturned();
                            @endphp
                            <tr @if($isFullyReturned) class="order-row-fully-returned" style="background:#fff0ef !important;border-left:4px solid #c9342d;" @elseif($isPartiallyReturned) class="order-row-partially-returned" style="background:#fff5e6 !important;border-left:4px solid #b45309;" @endif>
                                <td class="pl-4 py-3 font-weight-bold" style="color:#1a1a2e;">
                                    <span class="badge" style="background:#f0edff;color:#6758bd;border:1px solid #d9d4f5;font-size:0.82rem;padding:6px 10px;border-radius:8px;">
                                        {{ $order->getInvoiceNumber() }}
                                    </span>
                                    @if($isFullyReturned)
                                        <div style="margin-top:3px;">
                                            <span class="badge" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;font-size:0.7rem;padding:2px 7px;border-radius:6px;font-weight:800;letter-spacing:0.02em;" title="100% of order items have been returned">
                                                <i class="fas fa-undo-alt mr-1"></i>100% RETURNED
                                            </span>
                                        </div>
                                    @elseif($isPartiallyReturned)
                                        <div style="margin-top:3px;">
                                            <span class="badge" style="background:#fff5e6;color:#b45309;border:1px solid #f2d8a6;font-size:0.7rem;padding:2px 7px;border-radius:6px;font-weight:700;">
                                                <i class="fas fa-undo mr-1"></i>PARTIAL RETURN
                                            </span>
                                        </div>
                                    @endif
                                    @if($order->srb_invoice_id)
                                        <div style="margin-top:3px;">
                                            <span class="badge" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;font-size:0.7rem;padding:2px 6px;border-radius:6px;font-weight:700;" title="SRB Fiscal Invoice: {{ $order->srb_invoice_id }}">
                                                <i class="fas fa-check-circle mr-1"></i>SRB: {{ $order->srb_invoice_id }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="badge px-2 py-1" style="background:{{ $typeInfo['bg'] }};color:{{ $typeInfo['color'] }};border:1px solid {{ $typeInfo['border'] }};border-radius:6px;font-weight:700;font-size:0.75rem;">
                                        <i class="{{ $typeInfo['icon'] }} mr-1"></i>{{ $typeInfo['label'] }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div style="font-weight:700;color:#1a1a2e;">
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        {{ $order->getCustomerName() }}
                                    </div>
                                    @if($order->customer?->phone)
                                        <small class="text-muted" style="display:block;margin-top:2px;">
                                            <i class="fas fa-phone-alt mr-1" style="font-size:0.7rem;"></i>{{ $order->customer->phone }}
                                        </small>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($order->branch)
                                        <span class="badge badge-light border" style="font-weight:600;padding:4px 8px;border-radius:6px;">
                                            {{ $order->branch->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($order->counter)
                                        <small class="text-muted d-block mt-1">{{ $order->counter->name }}</small>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($dealsCount > 0 && $servicesCount > 0)
                                        <div class="d-flex flex-column" style="gap: 3px;">
                                            <span class="badge badge-pill px-2 py-1" style="background:#f0edff;color:#6758bd;border:1px solid #f0edff;font-weight:700;font-size:0.75rem;width:fit-content;">
                                                <i class="fas fa-tags mr-1"></i>{{ $dealsCount }} {{ $dealsCount === 1 ? 'Deal' : 'Deals' }}
                                            </span>
                                            <span class="badge badge-pill px-2 py-1" style="background:#ccfbf1;color:#16749c;border:1px solid #99f6e4;font-weight:700;font-size:0.75rem;width:fit-content;">
                                                <i class="fas fa-spa mr-1"></i>{{ $servicesCount }} {{ $servicesCount === 1 ? 'Service' : 'Services' }}
                                            </span>
                                        </div>
                                    @elseif($dealsCount > 0)
                                        <span class="badge badge-pill px-2 py-1" style="background:#f0edff;color:#6758bd;border:1px solid #f0edff;font-weight:700;font-size:0.78rem;">
                                            <i class="fas fa-tags mr-1"></i>{{ $dealsCount }} {{ $dealsCount === 1 ? 'Deal' : 'Deals' }}
                                        </span>
                                    @elseif($servicesCount > 0)
                                        <span class="badge badge-pill px-2 py-1" style="background:#ccfbf1;color:#16749c;border:1px solid #99f6e4;font-weight:700;font-size:0.78rem;">
                                            <i class="fas fa-spa mr-1"></i>{{ $servicesCount }} {{ $servicesCount === 1 ? 'Service' : 'Services' }}
                                        </span>
                                    @elseif($productsCount > 0)
                                        <span class="badge badge-pill px-2 py-1" style="background:#f0edff;color:#6758bd;border:1px solid #d9d4f5;font-weight:700;font-size:0.78rem;">
                                            <i class="fas fa-box-open mr-1"></i>{{ $productsCount }} {{ $productsCount === 1 ? 'Product' : 'Products' }}
                                        </span>
                                    @else
                                        <span class="badge badge-pill badge-light border px-2 py-1 text-muted" style="font-weight:600;font-size:0.78rem;">
                                            0 Services
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right font-weight-bold" style="color:#1a1a2e;font-size:0.92rem;">
                                    {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($orderTotal, 2) }}
                                </td>
                                <td class="py-3 text-right" style="color:#1f7a3d;font-weight:600;">
                                    {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($orderReceived, 2) }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($isFullyReturned)
                                        <span class="badge" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;padding:5px 10px;border-radius:20px;font-weight:800;font-size:0.75rem;" title="100% of order items have been returned">
                                            <i class="fas fa-undo-alt mr-1"></i> 100% Returned
                                        </span>
                                    @elseif($isPartiallyReturned)
                                        <span class="badge" style="background:#fff5e6;color:#b45309;border:1px solid #f2d8a6;padding:5px 10px;border-radius:20px;font-weight:700;font-size:0.75rem;">
                                            <i class="fas fa-undo mr-1"></i> Partial Return
                                        </span>
                                    @elseif($orderReceived >= $orderTotal && $orderTotal > 0)
                                        <span class="badge" style="background:#eaf9ee;color:#1f7a3d;padding:5px 10px;border-radius:20px;font-weight:700;font-size:0.75rem;">
                                            <i class="fas fa-check-circle mr-1"></i> Paid
                                        </span>
                                    @elseif($orderReceived > 0)
                                        <span class="badge" style="background:#fef9c3;color:#a16207;padding:5px 10px;border-radius:20px;font-weight:700;font-size:0.75rem;">
                                            <i class="fas fa-clock mr-1"></i> Partial
                                        </span>
                                    @else
                                        <span class="badge" style="background:#fff0ef;color:#c9342d;padding:5px 10px;border-radius:20px;font-weight:700;font-size:0.75rem;">
                                            <i class="fas fa-times-circle mr-1"></i> Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted" style="font-size:0.8rem;">
                                    <div>{{ $order->created_at->format('d M, Y') }}</div>
                                    <small>{{ $order->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="pr-3 py-3 text-center col-actions" style="white-space:nowrap; width: 100px; min-width: 95px;">
                                    <div class="snd-table-actions">
                                        {{-- View Order Details Modal Button --}}
                                        <button type="button"
                                                class="btn snd-table-action snd-table-action--secondary btn-view-order"
                                                data-toggle="modal"
                                                data-target="#modalOrderDetails"
                                                data-order-id="{{ $order->id }}"
                                                data-invoice="{{ $order->getInvoiceNumber() }}"
                                                data-is-fully-returned="{{ $isFullyReturned ? '1' : '0' }}"
                                                data-is-partially-returned="{{ $isPartiallyReturned ? '1' : '0' }}"
                                                data-type-label="{{ $typeInfo['label'] }}"
                                                data-type-icon="{{ $typeInfo['icon'] }}"
                                                data-type-bg="{{ $typeInfo['bg'] }}"
                                                data-type-color="{{ $typeInfo['color'] }}"
                                                data-type-border="{{ $typeInfo['border'] }}"
                                                data-customer="{{ $order->getCustomerName() }}"
                                                data-phone="{{ $order->customer?->phone ?? '' }}"
                                                data-address="{{ $order->customer?->address ?? '' }}"
                                                data-branch="{{ $order->branch?->name ?? 'Main Branch' }}"
                                                data-counter="{{ $order->counter?->name ?? '' }}"
                                                data-cashier="{{ $order->user?->first_name ? $order->user->first_name . ' ' . $order->user->last_name : 'Cashier' }}"
                                                data-subtotal="{{ number_format($order->subtotal ?? $orderTotal, 2) }}"
                                                data-tax-percent="{{ number_format($order->tax_percent ?? 0, 1) }}"
                                                data-tax-amount="{{ number_format($order->tax_amount ?? 0, 2) }}"
                                                data-discount-amount="{{ number_format($order->discount_amount ?? 0, 2) }}"
                                                data-discount-percent="{{ number_format($order->discount_percent ?? 0, 1) }}"
                                                data-total="{{ number_format($orderTotal, 2) }}"
                                                data-received="{{ number_format($orderReceived, 2) }}"
                                                data-remaining="{{ number_format($orderRemaining, 2) }}"
                                                data-status-text="{{ $isFullyReturned ? '100% Returned' : ($isPartiallyReturned ? 'Partial Return' : ($orderReceived >= $orderTotal && $orderTotal > 0 ? 'Paid' : ($orderReceived > 0 ? 'Partial' : 'Unpaid'))) }}"
                                                data-status-bg="{{ $isFullyReturned ? '#fff0ef' : ($isPartiallyReturned ? '#fff5e6' : ($orderReceived >= $orderTotal && $orderTotal > 0 ? '#eaf9ee' : ($orderReceived > 0 ? '#fef9c3' : '#fff0ef'))) }}"
                                                data-status-color="{{ $isFullyReturned ? '#c9342d' : ($isPartiallyReturned ? '#b45309' : ($orderReceived >= $orderTotal && $orderTotal > 0 ? '#1f7a3d' : ($orderReceived > 0 ? '#a16207' : '#c9342d'))) }}"
                                                data-date="{{ $order->created_at->format('d M Y, h:i A') }}"
                                                data-items="{{ base64_encode($itemsData->toJson()) }}"
                                                data-srb-id="{{ $order->srb_invoice_id ?? '' }}"
                                                title="View Order Details"
                                                data-tooltip="View Order Details">
                                            <x-snd-icon name="eye" />
                                        </button>

                                        {{-- Sale Return POS Screen Button (HIDDEN when 100% returned) --}}
                                        @if(!$isFullyReturned)
                                            <a href="{{ route('sale-returns.pos', $order->id) }}"
                                               class="btn snd-table-action btn-sale-return-icon"
                                               title="Process Sale Return"
                                               data-tooltip="Process Sale Return">
                                                <x-snd-icon name="rotate-ccw" />
                                            </a>
                                        @else
                                            <span class="btn snd-table-action snd-table-action--danger disabled"
                                                  title="Order is 100% Returned - Action not available"
                                                  data-tooltip="100% Returned">
                                                <x-snd-icon name="circle-x" />
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div style="font-size:2.5rem;color:var(--snd-border-strong);margin-bottom:0.75rem;"><i class="fas fa-box-open"></i></div>
                                    <div style="font-size:1.05rem;font-weight:700;color:#505469;">No orders found</div>
                                    <p class="text-muted mb-0">No sales orders match your criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 snd-pagination-inline" style="border-top:1px solid var(--snd-surface-soft);border-radius:0 0 16px 16px;">
                <span class="text-muted" style="font-size:0.85rem;">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                </span>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

{{-- Render modal in root @section('model') to prevent stacking/clipping inside content-wrapper --}}
@section('model')
<div class="modal fade" id="modalOrderDetails" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 820px;">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25); overflow: hidden;">
            {{-- Luxury Header --}}
            <div class="modal-header order-modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-right:12px;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.01em;">
                                Order Invoice <span id="modInvoice" class="ml-1" style="color: #b9aefe;"></span>
                            </h5>
                            <span id="modOrderTypeBadge" class="badge ml-2 px-2 py-1" style="font-size:0.75rem;border-radius:6px;font-weight:700;"></span>
                            <span id="modStatusBadge" class="badge ml-2 px-2 py-1" style="font-size:0.75rem;border-radius:20px;font-weight:700;"></span>
                            <span id="modSrbBadge" class="badge ml-2 px-2 py-1" style="font-size:0.75rem;border-radius:20px;font-weight:700;display:none;background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;"><i class="fas fa-check-circle mr-1"></i>SRB: <span id="modSrbId"></span></span>
                        </div>
                        <small class="text-white-50" id="modDateFormatted">Created details</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; font-size: 1.5rem; text-shadow: none; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body p-4" style="background: var(--snd-surface-soft);">
                {{-- 4 Info Cards --}}
                <div class="row mb-3" style="gap: 0; margin-left:-6px; margin-right:-6px;">
                    <div class="col-md-3 col-sm-6 col-12 px-2 mb-2 mb-md-0">
                        <div class="order-meta-card">
                            <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                <i class="fas fa-user text-primary mr-1"></i> Customer
                            </div>
                            <div id="modCustomer" class="font-weight-bold text-dark text-truncate" style="font-size: 0.92rem;"></div>
                            <div id="modPhone" class="text-muted small mt-1 text-truncate"></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 px-2 mb-2 mb-md-0">
                        <div class="order-meta-card">
                            <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                <i class="fas fa-store-alt text-primary mr-1"></i> Branch & Counter
                            </div>
                            <div id="modBranch" class="font-weight-bold text-dark text-truncate" style="font-size: 0.92rem;"></div>
                            <div id="modCounter" class="text-muted small mt-1 text-truncate"></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 px-2 mb-2 mb-md-0">
                        <div class="order-meta-card">
                            <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                <i class="fas fa-user-tie text-primary mr-1"></i> Billed By
                            </div>
                            <div id="modCashier" class="font-weight-bold text-dark text-truncate" style="font-size: 0.92rem;"></div>
                            <small class="text-muted d-block mt-1">POS Cashier</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12 px-2">
                        <div class="order-meta-card">
                            <div class="text-uppercase text-muted font-weight-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                <i class="far fa-calendar-alt text-primary mr-1"></i> Order Date
                            </div>
                            <div id="modDate" class="font-weight-bold text-dark" style="font-size: 0.88rem;"></div>
                            <small class="text-success font-weight-bold d-block mt-1"><i class="fas fa-check-circle mr-1"></i>Recorded</small>
                        </div>
                    </div>
                </div>

                {{-- Purchased Items Container --}}
                <div class="bg-white rounded-lg shadow-sm mb-3" style="border: 1px solid var(--snd-border); border-radius: 14px; overflow: hidden;">
                    <div class="px-3 py-2 bg-light border-bottom d-flex align-items-center justify-content-between">
                        <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                            <i class="fas fa-spa mr-1 text-primary"></i> Order Services & Deals
                        </span>
                        <span id="modItemsCountBadge" class="badge badge-light border text-muted" style="font-weight: 600;"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="bg-light text-muted" style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.04em;">
                                <tr>
                                    <th class="pl-3 py-2" style="width: 45%;">Service / Deal Name</th>
                                    <th class="py-2 text-center" style="width: 15%;">Type</th>
                                    <th class="py-2 text-center" style="width: 12%;">Qty</th>
                                    <th class="py-2 text-right" style="width: 13%;">Rate</th>
                                    <th class="pr-3 py-2 text-right" style="width: 15%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="modItemsBody">
                                {{-- Dynamically populated via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Totals Summary Section --}}
                <div class="row justify-content-end">
                    <div class="col-md-6 col-sm-8 col-12">
                        <div class="bg-white p-3 rounded-lg shadow-sm" style="border: 1px solid var(--snd-border); border-radius: 14px; font-size: 0.88rem;">
                            <div class="d-flex justify-content-between mb-2 text-muted">
                                <span>Sub Total:</span>
                                <span class="font-weight-bold text-dark">{{ config('settings.currency_symbol', 'PKR') }} <span id="modSubtotal">0.00</span></span>
                            </div>
                            <div id="modTaxRow" class="d-flex justify-content-between mb-2 text-muted">
                                <span>Tax (<span id="modTaxPercent">0</span>%):</span>
                                <span class="font-weight-bold text-dark">+ {{ config('settings.currency_symbol', 'PKR') }} <span id="modTax">0.00</span></span>
                            </div>
                            <div id="modDiscountRow" class="d-flex justify-content-between mb-2 text-muted">
                                <span>Discount:</span>
                                <span class="font-weight-bold text-danger">- {{ config('settings.currency_symbol', 'PKR') }} <span id="modDiscount">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top mb-2" style="font-size: 1.15rem; font-weight: 800; color: #6758bd;">
                                <span>Total Amount:</span>
                                <span>{{ config('settings.currency_symbol', 'PKR') }} <span id="modTotal">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top text-success" style="font-weight: 700; font-size: 0.88rem;">
                                <span><i class="fas fa-money-bill-wave mr-1"></i> Paid / Received:</span>
                                <span>{{ config('settings.currency_symbol', 'PKR') }} <span id="modReceived">0.00</span></span>
                            </div>
                            <div id="modRemainingRow" class="d-flex justify-content-between pt-1 text-danger small font-weight-bold">
                                <span>Remaining Balance:</span>
                                <span>{{ config('settings.currency_symbol', 'PKR') }} <span id="modRemaining">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center" style="border-top: 1px solid var(--snd-surface-soft);">
                <span class="text-muted small">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> Sale Return Review Mode
                </span>
                <div>
                    <button type="button" class="btn btn-light btn-sm mr-2 px-3" data-dismiss="modal" style="border-radius: 8px; font-weight: 600; border: 1px solid var(--snd-border-strong);">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-3 mr-2" onclick="window.print()" style="border-radius: 8px; font-weight: 700;">
                        <i class="fas fa-print mr-1"></i> Print Invoice
                    </button>
                    <a href="#" id="modBtnSaleReturn" class="btn btn-primary btn-sm px-3" style="border-radius:8px;font-weight:700;">
                        <i class="fas fa-undo-alt mr-1"></i> Process Sale Return
                    </a>
                    <span id="modFullyReturnedBadge" class="btn btn-sm btn-light disabled px-3 text-danger font-weight-bold" style="border: 1px solid #f2b8b5; background: #fff0ef; display: none;">
                        <i class="fas fa-ban mr-1"></i> Order 100% Returned
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Select2 on branch filter dropdown
    if (window.jQuery && $.fn.select2) {
        $('#branch-select').select2({
            placeholder: "Search & select branch...",
            allowClear: false,
            width: '100%'
        }).on('select2:select', function () {
            $(this).closest('form').submit();
        });
    }

    $(document).on('click', '.btn-view-order', function () {
        var btn = $(this);

        // Update Sale Return button link dynamically or hide if 100% returned
        var isFullyReturned = btn.data('is-fully-returned') == '1';
        var returnBaseUrl = "{{ route('sale-returns.pos', ['order' => '__ORDER_ID__']) }}";
        if (isFullyReturned) {
            $('#modBtnSaleReturn').hide();
            $('#modFullyReturnedBadge').show();
        } else {
            $('#modBtnSaleReturn').attr('href', returnBaseUrl.replace('__ORDER_ID__', btn.data('order-id'))).show();
            $('#modFullyReturnedBadge').hide();
        }

        // Header info
        $('#modInvoice').text(btn.data('invoice'));
        $('#modDateFormatted').text('Placed on ' + btn.data('date'));

        // Order Type badge
        var typeBadge = $('#modOrderTypeBadge');
        typeBadge.html('<i class="' + btn.data('type-icon') + ' mr-1"></i>' + btn.data('type-label'));
        typeBadge.css({
            'background': btn.data('type-bg'),
            'color': btn.data('type-color'),
            'border': '1px solid ' + btn.data('type-border')
        });

        // Status badge
        var statusBadge = $('#modStatusBadge');
        statusBadge.text(btn.data('status-text'));
        statusBadge.css({
            'background': btn.data('status-bg'),
            'color': btn.data('status-color')
        });

        // SRB badge
        var srbId = btn.data('srb-id');
        if (srbId) {
            $('#modSrbId').text(srbId);
            $('#modSrbBadge').show();
        } else {
            $('#modSrbBadge').hide();
        }

        // Meta cards
        $('#modCustomer').text(btn.data('customer'));
        var phone = btn.data('phone');
        $('#modPhone').html(phone ? '<i class="fas fa-phone mr-1"></i>' + phone : '<span class="text-muted font-italic">No phone recorded</span>');

        $('#modBranch').text(btn.data('branch'));
        var counter = btn.data('counter');
        $('#modCounter').text(counter ? 'Counter: ' + counter : 'Counter: Standard');

        $('#modCashier').text(btn.data('cashier'));
        $('#modDate').text(btn.data('date'));

        // Totals
        $('#modSubtotal').text(btn.data('subtotal'));
        $('#modTaxPercent').text(btn.data('tax-percent'));
        $('#modTax').text(btn.data('tax-amount'));
        $('#modDiscount').text(btn.data('discount-amount'));
        $('#modTotal').text(btn.data('total'));
        $('#modReceived').text(btn.data('received'));
        $('#modRemaining').text(btn.data('remaining'));

        // Parse items
        var items = [];
        try {
            items = JSON.parse(atob(btn.data('items')));
        } catch (e) {
            console.error('Error parsing items', e);
            items = [];
        }

        var dealQty = 0;
        var serviceQty = 0;
        var productQty = 0;
        if (items && items.length > 0) {
            items.forEach(function (it) {
                var q = parseInt(it.quantity, 10) || 1;
                if (it.is_deal) dealQty += q;
                else if (it.type === 'service') serviceQty += q;
                else productQty += q;
            });
        }
        var summaryArr = [];
        if (dealQty > 0) summaryArr.push(dealQty + (dealQty === 1 ? ' Deal' : ' Deals'));
        if (serviceQty > 0) summaryArr.push(serviceQty + (serviceQty === 1 ? ' Service' : ' Services'));
        if (productQty > 0) summaryArr.push(productQty + (productQty === 1 ? ' Product' : ' Products'));
        $('#modItemsCountBadge').text(summaryArr.length > 0 ? summaryArr.join(', ') : '0 Services');

        var tbody = $('#modItemsBody');
        tbody.empty();

        if (items && items.length > 0) {
            items.forEach(function (item, index) {
                var badgeHtml = '';
                if (item.is_deal) {
                    badgeHtml = '<span class="badge px-2 py-1" style="background:#ede9fe;color:#6758bd;border:1px solid #ddd6fe;border-radius:6px;font-size:0.72rem;font-weight:700;"><i class="fas fa-tags mr-1"></i>Deal</span>';
                } else if (item.type === 'service') {
                    badgeHtml = '<span class="badge px-2 py-1" style="background:#ccfbf1;color:#16749c;border:1px solid #99f6e4;border-radius:6px;font-size:0.72rem;font-weight:700;"><i class="fas fa-spa mr-1"></i>Service</span>';
                } else {
                    badgeHtml = '<span class="badge px-2 py-1" style="background:#f0edff;color:#6758bd;border:1px solid #d9d4f5;border-radius:6px;font-size:0.72rem;font-weight:700;"><i class="fas fa-box-open mr-1"></i>Product</span>';
                }

                if (item.is_fully_returned) {
                    badgeHtml += ' <span class="badge px-2 py-1" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;border-radius:6px;font-size:0.72rem;font-weight:700;"><i class="fas fa-undo mr-1"></i>100% Returned</span>';
                } else if (item.returned_qty && item.returned_qty > 0) {
                    badgeHtml += ' <span class="badge px-2 py-1" style="background:#fff5e6;color:#b45309;border:1px solid #f2d8a6;border-radius:6px;font-size:0.72rem;font-weight:700;"><i class="fas fa-undo mr-1"></i>' + item.returned_qty + ' Returned</span>';
                }

                var itemTitleHtml = '<div><span class="font-weight-bold text-dark" style="font-size:0.92rem;">' + item.name + '</span>';
                if (item.category) {
                    itemTitleHtml += '<small class="text-muted d-block"><i class="fas fa-folder-open mr-1" style="font-size:0.7rem;"></i>' + item.category + '</small>';
                }

                // If deal, render detailed included services
                if (item.is_deal && item.deal_services && item.deal_services.length > 0) {
                    itemTitleHtml += '<div class="deal-services-box mt-2">' +
                        '<div class="d-flex align-items-center justify-content-between mb-1">' +
                        '<small class="font-weight-bold" style="color:#6758bd;text-transform:uppercase;font-size:0.68rem;letter-spacing:0.04em;">' +
                        '<i class="fas fa-check-circle mr-1"></i> Included Deal Services (' + item.deal_services.length + '):' +
                        '</small>' +
                        '</div>' +
                        '<div class="d-flex flex-wrap" style="gap:4px;">';

                    item.deal_services.forEach(function (ds) {
                        itemTitleHtml += '<span class="badge bg-white text-dark border px-2 py-1" style="font-size:0.73rem;font-weight:600;border-color:#f0edff !important;">' +
                            '✓ ' + ds.name + (ds.rate > 0 ? ' <span class="text-muted ml-1">(' + ds.rate + ')</span>' : '') +
                            '</span>';
                    });

                    itemTitleHtml += '</div></div>';
                }

                itemTitleHtml += '</div>';

                var rowStyle = item.is_fully_returned ? 'border-bottom: 1px solid #f2b8b5; background: #fff0ef;' : 'border-bottom: 1px solid var(--snd-surface-soft);';
                var row = '<tr style="' + rowStyle + '">' +
                    '<td class="pl-3 py-3 align-top">' + itemTitleHtml + '</td>' +
                    '<td class="py-3 text-center align-top">' + badgeHtml + '</td>' +
                    '<td class="py-3 text-center align-top font-weight-bold text-dark">' + item.quantity + '</td>' +
                    '<td class="py-3 text-right align-top text-muted font-weight-bold">' + item.unit_price + '</td>' +
                    '<td class="pr-3 py-3 text-right align-top font-weight-bold" style="color:#1a1a2e;font-size:0.92rem;">' + item.total + '</td>' +
                    '</tr>';

                tbody.append(row);
            });
        } else {
            tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">No services or deals recorded for this order.</td></tr>');
        }
    });
});
</script>
@endsection
