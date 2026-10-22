@extends('layouts.admin')

@section('title', 'Create Stock Transfer')
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('Branch Stock Transfer'))
@section('content-subtitle', __('Move stock between branches while keeping inventory records synchronized.'))
@section('content-actions')
    <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> {{ __('Back to Transfers') }}
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark m-0">
                        <i class="fas fa-truck-loading text-primary mr-1"></i> {{ __('Transfer Stock Between Branches') }}
                    </h3>
                </div>

                <form action="{{ route('stock-transfers.store') }}" method="POST" id="stockTransferForm">
                    @csrf
                    <div class="card-body p-4">
                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        @endif

                        <!-- Branch Selection Row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-warehouse text-danger mr-1"></i> {{ __('From Branch (Source)') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="from_branch_id" id="from_branch_id" class="form-control @error('from_branch_id') is-invalid @enderror" required>
                                        <option value="">{{ __('-- Select Source Branch --') }}</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" {{ old('from_branch_id') == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }} ({{ $b->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_branch_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-store text-success mr-1"></i> {{ __('To Branch (Destination)') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="to_branch_id" id="to_branch_id" class="form-control @error('to_branch_id') is-invalid @enderror" required>
                                        <option value="">{{ __('-- Select Destination Branch --') }}</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" {{ old('to_branch_id') == $b->id ? 'selected' : '' }}>
                                                {{ $b->name }} ({{ $b->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_branch_id')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Product Selection with Small Current Stock Qty Display -->
                        <div class="form-group mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="font-weight-bold m-0" for="item_id">
                                    <i class="fas fa-box text-primary mr-1"></i> {{ __('Select Product') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <!-- Small Current Stock Badge shown dynamically when product is selected -->
                                <span id="currentStockBadge" class="badge badge-success px-2 py-1 font-weight-bold shadow-sm" style="display: none; font-size: 0.85rem;">
                                    <i class="fas fa-cubes mr-1"></i> Current Stock: <span id="stockValueSpan">0</span>
                                </span>
                            </div>

                            <select name="item_id" id="item_id" class="form-control select2 @error('item_id') is-invalid @enderror" required style="width: 100%;">
                                <option value="">{{ __('-- Choose a product to transfer --') }}</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}"
                                            data-stock="{{ $p->quantity }}"
                                            data-barcode="{{ $p->barcode }}"
                                            data-category="{{ $p->category?->name ?? 'General' }}"
                                            data-name="{{ $p->name }}"
                                            {{ old('item_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} [Barcode: {{ $p->barcode }}] &bull; Stock: {{ $p->quantity }}
                                    </option>
                                @endforeach
                            </select>
                            @error('item_id')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <!-- Dynamic Product Info Card -->
                        <div id="productInfoCard" class="card bg-light border p-3 mb-3" style="display: none;">
                            <div class="row align-items-center">
                                <div class="col-sm-7">
                                    <div class="font-weight-bold text-dark h6 mb-1" id="selectedProdName">-</div>
                                    <div class="text-muted small">
                                        Category: <span class="badge badge-light border" id="selectedProdCat">-</span> &bull;
                                        Barcode: <code id="selectedProdBarcode">-</code>
                                    </div>
                                </div>
                                <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                                    <span class="text-muted small d-block">Available In Stock</span>
                                    <span class="badge badge-success font-weight-bold px-3 py-2" style="font-size: 1.15rem;" id="selectedProdStockBadge">
                                        0
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity and Date -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold" for="quantity">
                                        <i class="fas fa-sort-numeric-up text-primary mr-1"></i> {{ __('Transfer Quantity') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               name="quantity"
                                               id="quantity"
                                               class="form-control font-weight-bold @error('quantity') is-invalid @enderror"
                                               min="0.01"
                                               step="1"
                                               placeholder="0"
                                               value="{{ old('quantity') }}"
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnMaxStock" title="Transfer all available stock">
                                                {{ __('Max') }}
                                            </button>
                                        </div>
                                    </div>
                                    <small id="qtyWarning" class="text-danger font-weight-bold mt-1" style="display: none;">
                                        <i class="fas fa-exclamation-circle"></i> Transfer quantity exceeds available stock!
                                    </small>
                                    @error('quantity')
                                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold" for="transfer_date">
                                        <i class="fas fa-calendar-alt text-primary mr-1"></i> {{ __('Transfer Date') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="transfer_date"
                                           id="transfer_date"
                                           class="form-control @error('transfer_date') is-invalid @enderror"
                                           value="{{ old('transfer_date', date('Y-m-d')) }}"
                                           required>
                                    @error('transfer_date')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-group mb-2">
                            <label class="font-weight-bold" for="notes">
                                <i class="fas fa-sticky-note text-secondary mr-1"></i> {{ __('Transfer Notes / Reason') }}
                            </label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g. Stock replenishment for downtown branch">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow-sm" id="btnSubmitTransfer">
                            <i class="fas fa-check mr-1"></i> {{ __('Confirm & Transfer Stock') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('item_id');
    const currentStockBadge = document.getElementById('currentStockBadge');
    const stockValueSpan = document.getElementById('stockValueSpan');
    const productInfoCard = document.getElementById('productInfoCard');
    const selectedProdName = document.getElementById('selectedProdName');
    const selectedProdCat = document.getElementById('selectedProdCat');
    const selectedProdBarcode = document.getElementById('selectedProdBarcode');
    const selectedProdStockBadge = document.getElementById('selectedProdStockBadge');
    const qtyInput = document.getElementById('quantity');
    const qtyWarning = document.getElementById('qtyWarning');
    const btnMax = document.getElementById('btnMaxStock');
    const btnSubmit = document.getElementById('btnSubmitTransfer');

    let currentAvailableStock = 0;

    function onProductChange() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            currentAvailableStock = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
            const barcode = selectedOption.getAttribute('data-barcode') || '';
            const category = selectedOption.getAttribute('data-category') || 'General';
            const name = selectedOption.getAttribute('data-name') || selectedOption.text;

            // Update small badge
            stockValueSpan.textContent = currentAvailableStock;
            currentStockBadge.style.display = 'inline-block';

            // Badge color based on stock
            if (currentAvailableStock <= 0) {
                currentStockBadge.className = 'badge badge-danger px-2 py-1 font-weight-bold shadow-sm';
                selectedProdStockBadge.className = 'badge badge-danger font-weight-bold px-3 py-2';
            } else if (currentAvailableStock < 5) {
                currentStockBadge.className = 'badge badge-warning text-dark px-2 py-1 font-weight-bold shadow-sm';
                selectedProdStockBadge.className = 'badge badge-warning text-dark font-weight-bold px-3 py-2';
            } else {
                currentStockBadge.className = 'badge badge-success px-2 py-1 font-weight-bold shadow-sm';
                selectedProdStockBadge.className = 'badge badge-success font-weight-bold px-3 py-2';
            }

            // Update card
            selectedProdName.textContent = name;
            selectedProdCat.textContent = category;
            selectedProdBarcode.textContent = barcode;
            selectedProdStockBadge.textContent = currentAvailableStock;
            productInfoCard.style.display = 'block';

            qtyInput.max = currentAvailableStock;
        } else {
            currentAvailableStock = 0;
            currentStockBadge.style.display = 'none';
            productInfoCard.style.display = 'none';
        }

        validateQty();
    }

    function validateQty() {
        const qtyVal = parseFloat(qtyInput.value) || 0;

        if (productSelect.value && qtyVal > currentAvailableStock) {
            qtyWarning.style.display = 'block';
            btnSubmit.disabled = true;
        } else {
            qtyWarning.style.display = 'none';
            btnSubmit.disabled = false;
        }
    }

    productSelect.addEventListener('change', onProductChange);
    qtyInput.addEventListener('input', validateQty);
    qtyInput.addEventListener('change', validateQty);

    btnMax.addEventListener('click', function () {
        if (currentAvailableStock > 0) {
            qtyInput.value = currentAvailableStock;
            validateQty();
        }
    });

    // Initialize on page load if old value exists
    if (productSelect.value) {
        onProductChange();
    }
});
</script>
@endsection
