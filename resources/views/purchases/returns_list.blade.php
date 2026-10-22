@extends('layouts.admin')

@section('title', 'Purchase Returns List')
@section('content-eyebrow', __('Purchases'))
@section('content-header', __('Purchase Returns'))
@section('content-subtitle', __('Review and filter recorded purchase returns.'))
@section('content-actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
        <x-snd-icon name="package" class="mr-1" /> {{ __('All Purchases') }}
    </a>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <x-snd-icon name="plus" class="mr-1" /> {{ __('New Purchase') }}
    </a>
@endsection

@section('content')
<div class="snd-page purchase-returns-page">
    <section class="card purchase-returns-filter-card" aria-labelledby="purchase-return-filter-title">
        <div class="card-body">
            <form action="{{ route('purchases.returns') }}" method="GET" class="purchase-returns-filter-form">
                <div class="purchase-returns-section-heading">
                    <span class="purchase-returns-heading-icon"><x-snd-icon name="filter" /></span>
                    <div>
                        <h2 id="purchase-return-filter-title">{{ __('Filter returns') }}</h2>
                        <p>{{ __('Find a return by supplier, date, or reference number.') }}</p>
                    </div>
                </div>

                <div class="purchase-returns-filter-grid">
                    <div class="purchase-returns-field">
                        <label for="return-supplier"><x-snd-icon name="truck" /> {{ __('Supplier') }}</label>
                        <select id="return-supplier" name="supplier_id" class="form-control">
                            <option value="">{{ __('All Suppliers') }}</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->first_name }} {{ $s->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="purchase-returns-field">
                        <label for="return-date-from"><x-snd-icon name="calendar-days" /> {{ __('Date From') }}</label>
                        <input id="return-date-from" type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="purchase-returns-field">
                        <label for="return-date-to"><x-snd-icon name="calendar-days" /> {{ __('Date To') }}</label>
                        <input id="return-date-to" type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="purchase-returns-field">
                        <label for="return-search"><x-snd-icon name="search" /> {{ __('Reference or return number') }}</label>
                        <input id="return-search" type="search" name="search" class="form-control" placeholder="{{ __('e.g. PRET-…') }}" value="{{ request('search') }}" autocomplete="off">
                    </div>

                    <div class="purchase-returns-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="search" /> <span>{{ __('Apply filters') }}</span>
                        </button>
                        <a href="{{ route('purchases.returns') }}" class="btn btn-secondary" title="{{ __('Clear filters') }}" aria-label="{{ __('Clear filters') }}">
                            <x-snd-icon name="refresh" />
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card purchase-returns-history-card" aria-labelledby="purchase-return-history-title">
        <div class="card-header purchase-returns-history-header">
            <div class="purchase-returns-section-heading">
                <span class="purchase-returns-heading-icon"><x-snd-icon name="receipt" /></span>
                <div>
                    <h2 id="purchase-return-history-title">{{ __('Return history') }}</h2>
                    <p>{{ __('Supplier refunds recorded against purchases') }}</p>
                </div>
            </div>
            <span class="purchase-returns-count">
                <x-snd-icon name="receipt" />
                {{ trans_choice(':count return|:count returns', $returns->total(), ['count' => number_format($returns->total())]) }}
            </span>
        </div>

        <div class="table-responsive purchase-returns-table-wrap">
            <table class="table purchase-returns-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ __('Return No') }}</th>
                        <th scope="col">{{ __('Date') }}</th>
                        <th scope="col">{{ __('Original Purchase') }}</th>
                        <th scope="col">{{ __('Supplier') }}</th>
                        <th scope="col">{{ __('Returned Items') }}</th>
                        <th scope="col" class="text-right">{{ __('Refund Total') }}</th>
                        <th scope="col" class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                    <tr>
                        <td><span class="purchase-return-voucher">{{ $ret->return_no }}</span></td>
                        <td class="purchase-return-date">{{ $ret->return_date ? $ret->return_date->format('d M, Y') : '—' }}</td>
                        <td>
                            @if($ret->purchase)
                                <a href="{{ route('purchases.show', $ret->purchase) }}" class="purchase-return-purchase-link">
                                    {{ $ret->purchase->purchase_no ?: ('#' . $ret->purchase->id) }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="purchase-return-supplier">{{ trim(($ret->supplier->first_name ?? '') . ' ' . ($ret->supplier->last_name ?? '')) ?: '—' }}</span></td>
                        <td>
                            <span class="purchase-return-items-count">{{ $ret->items->count() }} {{ __('items') }}</span>
                            <span class="purchase-return-items-quantity">{{ number_format((float) $ret->items->sum('quantity'), 2) }} {{ __('qty') }}</span>
                        </td>
                        <td class="text-right">
                            <span class="purchase-return-amount">{{ config('settings.currency_symbol') }}{{ number_format($ret->total_amount, 2) }}</span>
                            <span class="purchase-return-method">{{ ucfirst($ret->refund_method) }}</span>
                        </td>
                        <td class="text-center purchase-return-actions">
                            <a href="{{ route('purchases.return-receipt', $ret) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" title="{{ __('Print receipt') }}" aria-label="{{ __('Print receipt') }}">
                                <x-snd-icon name="printer" />
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="purchase-returns-empty-row">
                        <td colspan="7" class="purchase-returns-empty-cell">
                            <div class="purchase-returns-empty-state">
                                <span class="purchase-returns-empty-icon"><x-snd-icon name="receipt" /></span>
                                <span class="purchase-returns-empty-kicker">{{ __('Supplier refunds') }}</span>
                                <h3>{{ __('No purchase returns yet') }}</h3>
                                <p>{{ __('Processed returns will appear here with their original purchase, supplier, and refund total.') }}</p>
                                <a href="{{ route('purchases.index') }}" class="btn btn-primary">
                                    <x-snd-icon name="package" /> {{ __('View all purchases') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="card-footer purchase-returns-pagination snd-pagination-inline">
            <span>{{ __('Showing :first–:last of :total returns', ['first' => $returns->firstItem(), 'last' => $returns->lastItem(), 'total' => number_format($returns->total())]) }}</span>
            {{ $returns->links() }}
        </div>
        @endif
    </section>
</div>
@endsection

@section('css')
<style>
.purchase-returns-page { display: grid; gap: 16px; }
.purchase-returns-page > .card { margin: 0; }
.purchase-returns-filter-card .card-body { padding: 18px !important; }
.purchase-returns-filter-form { display: grid; gap: 16px; }
.purchase-returns-section-heading { display: flex; align-items: center; gap: 11px; min-width: 0; }
.purchase-returns-heading-icon { width: 38px; height: 38px; flex: 0 0 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); }
.purchase-returns-heading-icon .snd-icon { width: 18px; height: 18px; }
.purchase-returns-section-heading h2 { margin: 0; color: var(--snd-ink); font-size: .86rem; font-weight: var(--snd-font-semibold); }
.purchase-returns-section-heading p { margin: 4px 0 0; color: var(--snd-muted); font-size: .68rem; line-height: 1.4; }
.purchase-returns-filter-grid { display: grid; grid-template-columns: minmax(180px, 1.1fr) minmax(145px, .85fr) minmax(145px, .85fr) minmax(210px, 1.2fr) minmax(178px, auto); align-items: end; gap: 12px; }
.purchase-returns-field { min-width: 0; }
.purchase-returns-field label { display: flex; align-items: center; gap: 6px; margin: 0 0 6px; color: var(--snd-ink-soft); font-size: .68rem; font-weight: var(--snd-font-medium); }
.purchase-returns-field label .snd-icon { width: 14px; height: 14px; color: var(--snd-primary-deep); }
.purchase-returns-field .form-control { height: 40px; min-height: 40px; padding: 0 11px; }
.purchase-returns-filter-actions { display: grid; grid-template-columns: minmax(0, 1fr) 40px; gap: 7px; }
.purchase-returns-filter-actions .btn { height: 40px; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; margin: 0; white-space: nowrap; }
.purchase-returns-filter-actions .btn .snd-icon { width: 15px; height: 15px; }
.purchase-returns-filter-actions .btn-secondary { padding: 0 !important; }
.purchase-returns-history-card { overflow: hidden; }
.purchase-returns-history-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 18px !important; }
.purchase-returns-history-header .purchase-returns-heading-icon { width: 34px; height: 34px; flex-basis: 34px; border-radius: 10px; }
.purchase-returns-history-header .purchase-returns-heading-icon .snd-icon { width: 16px; height: 16px; }
.purchase-returns-count { display: inline-flex; align-items: center; gap: 7px; padding: 6px 10px; border: 1px solid rgba(151,134,238,.2); border-radius: 999px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .66rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.purchase-returns-count .snd-icon { width: 14px; height: 14px; }
.purchase-returns-table-wrap { border-radius: 0 0 var(--snd-radius-lg) var(--snd-radius-lg); }
.purchase-returns-table { min-width: 880px; }
.purchase-returns-table thead th { padding: 12px 14px; }
.purchase-returns-table tbody td { padding: 12px 14px; }
.purchase-return-voucher { display: inline-flex; align-items: center; min-height: 26px; padding: 0 9px; border: 1px solid rgba(151,134,238,.2); border-radius: 8px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .67rem; font-weight: var(--snd-font-semibold); white-space: nowrap; }
.purchase-return-date { color: var(--snd-ink-soft); white-space: nowrap; }
.purchase-return-purchase-link { color: var(--snd-primary-deep); font-size: .69rem; font-weight: var(--snd-font-semibold); }
.purchase-return-supplier { color: var(--snd-ink); font-weight: var(--snd-font-medium); }
.purchase-return-items-count { display: inline-flex; padding: 4px 8px; border: 1px solid rgba(151,134,238,.18); border-radius: 999px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: .64rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.purchase-return-items-quantity { margin-left: 5px; color: var(--snd-muted); font-size: .63rem; white-space: nowrap; }
.purchase-return-amount { display: block; color: var(--snd-ink); font-weight: var(--snd-font-semibold); white-space: nowrap; }
.purchase-return-method { display: inline-flex; margin-top: 4px; padding: 3px 7px; border-radius: 999px; background: var(--snd-surface-soft); color: var(--snd-muted); font-size: .59rem; text-transform: capitalize; }
.purchase-returns-table td.purchase-return-actions > a.btn { width: 32px; min-width: 32px; height: 32px; min-height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0 !important; border-radius: 6px !important; }
.purchase-returns-table td.purchase-return-actions > a.btn .snd-icon { width: 14px; height: 14px; }
.purchase-returns-table tbody tr.purchase-returns-empty-row:hover { background: transparent !important; }
.purchase-returns-table td.purchase-returns-empty-cell { padding: 0 !important; border-bottom: 0 !important; }
.purchase-returns-empty-state { min-height: 270px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 36px 20px; text-align: center; }
.purchase-returns-empty-icon { width: 58px; height: 58px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; border: 1px solid rgba(151,134,238,.19); border-radius: 18px; background: linear-gradient(145deg, var(--snd-primary-soft), rgba(255,255,255,.86)); color: var(--snd-primary-deep); box-shadow: 0 8px 20px rgba(151,134,238,.12); }
.purchase-returns-empty-icon .snd-icon { width: 25px; height: 25px; }
.purchase-returns-empty-kicker { color: var(--snd-primary-deep); font-size: .58rem; font-weight: var(--snd-font-semibold); letter-spacing: .13em; text-transform: uppercase; }
.purchase-returns-empty-state h3 { margin: 5px 0 0; color: var(--snd-ink); font-size: .95rem; font-weight: var(--snd-font-semibold); }
.purchase-returns-empty-state p { max-width: 410px; margin: 7px 0 17px; color: var(--snd-muted); font-size: .7rem; line-height: 1.55; }
.purchase-returns-empty-state .btn { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 13px !important; white-space: nowrap; }
.purchase-returns-empty-state .btn .snd-icon { width: 15px; height: 15px; }
.purchase-returns-pagination { display: flex; align-items: center; justify-content: space-between; gap: 14px; color: var(--snd-muted); font-size: .66rem; }
.purchase-returns-pagination .pagination { margin: 0; }

@media (max-width: 1199.98px) {
    .purchase-returns-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .purchase-returns-filter-actions { grid-column: 1 / -1; grid-template-columns: minmax(0, 1fr) 40px; }
}
@media (max-width: 575.98px) {
    .purchase-returns-filter-card .card-body { padding: 14px !important; }
    .purchase-returns-filter-grid { grid-template-columns: minmax(0, 1fr); gap: 11px; }
    .purchase-returns-filter-actions { grid-column: auto; }
    .purchase-returns-history-header { align-items: flex-start; flex-direction: column; }
    .purchase-returns-pagination { align-items: flex-start; flex-direction: column; }
    .purchase-returns-empty-state { min-height: 240px; padding-inline: 16px; }
}
</style>
@endsection
