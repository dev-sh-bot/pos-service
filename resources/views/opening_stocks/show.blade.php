@extends('layouts.admin')

@section('title', 'Opening Inventory Voucher ' . $voucher_no)
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('Opening Voucher: ') . $voucher_no)
@section('content-actions')
    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
        <i class="fas fa-print mr-1"></i> {{ __('Print Voucher') }}
    </button>
    <a href="{{ route('opening-stocks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> {{ __('Back to List') }}
    </a>
    <a href="{{ route('opening-stocks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> {{ __('New Entry') }}
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="card-title font-weight-bold text-dark m-0">
                    <i class="fas fa-receipt text-primary mr-1"></i> Voucher Summary
                </h5>
                <div class="text-muted small mt-1">Date: {{ $voucherDate ? ($voucherDate instanceof \DateTimeInterface ? $voucherDate->format('d/m/Y') : \Carbon\Carbon::parse($voucherDate)->format('d/m/Y')) : '—' }} &bull; Branch(es): {{ $branches }}</div>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 0.88rem; border-radius: 8px;">
                    Total Valuation: {{ config('settings.currency_symbol', 'Rs.') }}{{ number_format($totalAmount, 2) }}
                </span>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: var(--snd-surface-soft); color: var(--snd-muted);">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="min-width: 260px;">{{ __('Product / Item') }}</th>
                        <th style="width: 180px;">{{ __('Branch') }}</th>
                        <th style="width: 130px;" class="text-right">{{ __('Closing Qty') }}</th>
                        <th style="width: 130px;" class="text-right">{{ __('Unit Rate') }}</th>
                        <th style="width: 150px;" class="text-right">{{ __('Closing Amount') }}</th>
                        <th style="min-width: 200px;">{{ __('Notes / Memo') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $item)
                    <tr>
                        <td class="text-center text-muted font-weight-bold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $item->product?->name ?: ('Item #' . $item->item_id) }}</div>
                            <div class="small text-muted">
                                @if($item->product?->category)
                                    <span class="badge badge-light border">{{ $item->product->category->name }}</span>
                                @endif
                                @if($item->product?->barcode)
                                    <code>{{ $item->product->barcode }}</code>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-light border text-dark font-weight-bold">
                                <i class="fas fa-warehouse text-primary mr-1"></i> {{ $item->branch?->name ?: '—' }}
                            </span>
                        </td>
                        <td class="text-right font-weight-bold text-success" style="font-size: 0.95rem;">
                            +{{ number_format($item->qty, 2) }}
                        </td>
                        <td class="text-right text-muted font-weight-bold">
                            {{ config('settings.currency_symbol', 'Rs.') }}{{ number_format($item->rate, 2) }}
                        </td>
                        <td class="text-right font-weight-bold text-dark">
                            {{ config('settings.currency_symbol', 'Rs.') }}{{ number_format($item->amount, 2) }}
                        </td>
                        <td class="small text-muted">
                            {{ $item->notes ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: var(--snd-surface-soft); font-weight: 800; border-top: 2px solid var(--snd-border-strong);">
                    <tr>
                        <td colspan="3" class="text-right text-uppercase small text-muted">
                            Total Items: {{ $items->count() }}
                        </td>
                        <td class="text-right text-primary h6 font-weight-bold mb-0">
                            {{ number_format($totalQty, 2) }}
                        </td>
                        <td class="text-right small text-muted">
                            Total Valuation:
                        </td>
                        <td class="text-right text-success h5 font-weight-bold mb-0">
                            {{ config('settings.currency_symbol', 'Rs.') }}{{ number_format($totalAmount, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
