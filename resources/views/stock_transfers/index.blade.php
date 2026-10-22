@extends('layouts.admin')

@section('title', 'Branch Stock Transfers')
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('Branch Stock Transfers'))
@section('content-subtitle', __('Review branch-to-branch stock movement and transfer history.'))
@section('content-actions')
    <a href="{{ route('stocks.index') }}" class="btn btn-secondary">
        <x-snd-icon name="package" class="mr-1" /> {{ __('Stock Ledger') }}
    </a>
    <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
        <x-snd-icon name="plus" class="mr-1" /> {{ __('New Stock Transfer') }}
    </a>
@endsection

@section('content')
<div class="snd-page stock-transfer-page">
    <section class="card stock-transfer-filter-card" aria-labelledby="transfer-filter-title">
        <div class="card-body">
            <form action="{{ route('stock-transfers.index') }}" method="GET" class="stock-transfer-filter-form">
                <div class="stock-transfer-section-heading">
                    <span class="stock-transfer-heading-icon"><x-snd-icon name="filter" /></span>
                    <div>
                        <h2 id="transfer-filter-title">{{ __('Filter transfer history') }}</h2>
                        <p>{{ __('Narrow the list by branch, product, and transfer date.') }}</p>
                    </div>
                </div>

                <div class="stock-transfer-filter-grid">
                    <div class="stock-transfer-field">
                        <label for="transfer-branch"><x-snd-icon name="git-branch" /> {{ __('Branch') }}</label>
                        <select id="transfer-branch" name="branch_id" class="form-control">
                            <option value="">{{ __('All Branches') }}</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="stock-transfer-field">
                        <label for="transfer-product"><x-snd-icon name="package" /> {{ __('Products') }}</label>
                        <select id="transfer-product" name="item_id" class="form-control">
                            <option value="">{{ __('All Products') }}</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('item_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="stock-transfer-field">
                        <label for="transfer-date-from"><x-snd-icon name="calendar-days" /> {{ __('Date From') }}</label>
                        <input id="transfer-date-from" type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="stock-transfer-field">
                        <label for="transfer-date-to"><x-snd-icon name="calendar-days" /> {{ __('Date To') }}</label>
                        <input id="transfer-date-to" type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="stock-transfer-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="search" /> <span>{{ __('Apply filters') }}</span>
                        </button>
                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary" title="{{ __('Clear filters') }}" aria-label="{{ __('Clear filters') }}">
                            <x-snd-icon name="refresh" />
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card stock-transfer-history-card" aria-labelledby="transfer-history-title">
        <div class="card-header stock-transfer-history-header">
            <div class="stock-transfer-section-heading">
                <span class="stock-transfer-heading-icon"><x-snd-icon name="arrow-right-left" /></span>
                <div>
                    <h2 id="transfer-history-title">{{ __('Transfer history') }}</h2>
                    <p>{{ __('Stock movement between your branches') }}</p>
                </div>
            </div>
            <span class="stock-transfer-count">
                <x-snd-icon name="package" />
                {{ trans_choice(':count transfer|:count transfers', $transfers->total(), ['count' => number_format($transfers->total())]) }}
            </span>
        </div>

        <div class="table-responsive stock-transfer-table-wrap">
            <table class="table stock-transfer-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Voucher No') }}</th>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('From Branch') }}</th>
                        <th scope="col">{{ __('To Branch') }}</th>
                        <th scope="col">{{ __('Products') }}</th>
                        <th scope="col" class="text-right">{{ __('Quantity') }}</th>
                        <th scope="col" class="text-right">{{ __('Cost Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $trf)
                    <tr>
                        <td><span class="stock-transfer-voucher">{{ $trf->voucher_no }}</span></td>
                        <td class="stock-transfer-date">{{ $trf->voucher_date ? $trf->voucher_date->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($trf->fromBranch)
                                <span class="stock-transfer-branch"><x-snd-icon name="arrow-left" /> {{ $trf->fromBranch->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($trf->toBranch)
                                <span class="stock-transfer-branch"><x-snd-icon name="arrow-right-left" /> {{ $trf->toBranch->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($trf->product)
                                <div class="stock-transfer-product-name">{{ $trf->product->name }}</div>
                                <div class="stock-transfer-product-meta">
                                    <span>{{ __('Barcode') }} {{ $trf->product->barcode ?: '—' }}</span>
                                    <span>{{ __('Current stock') }} <strong>{{ number_format($trf->product->quantity, 2) }}</strong></span>
                                </div>
                            @else
                                <span class="text-muted">{{ __('Item #:id', ['id' => $trf->item_id]) }}</span>
                            @endif
                        </td>
                        <td class="text-right"><span class="stock-transfer-quantity">{{ number_format($trf->qty, 2) }}</span></td>
                        <td class="text-right stock-transfer-amount">{{ config('settings.currency_symbol') }}{{ number_format($trf->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr class="stock-transfer-empty-row">
                        <td colspan="7" class="stock-transfer-empty-cell">
                            <div class="stock-transfer-empty-state">
                                <span class="stock-transfer-empty-icon"><x-snd-icon name="arrow-right-left" /></span>
                                <span class="stock-transfer-empty-kicker">{{ __('Inventory movement') }}</span>
                                <h3>{{ __('No stock transfers yet') }}</h3>
                                <p>{{ __('Move products between branches to keep inventory balanced across locations.') }}</p>
                                <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
                                    <x-snd-icon name="plus" /> {{ __('Create your first transfer') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="card-footer stock-transfer-pagination snd-pagination-inline">
            <span>{{ __('Showing :first–:last of :total transfers', ['first' => $transfers->firstItem(), 'last' => $transfers->lastItem(), 'total' => number_format($transfers->total())]) }}</span>
            {{ $transfers->links() }}
        </div>
        @endif
    </section>
</div>
@endsection

@section('css')
<style>
.stock-transfer-page { display: grid; gap: 16px; }
.stock-transfer-page > .card { margin: 0; }
.stock-transfer-filter-card .card-body { padding: 18px !important; }
.stock-transfer-filter-form { display: grid; gap: 16px; }
.stock-transfer-section-heading { display: flex; align-items: center; gap: 11px; min-width: 0; }
.stock-transfer-heading-icon { width: 38px; height: 38px; flex: 0 0 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.stock-transfer-heading-icon .snd-icon { width: 18px; height: 18px; }
.stock-transfer-section-heading h2 { margin: 0; color: var(--snd-ink); font-size: .86rem; font-weight: var(--snd-font-semibold); }
.stock-transfer-section-heading p { margin: 4px 0 0; color: var(--snd-muted); font-size: .68rem; line-height: 1.4; }
.stock-transfer-filter-grid { display: grid; grid-template-columns: minmax(170px, 1.15fr) minmax(190px, 1.25fr) minmax(145px, .85fr) minmax(145px, .85fr) minmax(178px, auto); align-items: end; gap: 12px; }
.stock-transfer-field { min-width: 0; }
.stock-transfer-field label { display: flex; align-items: center; gap: 6px; margin: 0 0 6px; color: var(--snd-ink-soft); font-size: .68rem; font-weight: var(--snd-font-medium); }
.stock-transfer-field label .snd-icon { width: 14px; height: 14px; color: var(--snd-primary-deep); }
.stock-transfer-field .form-control { height: 40px; min-height: 40px; padding: 0 11px; }
.stock-transfer-filter-actions { display: grid; grid-template-columns: minmax(0, 1fr) 40px; gap: 7px; }
.stock-transfer-filter-actions .btn { height: 40px; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; margin: 0; white-space: nowrap; }
.stock-transfer-filter-actions .btn .snd-icon { width: 15px; height: 15px; }
.stock-transfer-filter-actions .btn-secondary { padding: 0 !important; }
.stock-transfer-history-card { overflow: hidden; }
.stock-transfer-history-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 18px !important; }
.stock-transfer-history-header .stock-transfer-heading-icon { width: 34px; height: 34px; flex-basis: 34px; border-radius: 10px; }
.stock-transfer-history-header .stock-transfer-heading-icon .snd-icon { width: 16px; height: 16px; }
.stock-transfer-count { display: inline-flex; align-items: center; gap: 7px; padding: 6px 10px; border: 1px solid rgba(151,134,238,.2); border-radius: 999px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .66rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.stock-transfer-count .snd-icon { width: 14px; height: 14px; }
.stock-transfer-table-wrap { border-radius: 0 0 var(--snd-radius-lg) var(--snd-radius-lg); }
.stock-transfer-table { min-width: 860px; }
.stock-transfer-table thead th { padding: 12px 14px; }
.stock-transfer-table tbody td { padding: 12px 14px; }
.stock-transfer-voucher { display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; border: 1px solid rgba(151,134,238,.2); border-radius: 8px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .67rem; font-weight: var(--snd-font-semibold); white-space: nowrap; }
.stock-transfer-date { color: var(--snd-ink-soft); white-space: nowrap; }
.stock-transfer-branch { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; padding: 5px 8px; border: 1px solid var(--snd-border); border-radius: 8px; background: rgba(255,255,255,.65); color: var(--snd-ink-soft); font-size: .67rem; font-weight: var(--snd-font-medium); }
.stock-transfer-branch .snd-icon { width: 13px; height: 13px; flex: 0 0 13px; color: var(--snd-primary-deep); }
.stock-transfer-product-name { color: var(--snd-ink); font-size: .72rem; font-weight: var(--snd-font-semibold); }
.stock-transfer-product-meta { display: flex; flex-wrap: wrap; gap: 4px 10px; margin-top: 4px; color: var(--snd-muted); font-size: .62rem; }
.stock-transfer-product-meta strong { color: var(--snd-ink-soft); font-weight: var(--snd-font-medium); }
.stock-transfer-quantity { display: inline-flex; justify-content: center; min-width: 60px; padding: 5px 8px; border: 1px solid rgba(151,134,238,.18); border-radius: 999px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .68rem; font-weight: var(--snd-font-semibold); }
.stock-transfer-amount { color: var(--snd-ink); font-weight: var(--snd-font-semibold); white-space: nowrap; }
.stock-transfer-table tbody tr.stock-transfer-empty-row:hover { background: transparent !important; }
.stock-transfer-table td.stock-transfer-empty-cell { padding: 0 !important; border-bottom: 0 !important; }
.stock-transfer-empty-state { min-height: 270px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 36px 20px; text-align: center; }
.stock-transfer-empty-icon { width: 58px; height: 58px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; border: 1px solid rgba(151,134,238,.19); border-radius: 18px; background: linear-gradient(145deg, var(--snd-primary-soft), rgba(255,255,255,.86)); color: var(--snd-primary-deep); box-shadow: 0 8px 20px rgba(151,134,238,.12); }
.stock-transfer-empty-icon .snd-icon { width: 25px; height: 25px; }
.stock-transfer-empty-kicker { color: var(--snd-primary-deep); font-size: .58rem; font-weight: var(--snd-font-semibold); letter-spacing: .13em; text-transform: uppercase; }
.stock-transfer-empty-state h3 { margin: 5px 0 0; color: var(--snd-ink); font-size: .95rem; font-weight: var(--snd-font-semibold); }
.stock-transfer-empty-state p { max-width: 390px; margin: 7px 0 17px; color: var(--snd-muted); font-size: .7rem; line-height: 1.55; }
.stock-transfer-empty-state .btn { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 13px !important; white-space: nowrap; }
.stock-transfer-empty-state .btn .snd-icon { width: 15px; height: 15px; }
.stock-transfer-pagination { display: flex; align-items: center; justify-content: space-between; gap: 14px; color: var(--snd-muted); font-size: .66rem; }
.stock-transfer-pagination .pagination { margin: 0; }

@media (max-width: 1199.98px) {
    .stock-transfer-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .stock-transfer-filter-actions { grid-column: 1 / -1; grid-template-columns: minmax(0, 1fr) 40px; }
}
@media (max-width: 575.98px) {
    .stock-transfer-filter-card .card-body { padding: 14px !important; }
    .stock-transfer-filter-grid { grid-template-columns: minmax(0, 1fr); gap: 11px; }
    .stock-transfer-filter-actions { grid-column: auto; }
    .stock-transfer-history-header { align-items: flex-start; flex-direction: column; }
    .stock-transfer-pagination { align-items: flex-start; flex-direction: column; }
    .stock-transfer-empty-state { min-height: 240px; padding-inline: 16px; }
}
</style>
@endsection
