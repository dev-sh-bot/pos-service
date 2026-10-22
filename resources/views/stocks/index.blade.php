@extends('layouts.admin')

@section('title', 'Stock Report & Branch Inventory')
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('Stock Inventory Report'))
@section('content-subtitle', __('Branch-wise stock balance, valuation, and transaction ledger'))
@section('content-actions')
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-outline-primary">
        <x-snd-icon name="arrow-right-left" class="mr-1" /> {{ __('Stock Transfers') }}
    </a>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <x-snd-icon name="plus" class="mr-1" /> {{ __('New Purchase') }}
    </a>
@endsection

@section('content')
<div class="snd-page stock-report-page">
    <section class="stock-kpi-grid" aria-label="{{ __('Inventory summary') }}">
        <article class="stock-kpi-card stock-kpi-card--primary">
            <span class="stock-kpi-icon"><x-snd-icon name="package" /></span>
            <span class="stock-kpi-label">{{ __('Total unique items') }}</span>
            <strong class="stock-kpi-value" id="kpi-total-items">—</strong>
            <span class="stock-kpi-help">{{ __('Products with inventory records') }}</span>
        </article>

        <article class="stock-kpi-card stock-kpi-card--success">
            <span class="stock-kpi-icon"><x-snd-icon name="box" /></span>
            <span class="stock-kpi-label">{{ __('Available quantity') }}</span>
            <strong class="stock-kpi-value" id="kpi-total-qty">—</strong>
            <span class="stock-kpi-help">{{ __('Units currently available') }}</span>
        </article>

        <article class="stock-kpi-card stock-kpi-card--primary">
            <span class="stock-kpi-icon"><x-snd-icon name="wallet" /></span>
            <span class="stock-kpi-label">{{ __('Inventory valuation') }}</span>
            <strong class="stock-kpi-value" id="kpi-total-valuation">—</strong>
            <span class="stock-kpi-help">{{ __('Cost value across all branches') }}</span>
        </article>

        <article class="stock-kpi-card stock-kpi-card--warning">
            <span class="stock-kpi-icon"><x-snd-icon name="triangle-alert" /></span>
            <span class="stock-kpi-label">{{ __('Low stock alert') }}</span>
            <strong class="stock-kpi-value" id="kpi-low-stock">—</strong>
            <span class="stock-kpi-help">{{ __('Items that need attention') }}</span>
        </article>
    </section>

    <section class="card stock-filter-card" aria-labelledby="stock-filter-title">
        <div class="card-body stock-filter-card-body">
            <div class="stock-filter-heading">
                <div class="stock-filter-heading-copy">
                    <span class="stock-filter-heading-icon"><x-snd-icon name="sliders" /></span>
                    <div>
                        <h2 id="stock-filter-title">{{ __('Find stock') }}</h2>
                        <p>{{ __('Results update automatically as you refine your filters.') }}</p>
                    </div>
                </div>
                <span class="stock-filter-live"><span></span>{{ __('Live inventory') }}</span>
            </div>

            <div class="stock-filter-grid">
                <div class="stock-filter-field">
                    <label for="filter-branch"><x-snd-icon name="git-branch" /> {{ __('Branch') }}</label>
                    <select id="filter-branch" class="form-control font-weight-bold">
                        <option value="all">{{ __('All branches (combined)') }}</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="stock-filter-field">
                    <label for="filter-category"><x-snd-icon name="tag" /> {{ __('Category') }}</label>
                    <select id="filter-category" class="form-control">
                        <option value="all">{{ __('All categories') }}</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="stock-filter-field">
                    <label for="filter-search"><x-snd-icon name="search" /> {{ __('Product or barcode') }}</label>
                    <div class="stock-search-control">
                        <input type="text" id="filter-search" class="form-control" placeholder="{{ __('Search product or barcode…') }}" autocomplete="off">
                        <button type="button" id="search-clear-btn" class="stock-search-clear" aria-label="{{ __('Clear search') }}" title="{{ __('Clear search') }}">
                            <x-snd-icon name="x" />
                        </button>
                    </div>
                </div>

                <div class="stock-filter-action">
                    <button type="button" id="btn-refresh" class="btn btn-outline-primary" title="{{ __('Refresh inventory data') }}">
                        <x-snd-icon name="refresh" />
                        <span>{{ __('Refresh') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="card stock-table-card" aria-labelledby="stock-table-title">
        <div class="card-header stock-table-header">
            <div class="stock-table-title-wrap">
                <span class="stock-table-title-icon"><x-snd-icon name="table" /></span>
                <div>
                    <h2 id="stock-table-title">{{ __('Stock items') }}</h2>
                    <p>{{ __('Current balances, branch allocation, and cost value') }}</p>
                </div>
            </div>
            <span id="filter-status-badge" class="stock-table-status">{{ __('Loading inventory…') }}</span>
        </div>
        <div class="stock-table-scroll table-responsive">
            <table class="table stock-summary-table" id="stock-summary-table">
                <thead>
                    <tr>
                        <th scope="col" class="text-center stock-index-column">{{ __('No.') }}</th>
                        <th scope="col" class="stock-item-column">{{ __('Item') }}</th>
                        <th scope="col" class="stock-category-column">{{ __('Category') }}</th>
                        <th scope="col" class="text-right stock-quantity-column">{{ __('Available') }}</th>
                        <th scope="col" class="stock-branch-column">{{ __('Branch allocation') }}</th>
                        <th scope="col" class="text-right stock-rate-column">{{ __('Cost rate') }}</th>
                        <th scope="col" class="text-right stock-value-column">{{ __('Valuation') }}</th>
                        <th scope="col" class="text-center stock-ledger-column">{{ __('Ledger') }}</th>
                    </tr>
                </thead>
                <tbody id="stock-table-body">
                    <tr class="snd-empty-row">
                        <td colspan="8" class="snd-table-empty-cell">
                            <div class="stock-table-feedback">
                                <x-snd-icon name="loader-circle" class="fa-spin" />
                                <strong>{{ __('Loading inventory') }}</strong>
                                <span>{{ __('Fetching the latest stock balances.') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade stock-ledger-modal" id="itemLedgerModal" tabindex="-1" role="dialog" aria-labelledby="ledger-item-name" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered stock-ledger-dialog" role="document">
        <div class="modal-content stock-ledger-modal-content">
            <div class="modal-header stock-ledger-modal-header">
                <div class="stock-ledger-heading">
                    <span class="stock-ledger-heading-icon"><x-snd-icon name="file-text" /></span>
                    <div>
                        <h5 class="modal-title" id="ledger-item-name">{{ __('Product stock ledger') }}</h5>
                        <p id="ledger-item-meta">{{ __('Item details and movement history') }}</p>
                    </div>
                </div>
                <button type="button" class="stock-modal-close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                    <x-snd-icon name="x" />
                </button>
            </div>

            <div class="modal-body p-0 stock-ledger-table-scroll">
                <table class="table stock-ledger-table" id="ledger-detail-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Voucher') }}</th>
                            <th scope="col">{{ __('Voucher type') }}</th>
                            <th scope="col">{{ __('Voucher date') }}</th>
                            <th scope="col">{{ __('Warehouse') }}</th>
                            <th scope="col" class="text-right">{{ __('Quantity') }}</th>
                            <th scope="col" class="text-right">{{ __('Rate') }}</th>
                            <th scope="col" class="text-right">{{ __('Amount') }}</th>
                            <th scope="col" class="text-right">{{ __('Balance qty') }}</th>
                            <th scope="col" class="text-right">{{ __('Balance amount') }}</th>
                            <th scope="col" class="text-right">{{ __('Average rate') }}</th>
                            <th scope="col" class="text-right">{{ __('Running amount') }}</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-table-body">
                        <tr class="snd-empty-row">
                            <td colspan="11" class="snd-table-empty-cell">
                                <div class="stock-table-feedback">
                                    <x-snd-icon name="loader-circle" class="fa-spin" />
                                    <strong>{{ __('Loading ledger') }}</strong>
                                    <span>{{ __('Preparing movement history.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer stock-ledger-modal-footer">
                <span id="ledger-summary-count">{{ __('0 transactions') }}</span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
.stock-report-page { width: 100%; }

.stock-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.stock-kpi-card {
    position: relative;
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-rows: auto auto auto;
    min-height: 122px;
    overflow: hidden;
    padding: 17px 18px;
    border: 1px solid var(--snd-border);
    border-radius: var(--snd-radius-lg);
    background: var(--snd-surface);
    box-shadow: var(--snd-shadow);
}

.stock-kpi-card::before {
    position: absolute;
    top: 16px;
    bottom: 16px;
    left: 0;
    width: 3px;
    border-radius: 0 4px 4px 0;
    background: var(--stock-accent, var(--snd-primary));
    content: '';
}

.stock-kpi-card--primary { --stock-accent: var(--snd-primary-deep); }
.stock-kpi-card--success { --stock-accent: var(--snd-success); }
.stock-kpi-card--warning { --stock-accent: var(--snd-warning); }

.stock-kpi-icon {
    grid-column: 2;
    grid-row: 1 / span 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 13px;
    background: var(--snd-primary-wash);
    color: var(--snd-primary-deep);
}

.stock-kpi-icon .snd-icon { width: 19px; height: 19px; }
.stock-kpi-card--success .stock-kpi-icon { background: var(--snd-success-soft); color: var(--snd-success); }
.stock-kpi-card--warning .stock-kpi-icon { background: var(--snd-warning-soft); color: var(--snd-warning); }

.stock-kpi-label {
    grid-column: 1;
    color: var(--snd-muted);
    font-size: .62rem;
    font-weight: var(--snd-font-semibold);
    letter-spacing: .08em;
    line-height: 1.25;
    text-transform: uppercase;
}

.stock-kpi-value {
    grid-column: 1 / -1;
    margin-top: 4px;
    overflow-wrap: anywhere;
    color: var(--stock-accent, var(--snd-primary-deep));
    font-size: clamp(1.12rem, 1.8vw, 1.45rem);
    font-weight: var(--snd-font-semibold);
    letter-spacing: -.035em;
    line-height: 1.15;
}

.stock-kpi-help {
    grid-column: 1 / -1;
    align-self: end;
    margin-top: 7px;
    color: var(--snd-muted);
    font-size: .66rem;
    line-height: 1.3;
}

.stock-filter-card { margin-bottom: 16px; }
.stock-filter-card-body { padding: 16px 18px 18px !important; }

.stock-filter-heading,
.stock-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.stock-filter-heading { margin-bottom: 15px; }
.stock-filter-heading-copy,
.stock-table-title-wrap,
.stock-ledger-heading {
    display: flex;
    align-items: center;
    min-width: 0;
}

.stock-filter-heading-copy { gap: 10px; }
.stock-filter-heading-icon,
.stock-table-title-icon,
.stock-ledger-heading-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: var(--snd-primary-wash);
    color: var(--snd-primary-deep);
}

.stock-filter-heading-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
}

.stock-filter-heading-icon .snd-icon { width: 16px; height: 16px; }
.stock-filter-heading h2,
.stock-table-title-wrap h2 {
    margin: 0;
    color: var(--snd-ink);
    font-size: .84rem;
    font-weight: var(--snd-font-semibold);
    line-height: 1.2;
}

.stock-filter-heading p,
.stock-table-title-wrap p {
    margin: 3px 0 0;
    color: var(--snd-muted);
    font-size: .67rem;
    line-height: 1.35;
}

.stock-filter-live,
.stock-table-status {
    display: inline-flex;
    align-items: center;
    flex: 0 0 auto;
    gap: 6px;
    padding: 5px 9px;
    border: 1px solid rgba(151, 134, 238, .2);
    border-radius: 999px;
    background: var(--snd-primary-wash);
    color: var(--snd-primary-deep);
    font-size: .64rem;
    font-weight: var(--snd-font-medium);
    line-height: 1.1;
}

.stock-filter-live > span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--snd-success);
    box-shadow: 0 0 0 3px rgba(31, 122, 61, .12);
}

.stock-filter-grid {
    display: grid;
    grid-template-columns: minmax(190px, 1.1fr) minmax(160px, .8fr) minmax(220px, 1.1fr) auto;
    gap: 12px;
    align-items: end;
}

.stock-filter-field { min-width: 0; }
.stock-filter-field label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 6px;
    color: var(--snd-ink-soft);
    font-size: .62rem;
    font-weight: var(--snd-font-semibold);
    letter-spacing: .06em;
    line-height: 1.2;
    text-transform: uppercase;
}

.stock-filter-field label .snd-icon { width: 13px; height: 13px; color: var(--snd-primary-deep); }
.stock-filter-field .form-control { width: 100%; height: 38px; min-height: 38px; }

.stock-search-control { position: relative; }
.stock-search-control .form-control { padding-right: 36px; }
.stock-search-clear {
    position: absolute;
    top: 50%;
    right: 7px;
    display: none;
    width: 25px;
    height: 25px;
    padding: 0;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: var(--snd-muted);
    cursor: pointer;
    transform: translateY(-50%);
}

.stock-search-clear:hover { background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.stock-search-clear .snd-icon { width: 14px; height: 14px; }
.stock-filter-action { display: flex; align-items: flex-end; }
.stock-filter-action .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; min-height: 38px; padding-inline: 12px; }
.stock-filter-action .snd-icon { width: 14px; height: 14px; }

.stock-table-card { overflow: hidden; }
.stock-table-header { min-height: 70px; padding: 14px 18px !important; background: rgba(255, 255, 255, .36) !important; }
.stock-table-title-wrap { gap: 10px; }
.stock-table-title-icon {
    width: 36px;
    height: 36px;
    border-radius: 11px;
}

.stock-table-title-icon .snd-icon { width: 17px; height: 17px; }
.stock-table-scroll {
    max-height: min(66vh, 640px);
    overflow: auto;
    scrollbar-color: rgba(151, 134, 238, .42) transparent;
}

.stock-table-scroll::-webkit-scrollbar,
.stock-ledger-table-scroll::-webkit-scrollbar { width: 9px; height: 9px; }
.stock-table-scroll::-webkit-scrollbar-thumb,
.stock-ledger-table-scroll::-webkit-scrollbar-thumb { border: 2px solid transparent; border-radius: 999px; background: rgba(151, 134, 238, .4); background-clip: padding-box; }

#stock-summary-table { min-width: 1090px; }
#stock-summary-table thead th { background: rgba(247, 248, 252, .9); }
#stock-summary-table tbody td { padding-block: 12px; }
#stock-summary-table tbody tr { transition: background-color .16s ease; }
#stock-summary-table tbody tr:hover { background: var(--snd-primary-wash); }
.stock-index-column { width: 60px; }
.stock-item-column { min-width: 230px; }
.stock-category-column { width: 145px; }
.stock-quantity-column { width: 138px; }
.stock-branch-column { min-width: 235px; }
.stock-rate-column { width: 130px; }
.stock-value-column { width: 148px; }
.stock-ledger-column { width: 74px; }

.stock-table-feedback {
    display: grid;
    justify-items: center;
    gap: 6px;
    min-height: 210px;
    padding: 36px 20px;
    color: var(--snd-muted);
    font-size: .72rem;
    text-align: center;
}

.stock-table-feedback .snd-icon { width: 25px; height: 25px; color: var(--snd-primary-deep); }
.stock-table-feedback strong { color: var(--snd-ink); font-size: .8rem; font-weight: var(--snd-font-semibold); }
.stock-table-feedback span { max-width: 300px; line-height: 1.45; }

.item-clickable-name {
    display: inline-block;
    color: var(--snd-primary-deep);
    font-weight: var(--snd-font-semibold);
    line-height: 1.25;
    text-decoration: none;
}

.item-clickable-name:hover { color: var(--snd-primary-hover); }
.stock-item-code {
    display: inline-flex;
    margin-top: 4px;
    padding: 2px 6px;
    border: 1px solid var(--snd-border);
    border-radius: 6px;
    background: rgba(248, 249, 253, .8);
    color: var(--snd-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: .6rem;
    line-height: 1.1;
}

.stock-category-chip,
.branch-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid var(--snd-border);
    border-radius: 999px;
    background: rgba(248, 249, 253, .75);
    color: var(--snd-ink-soft);
    font-size: .63rem;
    font-weight: var(--snd-font-medium);
    line-height: 1.1;
}

.stock-category-chip { padding: 4px 8px; }
.branch-pill { margin: 2px 4px 2px 0; padding: 4px 8px; }
.branch-pill.has-stock { border-color: rgba(31, 122, 61, .18); background: var(--snd-success-soft); color: var(--snd-success); }
.branch-pill.zero-stock { opacity: .68; }
.branch-pill.is-selected { border-color: var(--snd-primary) !important; background: var(--snd-primary-soft) !important; color: var(--snd-primary-deep) !important; }

.stock-quantity {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 61px;
    padding: 5px 9px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: var(--snd-font-semibold);
    line-height: 1;
}

.stock-quantity.is-healthy { border-color: rgba(31, 122, 61, .18); background: var(--snd-success-soft); color: var(--snd-success); }
.stock-quantity.is-low { border-color: rgba(180, 83, 9, .18); background: var(--snd-warning-soft); color: var(--snd-warning); }
.stock-quantity.is-empty { border-color: rgba(201, 52, 45, .18); background: var(--snd-danger-soft); color: var(--snd-danger); }
.stock-rate { color: var(--snd-muted); font-size: .7rem; font-weight: var(--snd-font-medium); }
.stock-value { color: var(--snd-ink); font-size: .72rem; font-weight: var(--snd-font-semibold); }

.stock-ledger-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    min-width: 32px;
    height: 32px;
    min-height: 32px;
    padding: 0;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: var(--snd-primary-deep);
    cursor: pointer;
}

.stock-ledger-trigger:hover { background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.stock-ledger-trigger .snd-icon { width: 14px; height: 14px; }

.stock-ledger-dialog { max-width: min(1220px, 94vw); }
.stock-ledger-modal-content { border: 1px solid var(--snd-border) !important; }
.stock-ledger-modal-header { padding: 16px 18px !important; background: rgba(255, 255, 255, .65) !important; }
.stock-ledger-heading { gap: 11px; }
.stock-ledger-heading-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
}

.stock-ledger-heading-icon .snd-icon { width: 18px; height: 18px; }
.stock-ledger-heading .modal-title { color: var(--snd-ink) !important; font-size: .9rem !important; }
.stock-ledger-heading p { margin: 3px 0 0; color: var(--snd-muted); font-size: .68rem; }
.stock-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 31px;
    height: 31px;
    padding: 0;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: var(--snd-muted);
    cursor: pointer;
}

.stock-modal-close:hover { background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.stock-modal-close .snd-icon { width: 17px; height: 17px; }
.stock-ledger-table-scroll { max-height: calc(82vh - 150px); overflow: auto; }
.stock-ledger-table { min-width: 1040px; }
.stock-ledger-table thead th { position: sticky; top: 0; z-index: 2; background: rgba(247, 248, 252, .96); }
.stock-ledger-table tbody td { white-space: nowrap; }
.voucher-type-red { color: var(--snd-danger); font-weight: var(--snd-font-semibold); }
.stock-ledger-balance { color: var(--snd-primary-deep); font-weight: var(--snd-font-semibold); }
.stock-ledger-modal-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.stock-ledger-modal-footer > span { color: var(--snd-muted); font-size: .68rem; font-weight: var(--snd-font-medium); }

@media (max-width: 1199.98px) {
    .stock-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .stock-filter-action { grid-column: span 2; }
    .stock-filter-action .btn { width: auto; }
}

@media (max-width: 767.98px) {
    .stock-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .stock-kpi-card { min-height: 116px; padding: 14px; }
    .stock-kpi-help { display: none; }
    .stock-filter-card-body { padding: 14px !important; }
    .stock-filter-heading,
    .stock-table-header { align-items: flex-start; flex-direction: column; }
    .stock-filter-live,
    .stock-table-status { align-self: flex-start; }
    .stock-filter-grid { grid-template-columns: 1fr; gap: 12px; }
    .stock-filter-action { grid-column: auto; }
    .stock-filter-action .btn { width: 100%; }
    .stock-table-header { padding: 14px !important; }
    .stock-table-scroll { max-height: none; }
}

@media (max-width: 420px) {
    .stock-kpi-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('js')
<script type="module">
jQuery(document).ready(function ($) {
    const currency = "{{ config('settings.currency_symbol', 'Rs.') }}";
    let stockData = [];

    // Format numbers with commas
    function formatNumber(num, decimals = 2) {
        const n = parseFloat(num) || 0;
        return n.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    // Load Stock Summary Data via AJAX
    function loadStockData() {
        const branchId = $('#filter-branch').val();
        const categoryId = $('#filter-category').val();
        const search = $('#filter-search').val().trim();

        $('#stock-table-body').html(`
            <tr class="snd-empty-row">
                <td colspan="8" class="snd-table-empty-cell">
                    <div class="stock-table-feedback">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <strong>Loading inventory</strong>
                        <span>Fetching the latest stock balances.</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: "{{ route('stocks.data') }}",
            type: "GET",
            data: {
                branch_id: branchId,
                category_id: categoryId,
                search: search
            },
            dataType: "json",
            success: function (res) {
                stockData = res.data || [];
                const stats = res.stats || {};

                // Update KPI Cards
                $('#kpi-total-items').text(formatNumber(stats.total_items, 0));
                $('#kpi-total-qty').text(formatNumber(stats.total_qty, 2));
                $('#kpi-total-valuation').text(currency + formatNumber(stats.total_valuation, 2));
                $('#kpi-low-stock').text(formatNumber(stats.low_stock_count, 0) + ' items');

                // Update filter status text
                const branchText = $('#filter-branch option:selected').text();
                $('#filter-status-badge').text('Viewing: ' + branchText + ' (' + stockData.length + ' items)');

                renderTable(stockData, branchId);
            },
            error: function () {
                $('#stock-table-body').html(`
                    <tr class="snd-empty-row">
                        <td colspan="8" class="snd-table-empty-cell">
                            <div class="stock-table-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Inventory could not load</strong>
                                <span>Please use Refresh to try again.</span>
                            </div>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Render Table Rows (1 row per item!)
    function renderTable(items, selectedBranchId) {
        if (!items || items.length === 0) {
            $('#stock-table-body').html(`
                <tr class="snd-empty-row">
                    <td colspan="8" class="snd-table-empty-cell">
                        <div class="stock-table-feedback">
                            <i class="fas fa-box-open"></i>
                            <strong>No stock items found</strong>
                            <span>Try clearing your search or choosing another branch.</span>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        items.forEach(function (item, idx) {
            const stockQty = parseFloat(item.in_stock_qty) || 0;
            const stockStateClass = stockQty > 5 ? 'is-healthy' : (stockQty > 0 ? 'is-low' : 'is-empty');

            // Render branch breakdown badges
            let branchBadgesHtml = '';
            if (Array.isArray(item.branch_stocks)) {
                item.branch_stocks.forEach(function (bs) {
                    const bQty = parseFloat(bs.qty) || 0;
                    const isSelected = selectedBranchId && String(bs.branch_id) === String(selectedBranchId);
                    const pillClass = bQty > 0 ? 'branch-pill has-stock' : 'branch-pill zero-stock';
                    const selectedClass = isSelected ? ' is-selected' : '';

                    branchBadgesHtml += `
                        <span class="${pillClass}${selectedClass}" title="${bs.branch_name}">
                            <span>${bs.branch_name}:</span>
                            <strong>${formatNumber(bQty, 1)}</strong>
                        </span>
                    `;
                });
            }

            html += `
                <tr>
                    <td class="text-center text-muted font-weight-bold">${idx + 1}</td>
                    <td>
                        <a href="javascript:void(0)" class="item-clickable-name view-ledger-trigger" data-id="${item.id}" data-name="${item.name}" data-barcode="${item.barcode}" data-category="${item.category_name}">
                            ${item.name}
                        </a>
                        <div>
                            ${item.barcode && item.barcode !== '—' ? `<span class="stock-item-code">${item.barcode}</span>` : ''}
                        </div>
                    </td>
                    <td>
                        <span class="stock-category-chip">${item.category_name}</span>
                    </td>
                    <td class="text-right">
                        <span class="stock-quantity ${stockStateClass}">
                            ${formatNumber(stockQty, 2)}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap align-items-center">
                            ${branchBadgesHtml}
                        </div>
                    </td>
                    <td class="text-right">
                        <span class="stock-rate">${currency}${formatNumber(item.cost_rate, 2)}</span>
                    </td>
                    <td class="text-right">
                        <span class="stock-value">${currency}${formatNumber(item.valuation, 2)}</span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="stock-ledger-trigger snd-table-action view-ledger-trigger" data-id="${item.id}" data-name="${item.name}" data-barcode="${item.barcode}" data-category="${item.category_name}" title="View stock ledger" aria-label="View stock ledger for ${item.name}">
                            <x-snd-icon name="eye" />
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#stock-table-body').html(html);
    }

    // Open Stock Ledger Modal (Matching Image 2 Reference)
    $(document).on('click', '.view-ledger-trigger', function () {
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const productBarcode = $(this).data('barcode');
        const productCategory = $(this).data('category');
        const branchId = $('#filter-branch').val();

        $('#ledger-item-name').text(productName);
        $('#ledger-item-meta').text('Category: ' + productCategory + (productBarcode && productBarcode !== '—' ? ' • Barcode: ' + productBarcode : ''));
        $('#ledger-table-body').html(`
            <tr class="snd-empty-row">
                <td colspan="11" class="snd-table-empty-cell">
                    <div class="stock-table-feedback">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <strong>Loading ledger</strong>
                        <span>Preparing the movement history.</span>
                    </div>
                </td>
            </tr>
        `);

        $('#itemLedgerModal').modal('show');

        $.ajax({
            url: `/admin/stocks/${productId}/ledger`,
            type: "GET",
            data: {
                branch_id: branchId
            },
            dataType: "json",
            success: function (res) {
                const records = res.records || [];
                $('#ledger-summary-count').text(records.length + ' ledger transactions found');

                if (records.length === 0) {
                    $('#ledger-table-body').html(`
                        <tr class="snd-empty-row">
                            <td colspan="11" class="snd-table-empty-cell">
                                <div class="stock-table-feedback">
                                    <i class="fas fa-file-invoice"></i>
                                    <strong>No ledger records found</strong>
                                    <span>This product has no recorded purchase, sale, or transfer vouchers.</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                let rowsHtml = '';
                records.forEach(function (r) {
                    rowsHtml += `
                        <tr>
                            <td class="font-weight-bold text-dark">${r.voucher}</td>
                            <td>
                                <span class="voucher-type-red">${r.voucher_type}</span>
                            </td>
                            <td>${r.voucher_date}</td>
                            <td>${r.warehouse || ''}</td>
                            <td class="text-right font-weight-bold">${r.qty}</td>
                            <td class="text-right">${r.rate}</td>
                            <td class="text-right">${r.amount}</td>
                            <td class="text-right stock-ledger-balance">${r.balance_qty}</td>
                            <td class="text-right font-weight-bold">${r.balance_amount}</td>
                            <td class="text-right">${r.average_rate}</td>
                            <td class="text-right pr-4 font-weight-bold text-dark">${r.running_amount}</td>
                        </tr>
                    `;
                });

                $('#ledger-table-body').html(rowsHtml);
            },
            error: function () {
                $('#ledger-table-body').html(`
                    <tr class="snd-empty-row">
                        <td colspan="11" class="snd-table-empty-cell">
                            <div class="stock-table-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Ledger could not load</strong>
                                <span>Please close this window and try again.</span>
                            </div>
                        </td>
                    </tr>
                `);
            }
        });
    });

    // Event Handlers for Filters
    $('#filter-branch').on('change', function () {
        loadStockData();
    });

    $('#filter-category').on('change', function () {
        loadStockData();
    });

    let searchTimer = null;
    $('#filter-search').on('input', function () {
        const val = $(this).val();
        if (val) {
            $('#search-clear-btn').show();
        } else {
            $('#search-clear-btn').hide();
        }

        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadStockData();
        }, 300);
    });

    $('#search-clear-btn').on('click', function () {
        $('#filter-search').val('');
        $(this).hide();
        loadStockData();
    });

    $('#btn-refresh').on('click', function () {
        loadStockData();
    });

    // Initial Load
    loadStockData();
});
</script>
@endsection
