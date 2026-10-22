@extends('layouts.admin')

@section('title', 'Purchase Return - ' . ($purchase->purchase_no ?: '#' . $purchase->id))
@section('content-eyebrow', __('Purchases'))
@section('content-header', __('Purchase Return'))
@section('content-subtitle', __('Record items returned against purchase ') . ($purchase->purchase_no ?: '#' . $purchase->id))
@section('content-actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> {{ __('Back to Purchases') }}
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Purchase Info Card -->
        <div class="col-md-4">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-file-invoice mr-1"></i> {{ __('Original Purchase Info') }}
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="pl-3" style="width: 45%;">{{ __('Purchase Ref:') }}</th>
                            <td class="font-weight-bold text-primary">{{ $purchase->purchase_no ?: ('#' . $purchase->id) }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('Purchase Date:') }}</th>
                            <td>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('Supplier:') }}</th>
                            <td>
                                <strong>{{ $purchase->supplier->first_name ?? '' }} {{ $purchase->supplier->last_name ?? '' }}</strong>
                                @if(optional($purchase->supplier)->phone)
                                    <div class="text-muted small"><i class="fas fa-phone-alt"></i> {{ $purchase->supplier->phone }}</div>
                                @endif
                            </td>
                        </tr>
                        @if($purchase->branch)
                        <tr>
                            <th class="pl-3">{{ __('Branch:') }}</th>
                            <td><span class="badge badge-info">{{ $purchase->branch->name }}</span></td>
                        </tr>
                        @endif
                        <tr>
                            <th class="pl-3">{{ __('Subtotal:') }}</th>
                            <td>{{ config('settings.currency_symbol') }}{{ number_format($purchase->subtotal ?: $purchase->total_amount, 2) }}</td>
                        </tr>
                        @if((float)$purchase->tax_amount > 0)
                        <tr>
                            <th class="pl-3">{{ __('Tax:') }}</th>
                            <td>{{ config('settings.currency_symbol') }}{{ number_format($purchase->tax_amount, 2) }} ({{ $purchase->tax_percent }}%)</td>
                        </tr>
                        @endif
                        @if((float)$purchase->discount_amount > 0)
                        <tr>
                            <th class="pl-3">{{ __('Discount:') }}</th>
                            <td>-{{ config('settings.currency_symbol') }}{{ number_format($purchase->discount_amount, 2) }} ({{ $purchase->discount_percent }}%)</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="pl-3">{{ __('Original Total:') }}</th>
                            <td class="font-weight-bold text-success" style="font-size: 1.1rem;">
                                {{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="pl-3">{{ __('Payment:') }}</th>
                            <td><span class="badge badge-light border">{{ ucfirst($purchase->payment_method ?? 'Cash') }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Return Instructions -->
            <div class="card card-outline card-warning shadow-sm mt-3">
                <div class="card-header bg-warning py-2">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-info-circle mr-1"></i> {{ __('Return Rules & Inventory') }}
                    </h3>
                </div>
                <div class="card-body text-sm py-3">
                    <ul class="pl-3 mb-0">
                        <li>Entered return quantities will be <strong>subtracted from product inventory</strong> immediately.</li>
                        <li>A transaction record will be logged in <strong>Stock Ledger</strong> under <code>Voucher Type 2 (Purchase Return)</code>.</li>
                        <li>Items already returned in prior returns cannot exceed the original purchased quantity.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Return Items Table & Form -->
        <div class="col-md-8">
            <form action="{{ route('purchases.process-return', $purchase) }}" method="POST" id="purchaseReturnForm">
                @csrf

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                        <h3 class="card-title font-weight-bold text-dark m-0">
                            <i class="fas fa-boxes text-warning mr-1"></i> {{ __('Items to Return') }}
                        </h3>
                        <span class="badge badge-warning px-2 py-1">Select items and quantities below</span>
                    </div>

                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 32%;">{{ __('Product') }}</th>
                                    <th class="text-center" style="width: 14%;">{{ __('Purchased') }}</th>
                                    <th class="text-center" style="width: 14%;">{{ __('Already Ret.') }}</th>
                                    <th class="text-center" style="width: 14%;">{{ __('Available') }}</th>
                                    <th class="text-center" style="width: 18%;">{{ __('Return Qty') }}</th>
                                    <th class="text-right pr-3" style="width: 18%;">{{ __('Refund Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemsWithAvailable as $idx => $item)
                                <tr class="{{ $item['available_qty'] <= 0 ? 'table-secondary opacity-75' : '' }}">
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $item['product_name'] }}</div>
                                        <div class="text-muted small">
                                            @if($item['barcode']) <code>{{ $item['barcode'] }}</code> &bull; @endif
                                            Stock: <span class="badge badge-light border">{{ $item['current_stock'] }}</span>
                                        </div>
                                        <input type="hidden" name="items[{{ $idx }}][purchase_item_id]" value="{{ $item['id'] }}">
                                        <input type="hidden" name="items[{{ $idx }}][return_price]" id="price_{{ $idx }}" value="{{ $item['purchase_price'] }}">
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $item['purchased_qty'] }}</td>
                                    <td class="text-center text-muted">{{ $item['already_returned'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $item['available_qty'] > 0 ? 'success' : 'danger' }} px-2 py-1">
                                            {{ $item['available_qty'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item['available_qty'] > 0)
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                   name="items[{{ $idx }}][quantity]"
                                                   id="qty_{{ $idx }}"
                                                   class="form-control text-center font-weight-bold return-qty-input"
                                                   min="0"
                                                   max="{{ $item['available_qty'] }}"
                                                   step="1"
                                                   value="0"
                                                   data-idx="{{ $idx }}"
                                                   data-price="{{ $item['purchase_price'] }}"
                                                   data-max="{{ $item['available_qty'] }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary max-btn" data-idx="{{ $idx }}" data-max="{{ $item['available_qty'] }}" title="Return All Available">Max</button>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-muted small text-center d-block">Fully Returned</span>
                                        <input type="hidden" name="items[{{ $idx }}][quantity]" value="0">
                                        @endif
                                    </td>
                                    <td class="text-right pr-3 font-weight-bold text-success" id="line_total_{{ $idx }}">
                                        {{ config('settings.currency_symbol') }}0.00
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-light p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold mb-1">{{ __('Return Date') }}</label>
                                    <input type="date" name="return_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold mb-1">{{ __('Refund Method') }}</label>
                                    <select name="refund_method" class="form-control form-control-sm">
                                        <option value="cash">{{ __('Cash Refund') }}</option>
                                        <option value="bank">{{ __('Bank Transfer') }}</option>
                                        <option value="credit">{{ __('Supplier Credit / Payable Adjustment') }}</option>
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold mb-1">{{ __('Return Reason / Notes') }}</label>
                                    <textarea name="return_reason" class="form-control form-control-sm" rows="2" placeholder="e.g. Damaged goods, incorrect item specification, etc."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="p-3 bg-white border rounded shadow-sm text-right">
                                    <div class="text-muted small text-uppercase font-weight-bold">{{ __('Total Refund Amount') }}</div>
                                    <div class="font-weight-bold text-success my-1" style="font-size: 1.85rem;" id="grand_refund_total">
                                        {{ config('settings.currency_symbol') }}0.00
                                    </div>
                                    <div class="text-muted small mb-3">Items returning: <span id="total_items_returning" class="font-weight-bold">0</span></div>

                                    <button type="submit" class="btn btn-warning btn-lg btn-block font-weight-bold shadow-sm" id="btnSubmitReturn" disabled>
                                        <i class="fas fa-check-circle mr-1"></i> {{ __('Confirm & Process Return') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('.return-qty-input');
    const grandRefundTotalEl = document.getElementById('grand_refund_total');
    const totalItemsReturningEl = document.getElementById('total_items_returning');
    const btnSubmit = document.getElementById('btnSubmitReturn');
    const currency = "{{ config('settings.currency_symbol') }}";

    function recalculate() {
        let totalRefund = 0;
        let totalItems = 0;

        qtyInputs.forEach(input => {
            const idx = input.getAttribute('data-idx');
            const price = parseFloat(input.getAttribute('data-price')) || 0;
            const max = parseFloat(input.getAttribute('data-max')) || 0;
            let val = parseFloat(input.value) || 0;

            if (val < 0) { val = 0; input.value = 0; }
            if (val > max) { val = max; input.value = max; }

            const lineTotal = val * price;
            const lineTotalEl = document.getElementById('line_total_' + idx);
            if (lineTotalEl) {
                lineTotalEl.textContent = currency + lineTotal.toFixed(2);
            }

            totalRefund += lineTotal;
            totalItems += val;
        });

        grandRefundTotalEl.textContent = currency + totalRefund.toFixed(2);
        totalItemsReturningEl.textContent = totalItems.toString();

        btnSubmit.disabled = totalItems <= 0;
    }

    qtyInputs.forEach(input => {
        input.addEventListener('input', recalculate);
        input.addEventListener('change', recalculate);
    });

    document.querySelectorAll('.max-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const idx = this.getAttribute('data-idx');
            const max = this.getAttribute('data-max');
            const input = document.getElementById('qty_' + idx);
            if (input) {
                input.value = max;
                recalculate();
            }
        });
    });

    recalculate();
});
</script>
@endsection
