@extends('layouts.admin')

@section('title', 'POS - Sale Return for ' . $order->getInvoiceNumber())
@section('body-class', 'sidebar-collapse')

@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 Bank Account Styling */
.select2-container--default .select2-selection--single {
    height: 40px !important;
    border: 1.5px solid var(--snd-border-strong) !important;
    border-radius: 9px !important;
    background: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 10px !important;
    transition: all 0.2s ease !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #6758bd !important;
    box-shadow: 0 0 0 3px rgba(103, 88, 189, 0.15) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1a1a2e !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    padding-left: 2px !important;
    line-height: normal !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #626b7b !important;
    font-weight: 500 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
    right: 8px !important;
}
.select2-dropdown {
    border: 1.5px solid #6758bd !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12) !important;
    overflow: hidden !important;
    z-index: 99999 !important;
}
.select2-results__option {
    font-size: 0.84rem !important;
    padding: 8px 12px !important;
    color: #1a1a2e !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--snd-button-primary) !important;
    color: var(--snd-button-primary-text) !important;
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f0edff !important;
    color: #6758bd !important;
    font-weight: 700 !important;
}

.content-wrapper {
    padding-bottom: 0 !important;
    background: var(--snd-surface-soft) !important;
    min-height: calc(100vh - 57px) !important;
    height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}
.content-header,
.main-footer {
    display: none !important;
}
.content {
    padding: 0 !important;
    margin: 0 !important;
    height: calc(100vh - 57px) !important;
    max-height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}

/* POS Return Shell */
.pos-return-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 57px);
    background: var(--snd-surface-soft);
    overflow: hidden;
}

/* Top bar */
.pos-return-topbar {
    height: 58px;
    background: #ffffff;
    border-bottom: 1.5px solid var(--snd-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}

/* Main Split View */
.pos-return-body {
    display: flex;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

/* Left Section: Purchased Items */
.pos-items-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: var(--snd-surface-soft);
    border-right: 1.5px solid var(--snd-border);
    overflow: hidden;
}

.pos-items-header {
    padding: 12px 18px;
    background: #ffffff;
    border-bottom: 1px solid var(--snd-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-shrink: 0;
}

.pos-items-list {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Item Card */
.return-item-card {
    background: #ffffff;
    border: 1.5px solid var(--snd-border);
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.18s ease;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}

.return-item-card.is-selected {
    border-color: #6758bd;
    background: #f0edff;
    box-shadow: 0 4px 14px rgba(103, 88, 189, 0.12);
}

.return-item-card.is-deselected {
    opacity: 0.55;
    background: var(--snd-surface-soft);
    border-color: var(--snd-border);
}

.item-check-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.item-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #6758bd;
}

.qty-stepper {
    display: inline-flex;
    align-items: center;
    background: var(--snd-surface-soft);
    border: 1px solid var(--snd-border-strong);
    border-radius: 10px;
    overflow: hidden;
}

.qty-btn {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #ffffff;
    color: #505469;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background 0.15s;
}

.qty-btn:hover:not(:disabled) {
    background: var(--snd-surface-soft);
    color: #1a1a2e;
}

.qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.qty-input {
    width: 44px;
    height: 30px;
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 800;
    font-size: 0.9rem;
    color: #1a1a2e;
    outline: none;
}

/* Right Section: Order & Refund Summary Panel */
.pos-summary-panel {
    width: 440px;
    min-width: 420px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    padding: 16px 20px;
    border-left: 1px solid var(--snd-border);
    box-shadow: -4px 0 16px rgba(15, 23, 42, 0.03);
}

.deal-subservice-pill {
    background: #f0edff;
    border: 1px solid #f0edff;
    color: #6758bd;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 7px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Responsive Tablet and Mobile Styles */
@media (max-width: 991.98px) {
    .content-wrapper,
    .content,
    .pos-return-container {
        height: auto !important;
        max-height: none !important;
        min-height: 100vh !important;
        overflow: visible !important;
    }
    .pos-return-topbar {
        height: auto !important;
        min-height: 56px !important;
        padding: 10px 16px !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }
    .pos-return-body {
        flex-direction: column !important;
        height: auto !important;
        overflow: visible !important;
    }
    .pos-items-panel {
        width: 100% !important;
        min-width: 100% !important;
        border-right: none !important;
        border-bottom: 2px solid var(--snd-border) !important;
        overflow: visible !important;
    }
    .pos-items-list {
        max-height: none !important;
        overflow-y: visible !important;
        padding: 14px !important;
    }
    .pos-summary-panel {
        width: 100% !important;
        min-width: 100% !important;
        border-left: none !important;
        box-shadow: none !important;
        overflow: visible !important;
        height: auto !important;
        padding: 18px 14px !important;
    }
}

@media (max-width: 575.98px) {
    .pos-return-topbar {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 8px !important;
        padding: 10px 12px !important;
    }
    .pos-items-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 8px !important;
        padding: 10px 14px !important;
    }
    .return-item-card {
        padding: 12px !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
    }
    .return-item-card .item-check-wrap {
        align-self: flex-start;
        padding-top: 4px;
    }
    .qty-stepper {
        margin-top: 6px !important;
    }
}

/* Print Receipt Styles */
@media print {
    body * {
        visibility: hidden !important;
    }
    #return-print-area,
    #return-print-area * {
        visibility: visible !important;
    }
    #return-print-area {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 80mm !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}

/* Refund Mode Selector Cards */
.refund-mode-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 2px solid var(--snd-border);
    border-radius: 12px;
    background: var(--snd-surface-soft);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    user-select: none;
    outline: none;
}
.refund-mode-card:hover {
    border-color: #b9aefe !important;
    background: #f0edff !important;
    transform: translateY(-1px);
}
.refund-mode-card:focus-visible {
    box-shadow: 0 0 0 3px rgba(103, 88, 189, 0.35) !important;
}
.refund-mode-card.active {
    border-color: #6758bd !important;
    background: #f0edff !important;
    box-shadow: 0 3px 10px rgba(103, 88, 189, 0.15) !important;
}
.refund-mode-card .refund-pay-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--snd-surface-soft);
    color: #626b7b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: all 0.2s;
}
.refund-mode-card.active .refund-pay-icon {
    background: var(--snd-button-primary) !important;
    color: var(--snd-button-primary-text) !important;
    box-shadow: 0 2px 6px var(--snd-primary-ring) !important;
}
.refund-mode-card .refund-pay-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: transparent;
    border: 2px solid var(--snd-border-strong);
    color: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    flex-shrink: 0;
    transition: all 0.2s;
}
.refund-mode-card .refund-pay-indicator i {
    opacity: 0;
    transition: opacity 0.2s;
}
.refund-mode-card.active .refund-pay-indicator {
    background: var(--snd-button-primary) !important;
    border-color: var(--snd-button-primary) !important;
    color: var(--snd-button-primary-text) !important;
}
.refund-mode-card.active .refund-pay-indicator i {
    opacity: 1 !important;
    color: #ffffff !important;
}
</style>
@endsection

@section('content')
<div class="pos-return-container">
    {{-- Top Bar --}}
    <header class="pos-return-topbar">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('sale-returns.index') }}" class="btn btn-light btn-sm mr-3" style="border:1.5px solid var(--snd-border-strong);border-radius:10px;font-weight:700;color:#505469;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Orders
            </a>
            <div class="d-flex align-items-center">
                <span class="badge mr-3 px-3 py-2" style="background:#f0edff;color:#6758bd;border:1.5px solid #d9d4f5;font-size:0.85rem;border-radius:10px;font-weight:800;letter-spacing:0.02em;">
                    <i class="fas fa-undo-alt mr-1"></i> SALE RETURN MODE
                </span>
                <div>
                    <span class="font-weight-bold text-dark" style="font-size:1.05rem;">
                        Invoice #{{ $order->getInvoiceNumber() }}
                    </span>
                    <span class="badge ml-2 px-2 py-1" style="background:{{ $orderTypeInfo['bg'] }};color:{{ $orderTypeInfo['color'] }};border:1px solid {{ $orderTypeInfo['border'] }};border-radius:6px;font-weight:700;font-size:0.75rem;">
                        <i class="{{ $orderTypeInfo['icon'] }} mr-1"></i>{{ $orderTypeInfo['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="text-right mr-3 d-none d-md-block">
                <small class="text-muted d-block" style="font-size:0.75rem;">Original Order Date</small>
                <span class="font-weight-bold text-dark" style="font-size:0.85rem;">{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
            @if($order->branch)
                <span class="badge badge-light border px-2 py-1 mr-2" style="font-weight:600;font-size:0.78rem;">
                    <i class="fas fa-store mr-1 text-primary"></i>{{ $order->branch->name }}
                    @if($order->counter) <small class="text-muted">({{ $order->counter->name }})</small> @endif
                </span>
            @endif
        </div>
    </header>

    {{-- Main POS Body --}}
    <div class="pos-return-body">
        {{-- Left: Purchased Items in this Order --}}
        <main class="pos-items-panel">
            <div class="pos-items-header">
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold text-dark mr-2" style="font-size:0.95rem;">
                        <i class="fas fa-boxes-stacked mr-1 text-primary"></i> Order Items ({{ count($returnItems) }})
                    </span>
                    <span class="text-muted small">Select items and quantities to return</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnSelectAll" class="btn btn-light btn-sm mr-2" style="border:1px solid var(--snd-border-strong);border-radius:8px;font-size:0.78rem;font-weight:700;color:#505469;">
                        <i class="fas fa-check-double mr-1"></i> Select All
                    </button>
                    <button type="button" id="btnDeselectAll" class="btn btn-light btn-sm" style="border:1px solid var(--snd-border-strong);border-radius:8px;font-size:0.78rem;font-weight:700;color:#626b7b;">
                        Deselect All
                    </button>
                </div>
            </div>

            <div class="pos-items-list" id="returnItemsList">
                @if(isset($hasReturnableItems) && !$hasReturnableItems)
                    <div class="alert alert-danger d-flex align-items-center mb-3" style="border-radius:12px;border:1.5px solid #f2b8b5;background:#fff0ef;color:#991b1b;padding:14px 18px;">
                        <i class="fas fa-exclamation-triangle fa-2x mr-3 text-danger"></i>
                        <div>
                            <div style="font-weight:800;font-size:1.05rem;">Order 100% Returned</div>
                            <div style="font-size:0.88rem;color:#7f1d1d;">All items in this order have already been completely returned. No further returns can be processed.</div>
                        </div>
                    </div>
                @endif

                @forelse($returnItems as $index => $item)
                    @php
                        $isDeal = $item['is_deal'];
                        $isService = $item['type'] === 'service';
                        $isFullyReturned = !empty($item['is_fully_returned']);
                    @endphp
                    <div class="return-item-card {{ $isFullyReturned ? 'item-fully-returned is-deselected' : 'is-selected' }}"
                         data-index="{{ $index }}"
                         id="item-card-{{ $item['order_item_id'] }}"
                         @if($isFullyReturned) style="background:#fff0ef !important;border:1.5px solid #f2b8b5 !important;opacity:0.9;" @endif>
                        {{-- Checkbox --}}
                        <div class="item-check-wrap">
                            @if(!$isFullyReturned)
                                <input type="checkbox"
                                       class="item-checkbox"
                                       id="chk-{{ $item['order_item_id'] }}"
                                       data-order-item-id="{{ $item['order_item_id'] }}"
                                       checked>
                            @else
                                <span class="badge" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;padding:6px;border-radius:6px;font-size:0.75rem;" title="100% Returned - Action not available">
                                    <i class="fas fa-ban"></i>
                                </span>
                                <input type="checkbox"
                                       class="item-checkbox"
                                       id="chk-{{ $item['order_item_id'] }}"
                                       data-order-item-id="{{ $item['order_item_id'] }}"
                                       disabled
                                       style="display:none;">
                            @endif
                        </div>

                        {{-- Item Info --}}
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="font-weight-bold text-dark" style="font-size:0.95rem;line-height:1.2;">
                                    {{ $item['name'] }}
                                </div>
                                <div class="d-flex align-items-center" style="gap:4px;">
                                    @if($isFullyReturned)
                                        <span class="badge" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;font-weight:800;font-size:0.72rem;border-radius:6px;padding:3px 8px;">
                                            <i class="fas fa-undo mr-1"></i> 100% Returned
                                        </span>
                                    @endif
                                    <span class="badge" style="
                                        background: {{ $isDeal ? '#f0edff' : ($isService ? '#ccfbf1' : '#f0edff') }};
                                        color: {{ $isDeal ? '#6758bd' : ($isService ? '#16749c' : '#6758bd') }};
                                        border: 1px solid {{ $isDeal ? '#f0edff' : ($isService ? '#99f6e4' : '#d9d4f5') }};
                                        font-weight: 700; font-size: 0.72rem; border-radius: 6px; padding: 3px 8px;">
                                        <i class="fas fa-{{ $isDeal ? 'tags' : ($isService ? 'spa' : 'box-open') }} mr-1"></i>
                                        {{ $item['type_label'] }}
                                    </span>
                                </div>
                            </div>

                            @if($item['category'])
                                <small class="text-muted d-block mb-1" style="font-size:0.75rem;">
                                    <i class="fas fa-folder-open mr-1"></i>{{ $item['category'] }}
                                </small>
                            @endif

                            {{-- If Deal, show included services list --}}
                            @if($isDeal && !empty($item['deal_services']))
                                <div class="mt-2 p-2" style="background:#f0edff;border:1px solid #f3e8ff;border-radius:8px;">
                                    <small class="font-weight-bold text-uppercase d-block mb-1" style="color:#7e22ce;font-size:0.68rem;letter-spacing:0.04em;">
                                        <i class="fas fa-layer-group mr-1"></i> Included in Deal Package ({{ count($item['deal_services']) }}):
                                    </small>
                                    <div class="d-flex flex-wrap" style="gap:4px;">
                                        @foreach($item['deal_services'] as $ds)
                                            <span class="deal-subservice-pill">
                                                ✓ {{ $ds['name'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                <div class="text-muted small">
                                    Purchased: <strong class="text-dark">{{ $item['purchased_qty'] ?? $item['original_qty'] }}</strong>
                                    @if(!empty($item['already_returned']) && $item['already_returned'] > 0)
                                        <span class="text-danger font-weight-bold">({{ $item['already_returned'] }} returned)</span>
                                    @endif
                                    × <span style="font-family:monospace;">{{ config('settings.currency_symbol', 'PKR') }} {{ number_format($item['unit_price'], 2) }}</span>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted d-block" style="font-size:0.72rem;">Line Refund</small>
                                    <span class="font-weight-bold text-dark item-line-total" id="line-total-{{ $item['order_item_id'] }}" style="font-size:0.95rem;font-family:monospace;">
                                        {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($item['line_total'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Quantity Stepper / Status Action --}}
                        <div class="text-center pl-2 flex-shrink-0">
                            @if(!$isFullyReturned)
                                <small class="text-muted d-block font-weight-bold mb-1" style="font-size:0.72rem;text-transform:uppercase;">Return Qty</small>
                                <div class="qty-stepper">
                                    <button type="button" class="qty-btn btn-qty-minus" data-id="{{ $item['order_item_id'] }}">-</button>
                                    <input type="number"
                                           class="qty-input"
                                           id="qty-{{ $item['order_item_id'] }}"
                                           value="{{ $item['original_qty'] }}"
                                           min="1"
                                           max="{{ $item['original_qty'] }}"
                                           data-max="{{ $item['original_qty'] }}"
                                           data-price="{{ $item['unit_price'] }}"
                                           data-id="{{ $item['order_item_id'] }}"
                                           readonly>
                                    <button type="button" class="qty-btn btn-qty-plus" data-id="{{ $item['order_item_id'] }}">+</button>
                                </div>
                            @else
                                <small class="text-danger d-block font-weight-bold mb-1" style="font-size:0.72rem;text-transform:uppercase;">Action</small>
                                <span class="badge" style="background:#fff0ef;color:#c9342d;border:1px solid #f2b8b5;padding:6px 10px;border-radius:8px;font-weight:700;font-size:0.78rem;" title="100% of this item has been returned">
                                    <i class="fas fa-check-double mr-1"></i>Returned
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-lg border my-3">
                        <div style="font-size:2.5rem;color:#1f7a3d;margin-bottom:0.75rem;"><i class="fas fa-check-circle"></i></div>
                        <div style="font-size:1.15rem;font-weight:700;color:#1a1a2e;">All Items Already Returned</div>
                        <p class="text-muted mb-3">All services and deals from this order have already been returned.</p>
                        <a href="{{ route('sale-returns.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-2" style="border-radius:10px;">
                            <i class="fas fa-arrow-left mr-1"></i> Return to Orders List
                        </a>
                    </div>
                @endforelse
            </div>
        </main>

        {{-- Right: POS Summary & Return Processing --}}
        <aside class="pos-summary-panel">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                <div>
                    <h5 class="font-weight-bold text-dark mb-0" style="font-size:1.1rem;letter-spacing:-0.01em;">
                        Return Summary
                    </h5>
                    <small class="text-muted">Review & confirm refund amount</small>
                </div>
                <div style="width:38px;height:38px;border-radius:10px;background:#f0edff;display:flex;align-items:center;justify-content:center;color:#6758bd;font-size:1.1rem;">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>

            {{-- Customer Card --}}
            <div class="card shadow-none mb-3" style="background:var(--snd-surface-soft);border:1.5px solid var(--snd-border);border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="font-weight-bold text-muted text-uppercase" style="font-size:0.7rem;letter-spacing:0.04em;">
                            <i class="fas fa-user-circle mr-1 text-primary"></i> Customer
                        </small>
                        <span class="badge badge-light border text-muted" style="font-size:0.68rem;">Original</span>
                    </div>
                    <div class="font-weight-bold text-dark" style="font-size:0.95rem;">
                        {{ $order->getCustomerName() }}
                    </div>
                    @if($order->customer?->phone)
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-phone-alt mr-1" style="font-size:0.7rem;"></i>{{ $order->customer->phone }}
                        </small>
                    @endif
                </div>
            </div>

            {{-- Financial Calculation Box --}}
            <div class="card shadow-none mb-3" style="background:#ffffff;border:1.5px solid var(--snd-border-strong);border-radius:14px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;">
                        <span>Original Order Total:</span>
                        <span class="font-weight-bold text-dark" style="font-family:monospace;">
                            {{ config('settings.currency_symbol', 'PKR') }} {{ number_format($order->total(), 2) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;">
                        <span>Units returning:</span>
                        <span class="font-weight-bold text-primary" id="dispReturnCount">0 selected</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;">
                        <span>Refund Subtotal:</span>
                        <span class="font-weight-bold text-dark" style="font-family:monospace;">
                            {{ config('settings.currency_symbol', 'PKR') }} <span id="dispSubtotal">0.00</span>
                        </span>
                    </div>

                    @php
                        $taxPct = (float) ($order->tax_percent ?? 0);
                        $discPct = (float) ($order->discount_percent ?? 0);
                    @endphp

                    @if($taxPct > 0)
                        <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;">
                            <span>Refund Tax ({{ $taxPct }}%):</span>
                            <span class="font-weight-bold text-dark" style="font-family:monospace;">
                                + {{ config('settings.currency_symbol', 'PKR') }} <span id="dispTax">0.00</span>
                            </span>
                        </div>
                    @endif

                    @if($discPct > 0)
                        <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:0.85rem;">
                            <span>Discount Adjustment ({{ $discPct }}%):</span>
                            <span class="font-weight-bold text-danger" style="font-family:monospace;">
                                - {{ config('settings.currency_symbol', 'PKR') }} <span id="dispDiscount">0.00</span>
                            </span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between pt-3 border-top mt-2 align-items-baseline">
                        <span class="font-weight-bold text-dark" style="font-size:1rem;">Total Refund Due:</span>
                        <span class="font-weight-bold" style="font-size:1.35rem;color:#6758bd;font-family:monospace;">
                            {{ config('settings.currency_symbol', 'PKR') }} <span id="dispTotalRefund">0.00</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Refund Payment Mode Selector (Professional UI) --}}
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="font-weight-bold text-muted text-uppercase mb-0" style="font-size:0.75rem;letter-spacing:0.04em;">
                        <i class="fas fa-wallet mr-1 text-primary"></i> Refund Payment Mode
                    </label>
                    @php
                        $origPayMethod = strtolower($order->payment_method ?? ($order->payments->first()?->payment_method ?? 'cash'));
                    @endphp
                    <span class="badge px-2 py-1" style="font-size:0.7rem; font-weight:700; background:var(--snd-surface-soft); color:#505469; border:1px solid var(--snd-border-strong); border-radius:6px;">
                        Orig: <i class="fas {{ $origPayMethod === 'card' ? 'fa-credit-card text-primary' : 'fa-money-bill-wave text-success' }} ml-1 mr-1"></i> {{ strtoupper($origPayMethod) }}
                    </span>
                </div>

                <input type="hidden" id="selRefundMethod" name="refund_method" value="{{ $origPayMethod === 'card' ? 'card' : 'cash' }}">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <!-- Cash Refund Card -->
                    <div id="refund-pay-cash" role="button" tabindex="0" class="refund-mode-card {{ $origPayMethod !== 'card' ? 'active' : '' }}" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 2px solid {{ $origPayMethod !== 'card' ? '#6758bd' : 'var(--snd-border)' }}; border-radius: 12px; background: {{ $origPayMethod !== 'card' ? '#f0edff' : 'var(--snd-surface-soft)' }}; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; user-select: none; outline: none; box-shadow: {{ $origPayMethod !== 'card' ? '0 3px 10px rgba(103, 88, 189, 0.15)' : 'none' }};">
                        <div class="refund-pay-icon" style="width: 36px; height: 36px; border-radius: 10px; background: {{ $origPayMethod !== 'card' ? '#6758bd' : 'var(--snd-border)' }}; color: {{ $origPayMethod !== 'card' ? '#ffffff' : '#626b7b' }}; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; transition: all 0.2s; box-shadow: {{ $origPayMethod !== 'card' ? '0 2px 6px rgba(103, 88, 189, 0.25)' : 'none' }};">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="refund-pay-title" style="font-size: 0.9rem; font-weight: 800; color: #1a1a2e; line-height: 1.2;">Cash</div>
                            <div class="refund-pay-sub" style="font-size: 0.7rem; color: #626b7b; margin-top: 1px;">Drawer Out</div>
                        </div>
                        <div class="refund-pay-indicator" style="width: 20px; height: 20px; border-radius: 50%; background: {{ $origPayMethod !== 'card' ? '#6758bd' : 'transparent' }}; border: 2px solid {{ $origPayMethod !== 'card' ? '#6758bd' : 'var(--snd-border-strong)' }}; color: {{ $origPayMethod !== 'card' ? '#ffffff' : 'transparent' }}; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; flex-shrink: 0; transition: all 0.2s;">
                            <i class="fas fa-check" style="opacity: {{ $origPayMethod !== 'card' ? '1' : '0' }}; color: #ffffff;"></i>
                        </div>
                    </div>

                    <!-- Card Refund Card -->
                    <div id="refund-pay-card" role="button" tabindex="0" class="refund-mode-card {{ $origPayMethod === 'card' ? 'active' : '' }}" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 2px solid {{ $origPayMethod === 'card' ? '#6758bd' : 'var(--snd-border)' }}; border-radius: 12px; background: {{ $origPayMethod === 'card' ? '#f0edff' : 'var(--snd-surface-soft)' }}; cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; user-select: none; outline: none; box-shadow: {{ $origPayMethod === 'card' ? '0 3px 10px rgba(103, 88, 189, 0.15)' : 'none' }};">
                        <div class="refund-pay-icon" style="width: 36px; height: 36px; border-radius: 10px; background: {{ $origPayMethod === 'card' ? '#6758bd' : 'var(--snd-border)' }}; color: {{ $origPayMethod === 'card' ? '#ffffff' : '#626b7b' }}; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; transition: all 0.2s; box-shadow: {{ $origPayMethod === 'card' ? '0 2px 6px rgba(103, 88, 189, 0.25)' : 'none' }};">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="refund-pay-title" style="font-size: 0.9rem; font-weight: 800; color: #1a1a2e; line-height: 1.2;">Card</div>
                            <div class="refund-pay-sub" style="font-size: 0.7rem; color: #626b7b; margin-top: 1px;">POS Reversal</div>
                        </div>
                        <div class="refund-pay-indicator" style="width: 20px; height: 20px; border-radius: 50%; background: {{ $origPayMethod === 'card' ? '#6758bd' : 'transparent' }}; border: 2px solid {{ $origPayMethod === 'card' ? '#6758bd' : 'var(--snd-border-strong)' }}; color: {{ $origPayMethod === 'card' ? '#ffffff' : 'transparent' }}; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; flex-shrink: 0; transition: all 0.2s;">
                            <i class="fas fa-check" style="opacity: {{ $origPayMethod === 'card' ? '1' : '0' }}; color: #ffffff;"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Refund Settlement Fields (Visible when Card is selected) --}}
            @php
                $defaultCardHolder = $order->card_holder_name ?: $order->getCustomerName();
                $defaultCardLast4 = $order->card_last_four ?: '';
                $defaultReceivingAcc = $order->receiving_account_id ?: '';
            @endphp
            <div id="return-card-panel" class="card shadow-none mb-3" style="display: {{ $origPayMethod === 'card' ? 'block' : 'none' }}; background: #ffffff; border: 1.5px solid #b9aefe; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(103, 88, 189, 0.08);">
                <div style="background: linear-gradient(135deg, #f0edff 0%, #f0edff 100%); padding: 9px 14px; border-bottom: 1.5px solid #d9d4f5; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 26px; height: 26px; border-radius: 7px; background: #6758bd; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span style="font-weight: 800; font-size: 0.85rem; color: #6758bd; letter-spacing: -0.01em;">
                            Card & Account Details
                        </span>
                    </div>
                    @if($order->card_last_four || $order->receiving_account_id)
                        <span class="badge" style="background: #6758bd; color: #fff; font-size: 0.68rem; font-weight: 700; border-radius: 6px; padding: 2px 7px;">
                            <i class="fas fa-magic mr-1"></i> Pre-filled from Order
                        </span>
                    @endif
                </div>

                <div class="card-body p-3">
                    {{-- Row 1: Card Holder & Last 4 digits --}}
                    <div class="row">
                        <div class="col-7 pr-1">
                            <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                                Card Holder Name
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: var(--snd-surface-soft); border: 1.5px solid var(--snd-border-strong); border-right: none; border-radius: 9px 0 0 9px; color: #626b7b; font-size: 0.82rem;">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>
                                <input type="text"
                                       id="txtCardHolderName"
                                       name="card_holder_name"
                                       class="form-control"
                                       placeholder="Holder name"
                                       value="{{ $defaultCardHolder }}"
                                       style="border-radius: 0 9px 9px 0; font-size: 0.86rem; font-weight: 600; border: 1.5px solid var(--snd-border-strong); color: #1a1a2e;">
                            </div>
                        </div>

                        <div class="col-5 pl-1">
                            <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                                Last 4 Digits <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: var(--snd-surface-soft); border: 1.5px solid var(--snd-border-strong); border-right: none; border-radius: 9px 0 0 9px; color: #626b7b; font-size: 0.82rem; font-family: monospace;">
                                        ••
                                    </span>
                                </div>
                                <input type="text"
                                       id="txtCardLastFour"
                                       name="card_last_four"
                                       class="form-control"
                                       placeholder="4242"
                                       maxlength="4"
                                       value="{{ $defaultCardLast4 }}"
                                       style="border-radius: 0 9px 9px 0; font-size: 0.95rem; font-weight: 800; font-family: monospace; letter-spacing: 2px; border: 1.5px solid var(--snd-border-strong); text-align: center; color: #1a1a2e;">
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Refunding Bank Account (Select2 Dropdown) --}}
                    <div class="mt-2 pt-1">
                        <label class="font-weight-bold text-muted text-uppercase mb-1 d-flex justify-content-between align-items-center" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                            <span><i class="fas fa-university mr-1 text-primary"></i> Refunding Bank Account <span class="text-danger">*</span></span>
                            <span class="text-muted font-italic" style="font-size: 0.65rem; text-transform: none;">Account refund is drawn from</span>
                        </label>
                        <select id="selReceivingAccount" name="receiving_account_id" class="form-control select2-bank-account" style="width: 100%;">
                            <option value="">— Select Bank Account —</option>
                            @foreach($paymentAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ (string)$defaultReceivingAcc === (string)$acc->id ? 'selected' : '' }}>
                                    {{ $acc->name }} @if($acc->bank_name && $acc->bank_name !== $acc->name) ({{ $acc->bank_name }}) @endif @if($acc->account_number) - #{{ $acc->account_number }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="font-weight-bold text-muted text-uppercase mb-1" style="font-size:0.7rem;letter-spacing:0.04em;">
                    <i class="fas fa-question-circle mr-1 text-primary"></i> Return Reason
                </label>
                <textarea id="txtReturnReason"
                          rows="2"
                          class="form-control"
                          placeholder="Enter reason for return..."
                          style="border-radius:10px;font-size:0.88rem;border:1.5px solid var(--snd-border-strong);padding:8px 12px;resize:vertical;"></textarea>
            </div>

            {{-- Submit Action Button --}}
            <div class="mt-auto pt-2">
                <button type="button"
                        id="btnProcessReturn"
                        class="btn btn-primary btn-block py-3 font-weight-bold"
                        style="background:var(--snd-button-primary);border:1px solid var(--snd-button-primary);border-radius:12px;font-size:1rem;color:var(--snd-button-primary-text);box-shadow:0 6px 20px var(--snd-primary-ring);letter-spacing:0.02em;">
                    <i class="fas fa-undo-alt mr-2"></i> Confirm & Process Return
                </button>
            </div>
        </aside>
    </div>
</div>

{{-- Hidden Thermal Print Area for Receipt --}}
<div id="return-print-area" style="display:none;">
    <div style="text-align:center;font-family:monospace;padding:10px;font-size:12px;color:#000;">
        <h3 style="margin:0;font-size:16px;font-weight:bold;">{{ \App\Helpers\CommonHelper::getBrandName() }}</h3>
        <p style="margin:2px 0;">{{ \App\Helpers\CommonHelper::getBrandSubtitle() }}</p>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">
        <h4 style="margin:4px 0;font-size:14px;font-weight:bold;">*** SALE RETURN RECEIPT ***</h4>
        <p style="margin:2px 0;">Return #: <span id="prtReturnNo"></span></p>
        <p style="margin:2px 0;">Original Inv #: {{ $order->getInvoiceNumber() }}</p>
        <p style="margin:2px 0;">Date: {{ now()->format('d M Y, h:i A') }}</p>
        <p style="margin:2px 0;">Customer: {{ $order->getCustomerName() }}</p>
        <p style="margin:2px 0;">Cashier: {{ auth()->user()->first_name ?? 'Cashier' }}</p>
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
            <span>Refund Subtotal:</span>
            <span id="prtSubtotal"></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin:2px 0;font-weight:bold;font-size:14px;">
            <span>TOTAL REFUND:</span>
            <span id="prtTotal"></span>
        </div>
        <p style="margin:6px 0 2px;">Method: <span id="prtMethod">Cash</span></p>
        <hr style="border:none;border-top:1px dashed #000;margin:6px 0;">

        {{-- SRB Fiscal Section (Appears when SRB returns an invoice ID) --}}
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = "{{ config('settings.currency_symbol', 'PKR') }}";
    const taxPercent = {{ (float) ($order->tax_percent ?? 0) }};
    const discountPercent = {{ (float) ($order->discount_percent ?? 0) }};
    const orderId = {{ $order->id }};

    // Initialize Select2 on Refunding Bank Account
    if (window.jQuery && typeof $.fn.select2 === 'function') {
        $('#selReceivingAccount').select2({
            placeholder: "— Select Bank Account —",
            allowClear: false,
            width: '100%'
        });
    }

    // Restrict Card Last Four to numeric 4 digits only
    $('#txtCardLastFour').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
    });

    function formatNumber(num) {
        return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Recalculate Live Refund Totals
    function recalculate() {
        let subtotal = 0;
        let selectedCount = 0;

        $('.item-checkbox:not(:disabled)').each(function () {
            const chk = $(this);
            const card = chk.closest('.return-item-card');
            const id = chk.data('order-item-id');
            const qtyInput = $('#qty-' + id);
            const price = parseFloat(qtyInput.data('price')) || 0;
            const qty = parseInt(qtyInput.val(), 10) || 0;

            if (chk.is(':checked') && qty > 0) {
                card.removeClass('is-deselected').addClass('is-selected');
                const lineTotal = price * qty;
                $('#line-total-' + id).text(currency + ' ' + formatNumber(lineTotal));
                subtotal += lineTotal;
                selectedCount += qty;
            } else {
                card.removeClass('is-selected').addClass('is-deselected');
                $('#line-total-' + id).text(currency + ' 0.00');
            }
        });

        const tax = taxPercent > 0 ? (subtotal * taxPercent / 100) : 0;
        const discount = discountPercent > 0 ? (subtotal * discountPercent / 100) : 0;
        const totalRefund = Math.max(0, subtotal + tax - discount);

        $('#dispReturnCount').text(selectedCount + (selectedCount === 1 ? ' unit' : ' units'));
        $('#dispSubtotal').text(formatNumber(subtotal));
        $('#dispTax').text(formatNumber(tax));
        $('#dispDiscount').text(formatNumber(discount));
        $('#dispTotalRefund').text(formatNumber(totalRefund));

        // Disable process button if no items selected
        if (selectedCount === 0 || totalRefund <= 0) {
            $('#btnProcessReturn').prop('disabled', true).css('opacity', '0.6');
        } else {
            $('#btnProcessReturn').prop('disabled', false).css('opacity', '1');
        }
    }

    // Checkbox toggle
    $(document).on('change', '.item-checkbox', function () {
        recalculate();
    });

    // Select All / Deselect All
    $('#btnSelectAll').on('click', function () {
        $('.item-checkbox:not(:disabled)').prop('checked', true);
        recalculate();
    });

    $('#btnDeselectAll').on('click', function () {
        $('.item-checkbox:not(:disabled)').prop('checked', false);
        recalculate();
    });

    // Quantity Plus
    $(document).on('click', '.btn-qty-plus', function () {
        const id = $(this).data('id');
        const input = $('#qty-' + id);
        const max = parseInt(input.data('max'), 10) || 1;
        let current = parseInt(input.val(), 10) || 1;

        if (current < max) {
            input.val(current + 1);
            // Ensure checkbox is checked
            $('#chk-' + id).prop('checked', true);
            recalculate();
        }
    });

    // Quantity Minus
    $(document).on('click', '.btn-qty-minus', function () {
        const id = $(this).data('id');
        const input = $('#qty-' + id);
        let current = parseInt(input.val(), 10) || 1;

        if (current > 1) {
            input.val(current - 1);
            recalculate();
        }
    });

    // Initial calculation
    recalculate();

    // Refund Mode Selection (Cash vs Card)
    function setRefundMode(mode) {
        $('#selRefundMethod').val(mode);
        const cashCard = document.getElementById('refund-pay-cash');
        const cardCard = document.getElementById('refund-pay-card');
        const cardPanel = $('#return-card-panel');

        if (mode === 'cash') {
            cardPanel.slideUp(200);
            $('#refund-pay-cash').addClass('active');
            $('#refund-pay-card').removeClass('active');
            if (cashCard) {
                cashCard.style.borderColor = '#6758bd';
                cashCard.style.background = '#f0edff';
                cashCard.style.boxShadow = '0 3px 10px rgba(103, 88, 189, 0.15)';
                const icon = cashCard.querySelector('.refund-pay-icon');
                if (icon) { icon.style.background = '#6758bd'; icon.style.color = '#ffffff'; icon.style.boxShadow = '0 2px 6px rgba(103, 88, 189, 0.25)'; }
                const ind = cashCard.querySelector('.refund-pay-indicator');
                if (ind) { ind.style.background = '#6758bd'; ind.style.borderColor = '#6758bd'; ind.style.color = '#ffffff'; }
                const check = cashCard.querySelector('.refund-pay-indicator i');
                if (check) { check.style.opacity = '1'; check.style.color = '#ffffff'; }
            }
            if (cardCard) {
                cardCard.style.borderColor = 'var(--snd-border)';
                cardCard.style.background = 'var(--snd-surface-soft)';
                cardCard.style.boxShadow = 'none';
                const icon = cardCard.querySelector('.refund-pay-icon');
                if (icon) { icon.style.background = 'var(--snd-border)'; icon.style.color = '#626b7b'; icon.style.boxShadow = 'none'; }
                const ind = cardCard.querySelector('.refund-pay-indicator');
                if (ind) { ind.style.background = 'transparent'; ind.style.borderColor = 'var(--snd-border-strong)'; ind.style.color = 'transparent'; }
                const check = cardCard.querySelector('.refund-pay-indicator i');
                if (check) { check.style.opacity = '0'; check.style.color = 'transparent'; }
            }
        } else {
            cardPanel.slideDown(200);
            $('#refund-pay-card').addClass('active');
            $('#refund-pay-cash').removeClass('active');
            if (cardCard) {
                cardCard.style.borderColor = '#6758bd';
                cardCard.style.background = '#f0edff';
                cardCard.style.boxShadow = '0 3px 10px rgba(103, 88, 189, 0.15)';
                const icon = cardCard.querySelector('.refund-pay-icon');
                if (icon) { icon.style.background = '#6758bd'; icon.style.color = '#ffffff'; icon.style.boxShadow = '0 2px 6px rgba(103, 88, 189, 0.25)'; }
                const ind = cardCard.querySelector('.refund-pay-indicator');
                if (ind) { ind.style.background = '#6758bd'; ind.style.borderColor = '#6758bd'; ind.style.color = '#ffffff'; }
                const check = cardCard.querySelector('.refund-pay-indicator i');
                if (check) { check.style.opacity = '1'; check.style.color = '#ffffff'; }
            }
            if (cashCard) {
                cashCard.style.borderColor = 'var(--snd-border)';
                cashCard.style.background = 'var(--snd-surface-soft)';
                cashCard.style.boxShadow = 'none';
                const icon = cashCard.querySelector('.refund-pay-icon');
                if (icon) { icon.style.background = 'var(--snd-border)'; icon.style.color = '#626b7b'; icon.style.boxShadow = 'none'; }
                const ind = cashCard.querySelector('.refund-pay-indicator');
                if (ind) { ind.style.background = 'transparent'; ind.style.borderColor = 'var(--snd-border-strong)'; ind.style.color = 'transparent'; }
                const check = cashCard.querySelector('.refund-pay-indicator i');
                if (check) { check.style.opacity = '0'; check.style.color = 'transparent'; }
            }
        }
    }

    // Set initial mode state on load
    setRefundMode($('#selRefundMethod').val() || 'cash');

    $('#refund-pay-cash').on('click', function () {
        setRefundMode('cash');
    });

    $('#refund-pay-card').on('click', function () {
        setRefundMode('card');
    });

    $('.refund-mode-card').on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    // Process Return Action
    $('#btnProcessReturn').on('click', function () {
        const selectedItems = [];

        $('.item-checkbox:not(:disabled):checked').each(function () {
            const chk = $(this);
            const id = chk.data('order-item-id');
            const qty = parseInt($('#qty-' + id).val(), 10) || 0;
            if (qty > 0) {
                selectedItems.push({
                    order_item_id: id,
                    quantity: qty,
                });
            }
        });

        if (selectedItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nothing Selected',
                text: 'Please select at least one service or deal to return.',
                confirmButtonColor: '#6758bd',
            });
            return;
        }

        const refundTotalText = $('#dispTotalRefund').text();
        const refundMethod = ($('#selRefundMethod').val() || 'cash').toLowerCase();
        const returnReason = $('#txtReturnReason').val();

        const isCard = refundMethod === 'card';
        let cardHolder = '';
        let cardLast4 = '';
        let receivingAccountId = '';
        let bankName = '';

        if (isCard) {
            cardHolder = ($('#txtCardHolderName').val() || '').trim();
            cardLast4 = ($('#txtCardLastFour').val() || '').trim();
            receivingAccountId = $('#selReceivingAccount').val();
            bankName = $('#selReceivingAccount option:selected').text().trim();

            if (!cardLast4 || cardLast4.length !== 4) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Card Digits Required',
                    text: 'Please enter the exact last 4 digits of the customer card.',
                    confirmButtonColor: '#6758bd',
                }).then(() => {
                    $('#txtCardLastFour').focus();
                });
                return;
            }

            if (!receivingAccountId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bank Account Required',
                    text: 'Please select the bank account from which refund is being settled.',
                    confirmButtonColor: '#6758bd',
                }).then(() => {
                    if (window.jQuery && typeof $.fn.select2 === 'function') {
                        $('#selReceivingAccount').select2('open');
                    }
                });
                return;
            }
        }

        const methodBadgeHtml = isCard
            ? `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#f0edff;color:#6758bd;border:1px solid #d9d4f5;font-weight:800;font-size:0.82rem;"><i class="fas fa-credit-card text-primary"></i> CARD (POS Reversal)</span>`
            : `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;font-weight:800;font-size:0.82rem;"><i class="fas fa-money-bill-wave text-success"></i> CASH (Drawer Out)</span>`;

        const cardDetailsHtml = isCard ? `
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted" style="font-size:0.82rem;">Card Digits:</span>
                <strong class="text-dark font-monospace">•••• ${cardLast4} ${cardHolder ? `(${cardHolder})` : ''}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted" style="font-size:0.82rem;">Refund Bank Account:</span>
                <strong class="text-primary" style="font-size:0.82rem;">${bankName}</strong>
            </div>
        ` : '';

        Swal.fire({
            title: 'Confirm Sale Return?',
            html: `
                <div class="text-left" style="font-size:0.9rem;color:#505469;">
                    <div style="background:var(--snd-surface-soft);border:1px solid var(--snd-border);border-radius:12px;padding:12px 14px;margin-bottom:12px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size:0.82rem;">Invoice:</span>
                            <strong class="text-dark">{{ $order->getInvoiceNumber() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size:0.82rem;">Customer:</span>
                            <strong class="text-dark">{{ $order->getCustomerName() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted" style="font-size:0.82rem;">Items to Return:</span>
                            <strong class="text-dark">${selectedItems.length} lines</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-1" style="border-top:1px dashed var(--snd-border-strong);">
                            <span class="text-muted" style="font-size:0.82rem;">Refund Mode:</span>
                            ${methodBadgeHtml}
                        </div>
                        ${cardDetailsHtml}
                        ${returnReason ? `
                        <div class="d-flex justify-content-between mt-2 pt-1">
                            <span class="text-muted" style="font-size:0.82rem;">Reason:</span>
                            <span class="text-dark font-italic">${returnReason}</span>
                        </div>` : ''}
                    </div>
                    <div style="background:#f0edff;border:1.5px solid #b9aefe;border-radius:12px;padding:12px 14px;display:flex;justify-content:space-between;align-items:baseline;">
                        <span style="font-weight:700;color:#6758bd;font-size:0.95rem;">Total Refund Due:</span>
                        <span style="font-weight:900;font-size:1.35rem;color:#6758bd;font-family:monospace;">
                            ${currency} ${refundTotalText}
                        </span>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Yes, Process Return (Enter)',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#6758bd',
            cancelButtonColor: '#626b7b',
            focusConfirm: true,
            didOpen: () => {
                const confirmBtn = Swal.getConfirmButton();
                if (confirmBtn) confirmBtn.focus();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $('#btnProcessReturn');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing Return...');

                $.ajax({
                    url: "{{ route('sale-returns.process') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: orderId,
                        items: selectedItems,
                        refund_method: refundMethod,
                        card_holder_name: isCard ? cardHolder : null,
                        card_last_four: isCard ? cardLast4 : null,
                        receiving_account_id: isCard ? receivingAccountId : null,
                        return_reason: returnReason,
                    },
                    success: function (res) {
                        btn.prop('disabled', false).html('<i class="fas fa-undo-alt mr-2"></i> Confirm & Process Return');

                        if (res.success) {
                            // Populate print receipt
                            $('#prtReturnNo').text(res.return_no);
                            $('#prtSubtotal').text(currency + ' ' + $('#dispSubtotal').text());
                            $('#prtTotal').text(currency + ' ' + res.refund_total);
                            if (refundMethod === 'card') {
                                const receiptDigits = res.card_last_four || cardLast4;
                                const receiptBank = res.receiving_account || bankName;
                                $('#prtMethod').html('CARD (••' + receiptDigits + ')<br><small style="font-size:10px;">Bank: ' + receiptBank + '</small>');
                            } else {
                                $('#prtMethod').text('CASH');
                            }

                            const prtBody = $('#prtItemsBody');
                            prtBody.empty();
                            if (res.sale_return && res.sale_return.items) {
                                res.sale_return.items.forEach(function (ri) {
                                    prtBody.append(`
                                        <tr>
                                            <td style="padding:2px 0;">${ri.item_name}</td>
                                            <td style="text-align:center;">${ri.quantity}</td>
                                            <td style="text-align:right;">${currency} ${formatNumber(ri.refund_amount)}</td>
                                        </tr>
                                    `);
                                });
                            }

                            // Handle SRB section on receipt
                            if (res.srb_invoice_id) {
                                $('#prtSrbSection').show();
                                $('#prtSrbInvoiceId').text(res.srb_invoice_id);
                                if (res.srb_qr_code_link) {
                                    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(res.srb_qr_code_link);
                                    $('#prtSrbQrCode').attr('src', qrUrl);
                                    $('#prtSrbQrWrapper').show();
                                } else {
                                    $('#prtSrbQrWrapper').hide();
                                }
                            } else {
                                $('#prtSrbSection').hide();
                            }

                            const srbBadgeHtml = res.srb_invoice_id
                                ? `<div class="mt-2"><span class="badge px-3 py-2 font-weight-bold" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;border-radius:20px;font-size:0.85rem;"><i class="fas fa-check-circle mr-1"></i> SRB Fiscal Invoice: ${res.srb_invoice_id}</span></div>`
                                : '';

                            Swal.fire({
                                icon: 'success',
                                title: 'Sale Return Processed!',
                                html: `
                                    <div class="text-center">
                                        <p class="mb-2">${res.message}</p>
                                        <p class="font-weight-bold mb-1" style="font-size:1.15rem;color:#6758bd;">
                                            Return #: ${res.return_no}
                                        </p>
                                        ${srbBadgeHtml}
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-print mr-1"></i> Print Receipt',
                                cancelButtonText: '<i class="fas fa-clipboard-list mr-1"></i> Return List',
                                confirmButtonColor: '#6758bd',
                                cancelButtonColor: '#1f7a3d',
                            }).then((action) => {
                                if (action.isConfirmed) {
                                    // Trigger print
                                    const printContents = document.getElementById('return-print-area').innerHTML;
                                    const printWindow = window.open('', '', 'height=600,width=400');
                                    printWindow.document.write('<html><head><title>Return Receipt</title></head><body>');
                                    printWindow.document.write(printContents);
                                    printWindow.document.write('</body></html>');
                                    printWindow.document.close();
                                    printWindow.focus();
                                    setTimeout(() => {
                                        printWindow.print();
                                        printWindow.close();
                                        window.location.href = "{{ route('sale-returns.list') }}";
                                    }, 250);
                                } else {
                                    window.location.href = "{{ route('sale-returns.list') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Return Failed',
                                text: res.message || 'An error occurred.',
                                confirmButtonColor: '#6758bd',
                            });
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-undo-alt mr-2"></i> Confirm & Process Return');
                        const errorMsg = xhr.responseJSON?.message || 'Server error processing return.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#6758bd',
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
