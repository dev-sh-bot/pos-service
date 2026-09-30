@extends('layouts.admin')

@section('title', 'Create New Deal')
@section('content-header', 'Create New Deal')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    border: 1px solid var(--snd-border) !important;
    border-radius: 10px !important;
    padding: 6px 10px !important;
    min-height: 46px !important;
    background: #fff !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--snd-primary) !important;
    box-shadow: 0 0 0 3px var(--snd-primary-ring) !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: var(--snd-button-primary) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    border-radius: 8px !important;
    padding: 3px 10px 3px 22px !important;
    margin-top: 3px !important;
    margin-right: 5px !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff !important;
    left: 5px !important;
    border-right: none !important;
}
.select2-dropdown {
    border: 1px solid var(--snd-border) !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1) !important;
}
.select2-results__group {
    background: var(--snd-surface-soft, #f8fafc) !important;
    color: var(--snd-ink) !important;
    font-weight: 700 !important;
    font-size: 0.8rem !important;
    padding: 8px 12px !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--snd-button-primary) !important;
    color: var(--snd-button-primary-text) !important;
}
.deal-savings-box {
    background: var(--snd-success-soft, #eaf9ee);
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 16px;
}
.deal-count-badge {
    background: var(--snd-primary-soft, #f0edff);
    color: var(--snd-primary-deep, #6758bd);
    border: 1px solid var(--snd-border);
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
}
</style>
@endsection

@section('content')
<form action="{{ route('deals.store') }}" method="POST" id="deal-form">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="snd-form-section-heading">
                        <x-snd-icon name="tags" />
                        <div>
                            <h3>Deal Package Information</h3>
                            <p>Enter deal details and select the services included in this package.</p>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="name">Deal Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Party Makeup + Hair Styling Deal" value="{{ old('name') }}" required>
                                @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="barcode">Barcode / Code <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="barcode" id="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Auto-generated if empty" value="{{ old('barcode') }}">
                                @error('barcode')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description / Details</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Briefly describe what's included in this deal...">{{ old('description') }}</textarea>
                                @error('description')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="services-select" class="mb-0">Select Included Services <span class="text-danger">*</span></label>
                                    <span class="deal-count-badge" id="selected-services-count">0 Services Selected</span>
                                </div>
                                <select name="services[]" id="services-select" class="form-control select2" multiple="multiple" data-placeholder="Search and select services..." required style="width: 100%;">
                                    @foreach($categories as $category)
                                        @if($category->services->count() > 0)
                                            <optgroup label="{{ $category->name }}">
                                                @foreach($category->services as $service)
                                                    <option value="{{ $service->id }}" data-rate="{{ $service->rate }}" {{ is_array(old('services')) && in_array($service->id, old('services')) ? 'selected' : '' }}>
                                                        {{ $service->name }} — {{ config('settings.currency_symbol') }} {{ number_format($service->rate, 2) }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                    @if($uncategorizedServices->count() > 0)
                                        <optgroup label="Other Services">
                                            @foreach($uncategorizedServices as $service)
                                                <option value="{{ $service->id }}" data-rate="{{ $service->rate }}" {{ is_array(old('services')) && in_array($service->id, old('services')) ? 'selected' : '' }}>
                                                    {{ $service->name }} — {{ config('settings.currency_symbol') }} {{ number_format($service->rate, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <small class="text-muted mt-2 d-block">Search and select multiple services. Prices auto-sum in real time.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="snd-form-section-heading">
                        <x-snd-icon name="calculator" />
                        <div>
                            <h3>Deal Pricing & Savings</h3>
                            <p>Set original total and discounted deal price.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="original_amount">Before Amount (Original Total)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ config('settings.currency_symbol') }}</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="original_amount" id="original_amount" class="form-control @error('original_amount') is-invalid @enderror" value="{{ old('original_amount', '0.00') }}" required>
                        </div>
                        <small class="text-muted mt-1 d-block">Auto-summed from selected services.</small>
                        @error('original_amount')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="discounted_amount">After Discount (Deal Price) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ config('settings.currency_symbol') }}</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="discounted_amount" id="discounted_amount" class="form-control @error('discounted_amount') is-invalid @enderror" value="{{ old('discounted_amount', '0.00') }}" required>
                        </div>
                        @error('discounted_amount')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="deal-savings-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold">Discount Percentage</span>
                            <span class="badge badge-success" id="live-discount-percent">0% OFF</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px dashed #bbf7d0;">
                            <span class="small text-muted font-weight-bold">Customer Savings</span>
                            <span class="font-weight-bold text-success" id="live-discount-savings">{{ config('settings.currency_symbol') }} 0.00</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="snd-form-toggle">
                            <input type="checkbox" name="status" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                            <span>Active Deal Package</span>
                        </label>
                    </div>

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary"><x-snd-icon name="save" class="mr-1" /> Save Deal Package</button>
                        <a href="{{ route('deals.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function updateDealCalculations() {
        const select = document.getElementById('services-select');
        let totalOriginal = 0;
        let selectedCount = 0;

        if (select) {
            Array.from(select.selectedOptions).forEach(opt => {
                totalOriginal += parseFloat(opt.dataset.rate || 0);
                selectedCount++;
            });
        }

        const countBadge = document.getElementById('selected-services-count');
        if (countBadge) countBadge.innerText = `${selectedCount} Services Selected`;

        const originalInput = document.getElementById('original_amount');
        if (originalInput) originalInput.value = totalOriginal.toFixed(2);

        calculateDiscount();
    }

    function calculateDiscount() {
        const original = parseFloat(document.getElementById('original_amount').value || 0);
        const discounted = parseFloat(document.getElementById('discounted_amount').value || 0);
        let percent = 0;
        let savings = 0;

        if (original > 0 && discounted < original) {
            savings = original - discounted;
            percent = (savings / original) * 100;
        }

        const percentBadge = document.getElementById('live-discount-percent');
        if (percentBadge) percentBadge.innerText = `${percent.toFixed(0)}% OFF`;

        const savingsSpan = document.getElementById('live-discount-savings');
        if (savingsSpan) savingsSpan.innerText = `{{ config('settings.currency_symbol') }} ${savings.toFixed(2)}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.$ && $.fn.select2) {
            $('#services-select').select2({
                placeholder: 'Search and select services...',
                allowClear: true,
                width: '100%'
            }).on('change', updateDealCalculations);
        }

        document.getElementById('original_amount').addEventListener('input', calculateDiscount);
        document.getElementById('discounted_amount').addEventListener('input', calculateDiscount);
        updateDealCalculations();
    });
</script>
@endsection
