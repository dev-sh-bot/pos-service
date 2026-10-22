@extends('layouts.admin')

@section('title', 'Opening Inventory Vouchers')
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('Opening Inventory Vouchers'))
@section('content-subtitle', __('Starting inventory balances and valuation ledger entries'))
@section('content-actions')
    <button type="button" id="btn-refresh" class="btn btn-secondary">
        <i class="fas fa-sync-alt mr-1"></i> {{ __('Refresh') }}
    </button>
    <a href="{{ route('stocks.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-layer-group mr-1"></i> {{ __('Stock Report') }}
    </a>
    <a href="{{ route('opening-stocks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> {{ __('New Opening Inventory') }}
    </a>
@endsection

@section('content')
<div class="snd-page opening-stock-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm opening-stock-alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <section class="opening-stock-kpi-grid" aria-label="{{ __('Opening inventory summary') }}">
        <article class="opening-stock-kpi opening-stock-kpi--success">
            <span class="opening-stock-kpi-label">{{ __('Total vouchers') }}</span>
            <strong class="opening-stock-kpi-value" id="kpi-total-vouchers">{{ number_format($totalVouchers) }}</strong>
            <span class="opening-stock-kpi-help">{{ __('Opening balance documents') }}</span>
            <span class="opening-stock-kpi-icon"><x-snd-icon name="file-text" /></span>
        </article>
        <article class="opening-stock-kpi opening-stock-kpi--primary">
            <span class="opening-stock-kpi-label">{{ __('Total item entries') }}</span>
            <strong class="opening-stock-kpi-value" id="kpi-total-items">{{ number_format($totalItems) }}</strong>
            <span class="opening-stock-kpi-help">{{ __('Products added to opening stock') }}</span>
            <span class="opening-stock-kpi-icon"><x-snd-icon name="list" /></span>
        </article>
        <article class="opening-stock-kpi opening-stock-kpi--primary">
            <span class="opening-stock-kpi-label">{{ __('Total opening quantity') }}</span>
            <strong class="opening-stock-kpi-value" id="kpi-total-qty">{{ number_format($totalQty, 2) }}</strong>
            <span class="opening-stock-kpi-help">{{ __('Units recorded across branches') }}</span>
            <span class="opening-stock-kpi-icon"><x-snd-icon name="package" /></span>
        </article>
        <article class="opening-stock-kpi opening-stock-kpi--warning">
            <span class="opening-stock-kpi-label">{{ __('Opening valuation') }}</span>
            <strong class="opening-stock-kpi-value" id="kpi-total-amount">{{ config('settings.currency_symbol', 'Rs.') }}{{ number_format($totalAmount, 2) }}</strong>
            <span class="opening-stock-kpi-help">{{ __('Value at the opening cost rate') }}</span>
            <span class="opening-stock-kpi-icon"><x-snd-icon name="wallet" /></span>
        </article>
    </section>

    <section class="card opening-stock-filter-card" aria-labelledby="opening-stock-filter-title">
        <div class="card-body">
            <div class="opening-stock-section-heading">
                <span class="opening-stock-heading-icon"><x-snd-icon name="sliders" /></span>
                <div>
                    <h2 id="opening-stock-filter-title">{{ __('Find opening stock') }}</h2>
                    <p>{{ __('Filter voucher history by item, branch, or date range.') }}</p>
                </div>
                <span class="opening-stock-live"><span></span>{{ __('Live results') }}</span>
            </div>

            <div class="opening-stock-filter-grid">
                <div class="opening-stock-field">
                    <label for="filter-search"><x-snd-icon name="search" /> {{ __('Voucher or item') }}</label>
                    <input type="search" id="filter-search" class="form-control" placeholder="{{ __('Voucher #, item name, or barcode…') }}" autocomplete="off">
                </div>
                <div class="opening-stock-field">
                    <label for="filter-branch"><x-snd-icon name="git-branch" /> {{ __('Branch') }}</label>
                    <select id="filter-branch" class="form-control">
                        <option value="all">{{ __('All branches') }}</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="opening-stock-field">
                    <label for="filter-product"><x-snd-icon name="package" /> Product</label>
                    <select id="filter-product" class="form-control">
                        <option value="">{{ __('All products') }}</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="opening-stock-field">
                    <label for="filter-date-from"><x-snd-icon name="calendar-days" /> {{ __('Date from') }}</label>
                    <input type="date" id="filter-date-from" class="form-control">
                </div>
                <div class="opening-stock-field">
                    <label for="filter-date-to"><x-snd-icon name="calendar-days" /> {{ __('Date to') }}</label>
                    <div class="opening-stock-date-actions">
                        <input type="date" id="filter-date-to" class="form-control">
                        <button type="button" id="btn-reset-filters" class="btn btn-secondary" title="{{ __('Reset filters') }}" aria-label="{{ __('Reset filters') }}">
                            <x-snd-icon name="refresh" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card opening-stock-history-card" aria-labelledby="opening-stock-history-title">
        <div class="card-header opening-stock-history-header">
            <div class="opening-stock-section-heading opening-stock-history-heading">
                <span class="opening-stock-heading-icon"><x-snd-icon name="table" /></span>
                <div>
                    <h2 id="opening-stock-history-title">{{ __('Opening stock records') }}</h2>
                    <p>{{ __('Recorded starting balances and their cost valuation') }}</p>
                </div>
                <span id="loading-spinner" class="opening-stock-loading d-none" aria-label="{{ __('Loading') }}">
                    <x-snd-icon name="loader-circle" class="fa-spin" />
                </span>
            </div>
            <span class="opening-stock-count" id="pagination-info">{{ __('Loading opening inventory data…') }}</span>
        </div>

        <div class="table-responsive opening-stock-table-wrap">
            <table class="table opening-stock-table mb-0" id="opening-stocks-table">
                <thead>
                    <tr>
                        <th scope="col" class="text-center">#</th>
                        <th scope="col">{{ __('Voucher no.') }}</th>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('Item / product') }}</th>
                        <th scope="col">{{ __('Branch') }}</th>
                        <th scope="col" class="text-right">{{ __('Closing qty') }}</th>
                        <th scope="col" class="text-right">{{ __('Rate') }}</th>
                        <th scope="col" class="text-right">{{ __('Closing amount') }}</th>
                        <th scope="col">{{ __('Notes') }}</th>
                        <th scope="col" class="text-center">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody id="opening-stocks-body">
                    <tr class="snd-empty-row">
                        <td colspan="10" class="snd-table-empty-cell">
                            <div class="opening-stock-feedback opening-stock-feedback--loading">
                                <x-snd-icon name="loader-circle" class="fa-spin" />
                                <strong>{{ __('Loading opening inventory') }}</strong>
                                <span>{{ __('Fetching voucher history and totals.') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer opening-stock-pagination snd-pagination-inline" id="pagination-footer">
            <div class="small text-muted" id="pagination-summary"></div>
            <div id="pagination-controls"></div>
        </div>
    </section>
</div>
@endsection

@section('css')
<style>
.opening-stock-page { display: grid; gap: 16px; }
.opening-stock-page > .card { margin: 0; }
.opening-stock-alert { margin: 0; border-radius: var(--snd-radius-md); }

.opening-stock-kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.opening-stock-kpi {
    --opening-stock-accent: var(--snd-primary-deep);
    position: relative; display: grid; grid-template-columns: minmax(0, 1fr) auto; grid-template-rows: auto auto auto;
    min-height: 122px; overflow: hidden; padding: 17px 18px; border: 1px solid var(--snd-border);
    border-radius: var(--snd-radius-lg); background: var(--snd-surface); box-shadow: var(--snd-shadow);
}
.opening-stock-kpi::before { position: absolute; top: 16px; bottom: 16px; left: 0; width: 3px; border-radius: 0 4px 4px 0; background: var(--opening-stock-accent); content: ''; }
.opening-stock-kpi--success { --opening-stock-accent: var(--snd-success); }
.opening-stock-kpi--warning { --opening-stock-accent: var(--snd-warning); }
.opening-stock-kpi-label { grid-column: 1; color: var(--snd-muted); font-size: .62rem; font-weight: var(--snd-font-semibold); letter-spacing: .08em; line-height: 1.25; text-transform: uppercase; }
.opening-stock-kpi-value { grid-column: 1 / -1; margin-top: 4px; overflow-wrap: anywhere; color: var(--opening-stock-accent); font-size: clamp(1.12rem, 1.8vw, 1.45rem); font-weight: var(--snd-font-semibold); letter-spacing: -.035em; line-height: 1.15; }
.opening-stock-kpi-help { grid-column: 1 / -1; align-self: end; margin-top: 7px; color: var(--snd-muted); font-size: .66rem; line-height: 1.3; }
.opening-stock-kpi-icon { grid-column: 2; grid-row: 1 / span 2; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 13px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); }
.opening-stock-kpi-icon .snd-icon { width: 19px; height: 19px; }
.opening-stock-kpi--success .opening-stock-kpi-icon { background: var(--snd-success-soft); color: var(--snd-success); }
.opening-stock-kpi--warning .opening-stock-kpi-icon { background: var(--snd-warning-soft); color: var(--snd-warning); }

.opening-stock-filter-card .card-body { padding: 17px 18px 18px !important; }
.opening-stock-section-heading { display: flex; align-items: center; gap: 10px; min-width: 0; }
.opening-stock-heading-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 34px; width: 34px; height: 34px; border-radius: 10px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); }
.opening-stock-heading-icon .snd-icon { width: 16px; height: 16px; }
.opening-stock-section-heading h2 { margin: 0; color: var(--snd-ink); font-size: .84rem; font-weight: var(--snd-font-semibold); line-height: 1.2; }
.opening-stock-section-heading p { margin: 3px 0 0; color: var(--snd-muted); font-size: .67rem; line-height: 1.35; }
.opening-stock-filter-card .opening-stock-section-heading { margin-bottom: 15px; }
.opening-stock-live { display: inline-flex; align-items: center; gap: 7px; margin-left: auto; padding: 6px 10px; border: 1px solid rgba(151,134,238,.2); border-radius: 999px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); font-size: .64rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.opening-stock-live > span { width: 6px; height: 6px; border-radius: 50%; background: var(--snd-success); box-shadow: 0 0 0 3px rgba(31,122,61,.12); }
.opening-stock-filter-grid { display: grid; grid-template-columns: minmax(210px, 1.18fr) minmax(150px, .82fr) minmax(190px, 1.08fr) minmax(145px, .78fr) minmax(180px, .92fr); align-items: end; gap: 12px; }
.opening-stock-field { min-width: 0; }
.opening-stock-field label { display: flex; align-items: center; gap: 6px; margin: 0 0 6px; color: var(--snd-ink-soft); font-size: .64rem; font-weight: var(--snd-font-semibold); letter-spacing: .04em; }
.opening-stock-field label .snd-icon { width: 14px; height: 14px; color: var(--snd-primary-deep); }
.opening-stock-field .form-control { width: 100%; height: 40px; min-height: 40px; padding: 0 11px; border-radius: var(--snd-radius-sm); font-size: .7rem; }
.opening-stock-date-actions { display: grid; grid-template-columns: minmax(0, 1fr) 40px; gap: 7px; }
.opening-stock-date-actions .btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; min-height: 40px; padding: 0 !important; margin: 0; }
.opening-stock-date-actions .snd-icon { width: 15px; height: 15px; }

.opening-stock-history-card { overflow: hidden; }
.opening-stock-history-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 13px 18px !important; }
.opening-stock-history-heading { gap: 9px; }
.opening-stock-history-heading .opening-stock-heading-icon { flex-basis: 32px; width: 32px; height: 32px; border-radius: 9px; }
.opening-stock-loading { display: inline-flex; align-items: center; color: var(--snd-primary-deep); }
.opening-stock-count { flex: 0 0 auto; padding: 6px 10px; border: 1px solid var(--snd-border); border-radius: 999px; background: var(--snd-surface-soft); color: var(--snd-muted); font-size: .65rem; font-weight: var(--snd-font-medium); line-height: 1.2; }
.opening-stock-table-wrap { width: 100%; }
.opening-stock-table { min-width: 1060px; }
.opening-stock-table thead th { padding: 11px 12px; }
.opening-stock-table tbody td { padding: 12px; color: var(--snd-ink-soft); font-size: .72rem; }
.opening-stock-table tbody tr:hover { background: var(--snd-primary-wash); }
.opening-stock-table .opening-stock-voucher { display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; border: 1px solid rgba(151,134,238,.2); border-radius: 8px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .67rem; font-weight: var(--snd-font-semibold); white-space: nowrap; }
.opening-stock-table .opening-stock-branch { display: inline-flex; align-items: center; gap: 5px; max-width: 100%; padding: 5px 8px; border: 1px solid var(--snd-border); border-radius: 8px; background: rgba(255,255,255,.65); color: var(--snd-ink-soft); font-size: .66rem; font-weight: var(--snd-font-medium); }
.opening-stock-table .opening-stock-branch .snd-icon { width: 13px; height: 13px; color: var(--snd-primary-deep); }
.opening-stock-table .opening-stock-quantity { color: var(--snd-success); font-weight: var(--snd-font-semibold); white-space: nowrap; }
.opening-stock-table .opening-stock-amount { color: var(--snd-ink); font-weight: var(--snd-font-semibold); white-space: nowrap; }
.opening-stock-empty-cell { padding: 0 !important; }
.opening-stock-feedback { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; min-height: 270px; padding: 28px 20px; color: var(--snd-muted); text-align: center; }
.opening-stock-feedback > .snd-icon { width: 27px; height: 27px; margin-bottom: 3px; color: var(--snd-primary-deep); }
.opening-stock-feedback strong { color: var(--snd-ink-soft); font-size: .85rem; font-weight: var(--snd-font-semibold); }
.opening-stock-feedback > span { max-width: 380px; color: var(--snd-muted); font-size: .68rem; line-height: 1.5; }
.opening-stock-feedback--error > .snd-icon { color: var(--snd-danger); }
.opening-stock-feedback--error > i { margin-bottom: 3px; color: var(--snd-danger); font-size: 1.45rem; }
.opening-stock-empty-icon { display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; margin-bottom: 2px; border-radius: 16px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.opening-stock-empty-icon .snd-icon { width: 23px; height: 23px; }
.opening-stock-empty-icon > i { font-size: 1.2rem; }
.opening-stock-empty-kicker { color: var(--snd-primary-deep) !important; font-size: .61rem !important; font-weight: var(--snd-font-semibold); letter-spacing: .1em; text-transform: uppercase; }
.opening-stock-feedback h3 { margin: 0; color: var(--snd-ink); font-size: .92rem; font-weight: var(--snd-font-semibold); }
.opening-stock-feedback .opening-stock-empty-cta { display: inline-flex; align-items: center; justify-content: center; gap: 7px; width: auto !important; min-width: 0 !important; min-height: 38px; margin-top: 5px; padding: 0 14px !important; border-radius: 10px !important; white-space: nowrap; }
.opening-stock-feedback .opening-stock-empty-cta .snd-icon { width: 15px; height: 15px; }
.opening-stock-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 48px; }
.opening-stock-pagination:empty { display: none; }
.opening-stock-pagination .pagination { margin: 0; }

@media (max-width: 1199.98px) {
    .opening-stock-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 991.98px) {
    .opening-stock-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .opening-stock-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .opening-stock-kpi-grid, .opening-stock-filter-grid { grid-template-columns: minmax(0, 1fr); }
    .opening-stock-kpi { min-height: 112px; }
    .opening-stock-history-header { align-items: flex-start; flex-direction: column; }
    .opening-stock-count { margin-left: 43px; }
    .opening-stock-live { padding: 5px 8px; font-size: .59rem; }
    .opening-stock-pagination { align-items: flex-start; flex-direction: column; }
}
</style>
@endsection

@section('js')
<script type="module">
jQuery(document).ready(function ($) {
    let currentPage = 1;
    let searchTimeout = null;

    // Load Opening Stock Data via AJAX
    function loadData(page = 1) {
        currentPage = page;
        $('#loading-spinner').removeClass('d-none');

        const params = {
            page: page,
            branch_id: $('#filter-branch').val(),
            item_id: $('#filter-product').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            search: $('#filter-search').val().trim(),
        };

        $.ajax({
            url: "{{ route('opening-stocks.data') }}",
            type: "GET",
            data: params,
            dataType: "json",
            success: function (res) {
                $('#loading-spinner').addClass('d-none');
                if (res.success) {
                    renderTable(res.rows, res.pagination);
                    updateKPI(res.kpi);
                    renderPagination(res.pagination);
                }
            },
            error: function () {
                $('#loading-spinner').addClass('d-none');
                $('#opening-stocks-body').html(`
                    <tr class="snd-empty-row">
                        <td colspan="10" class="snd-table-empty-cell opening-stock-empty-cell">
                            <div class="opening-stock-feedback opening-stock-feedback--error">
                                <i class="fas fa-triangle-exclamation"></i>
                                <strong>Could not load opening stock</strong>
                                <span>Please refresh to try loading the voucher history again.</span>
                            </div>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Render Table Rows
    function renderTable(rows, pagination) {
        const $tbody = $('#opening-stocks-body');
        $tbody.empty();

        if (!rows || rows.length === 0) {
            $tbody.html(`
                <tr class="snd-empty-row opening-stock-empty-row">
                    <td colspan="10" class="snd-table-empty-cell opening-stock-empty-cell">
                        <div class="opening-stock-feedback opening-stock-empty-state">
                            <span class="opening-stock-empty-icon"><i class="fas fa-boxes-stacked"></i></span>
                            <span class="opening-stock-empty-kicker">Inventory setup</span>
                            <h3>No opening inventory entries found</h3>
                            <span>No records match the selected filters. Add an opening balance to get started.</span>
                            <a href="{{ route('opening-stocks.create') }}" class="btn btn-primary opening-stock-empty-cta">
                                <i class="fas fa-plus"></i> Add opening inventory
                            </a>
                        </div>
                    </td>
                </tr>
            `);
            $('#pagination-info').text('Showing 0 of 0 entries');
            $('#pagination-summary').text('');
            return;
        }

        $('#pagination-info').text(`Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total} entries`);
        $('#pagination-summary').text(`Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`);

        rows.forEach(function (row) {
            const barcodeHtml = row.product_code ? `<code>${row.product_code}</code>` : '';
            const categoryHtml = row.category_name ? `<span class="badge badge-light border mr-1">${row.category_name}</span>` : '';

            const tr = `
                <tr class="opening-stock-row">
                    <td class="text-center text-muted font-weight-bold">${row.index}</td>
                    <td>
                        <a href="${row.show_url}" class="opening-stock-voucher">${row.voucher_no}</a>
                    </td>
                    <td>${row.voucher_date}</td>
                    <td>
                        <div class="font-weight-bold text-dark">${row.product_name}</div>
                        <div class="small text-muted">${categoryHtml}${barcodeHtml}</div>
                    </td>
                    <td>
                        <span class="opening-stock-branch">
                            <i class="fas fa-warehouse text-primary mr-1"></i> ${row.branch_name}
                        </span>
                    </td>
                    <td class="text-right opening-stock-quantity">
                        ${row.qty_formatted}
                    </td>
                    <td class="text-right text-muted font-weight-bold">
                        ${row.rate_formatted}
                    </td>
                    <td class="text-right opening-stock-amount">
                        ${row.amount_formatted}
                    </td>
                    <td class="small text-muted">
                        ${row.notes}
                    </td>
                    <td class="text-center">
                        <a href="${row.show_url}" class="btn snd-table-action snd-table-action--secondary" title="View voucher details" aria-label="View voucher details">
                            <x-snd-icon name="eye" />
                        </a>
                    </td>
                </tr>
            `;
            $tbody.append(tr);
        });
    }

    // Update KPI Card Numbers
    function updateKPI(kpi) {
        if (!kpi) return;
        $('#kpi-total-vouchers').text(Number(kpi.total_vouchers).toLocaleString());
        $('#kpi-total-items').text(Number(kpi.total_items).toLocaleString());
        $('#kpi-total-qty').text(kpi.total_qty);
        $('#kpi-total-amount').text(kpi.total_amount);
    }

    // Render Pagination Controls
    function renderPagination(pagination) {
        const $ctrls = $('#pagination-controls');
        $ctrls.empty();

        if (pagination.last_page <= 1) return;

        let html = '<ul class="pagination pagination-sm m-0">';

        // Prev button
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link page-nav-btn" href="#" data-page="${pagination.current_page - 1}">&laquo;</a></li>`;
        } else {
            html += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
        }

        // Numeric pages
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link page-nav-btn" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }

        for (let p = startPage; p <= endPage; p++) {
            if (p === pagination.current_page) {
                html += `<li class="page-item active"><span class="page-link font-weight-bold">${p}</span></li>`;
            } else {
                html += `<li class="page-item"><a class="page-link page-nav-btn" href="#" data-page="${p}">${p}</a></li>`;
            }
        }

        if (endPage < pagination.last_page) {
            if (endPage < pagination.last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link page-nav-btn" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item"><a class="page-link page-nav-btn" href="#" data-page="${pagination.current_page + 1}">&raquo;</a></li>`;
        } else {
            html += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
        }

        html += '</ul>';
        $ctrls.html(html);
    }

    // Event Handlers: Pagination click
    $(document).on('click', '.page-nav-btn', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            loadData(page);
        }
    });

    // Event Handlers: Filter changes
    $('#filter-branch, #filter-product, #filter-date-from, #filter-date-to').on('change', function () {
        loadData(1);
    });

    // Live search debounce
    $('#filter-search').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            loadData(1);
        }, 300);
    });

    // Reset Filters
    $('#btn-reset-filters').on('click', function () {
        $('#filter-search').val('');
        $('#filter-branch').val('all');
        $('#filter-product').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        loadData(1);
    });

    // Refresh button
    $('#btn-refresh').on('click', function () {
        loadData(currentPage);
    });

    // Initial Load
    loadData(1);
});
</script>
@endsection
