@extends('layouts.admin')

@section('title', 'New Opening Inventory Entry')
@section('content-eyebrow', __('Inventory'))
@section('content-header', __('New Opening Inventory'))
@section('content-subtitle', __('Record starting stock balances and closing valuations directly into inventory'))
@section('content-actions')
    <a href="{{ route('opening-stocks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> {{ __('Opening Vouchers List') }}
    </a>
    <a href="{{ route('stocks.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-layer-group mr-1"></i> {{ __('Stock Report') }}
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('opening-stocks.store') }}" method="POST" id="openingStockForm">
        @csrf

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius: 12px;">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius: 12px;">
                <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Please check the form errors:</h6>
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Batch Information Header Card -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 16px; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title font-weight-bold text-dark m-0 d-flex align-items-center">
                    <i class="fas fa-sliders-h text-primary mr-2"></i> {{ __('Voucher & Branch Setup') }}
                </h5>
                <span class="badge badge-light border text-primary font-weight-bold px-3 py-2" style="font-size: 0.85rem; border-radius: 8px;">
                    <i class="fas fa-hashtag mr-1"></i> {{ $suggestedVoucherNo }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <!-- Default Branch -->
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase" style="letter-spacing: 0.03em;">
                            <i class="fas fa-warehouse text-primary mr-1"></i> {{ __('Default Branch') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="default_branch_id" id="default_branch_id" class="form-control form-control-sm font-weight-bold" style="border-radius: 10px; border: 1.5px solid var(--snd-border-strong); height: 38px;" required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('default_branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Applied automatically to all new rows.</small>
                    </div>

                    <!-- Voucher Date -->
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase" style="letter-spacing: 0.03em;">
                            <i class="fas fa-calendar-alt text-primary mr-1"></i> {{ __('Voucher Date') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="voucher_date" id="voucher_date" class="form-control form-control-sm" value="{{ old('voucher_date', $todayDate) }}" style="border-radius: 10px; border: 1.5px solid var(--snd-border-strong); height: 38px;" required>
                        <small class="form-text text-muted">Effective date for stock ledger entry.</small>
                    </div>

                    <!-- Voucher Note / Memo -->
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold text-muted small text-uppercase" style="letter-spacing: 0.03em;">
                            <i class="fas fa-sticky-note text-primary mr-1"></i> {{ __('Voucher Memo / Reference') }}
                        </label>
                        <input type="text" name="notes" id="notes" class="form-control form-control-sm" placeholder="e.g. Initial Opening Inventory Setup" value="{{ old('notes') }}" style="border-radius: 10px; border: 1.5px solid var(--snd-border-strong); height: 38px;">
                        <small class="form-text text-muted">Optional reference note recorded in ledger.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Items Table Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <div>
                    <h5 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-list-check text-primary mr-2"></i> {{ __('Opening Inventory Items') }}
                    </h5>
                    <div class="text-muted small mt-1">Select items, specify closing quantity and closing amount. Click "Add Row" to enter multiple items.</div>
                </div>
                <div class="mt-2 mt-md-0">
                    <button type="button" id="addRowBtn" class="btn btn-primary btn-sm font-weight-bold mr-2 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-plus mr-1"></i> {{ __('Add Row') }}
                    </button>
                    <button type="button" id="addFiveRowsBtn" class="btn btn-outline-primary btn-sm font-weight-bold mr-2 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-layer-group mr-1"></i> {{ __('+5 Rows') }}
                    </button>
                    <button type="button" id="clearAllRowsBtn" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-trash-alt mr-1"></i> {{ __('Clear All') }}
                    </button>
                </div>
            </div>

            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered align-middle mb-0" id="openingItemsTable">
                    <thead style="background: var(--snd-surface-soft); color: var(--snd-muted);">
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th style="min-width: 280px;">{{ __('Item / Product') }} <span class="text-danger">*</span></th>
                            <th style="width: 200px;">{{ __('Branch') }} <span class="text-danger">*</span></th>
                            <th style="width: 140px;" class="text-right">{{ __('Closing Qty') }} <span class="text-danger">*</span></th>
                            <th style="width: 140px;" class="text-right">{{ __('Unit Rate (Cost)') }}</th>
                            <th style="width: 160px;" class="text-right">{{ __('Closing Amount') }} <span class="text-danger">*</span></th>
                            <th style="min-width: 150px;">{{ __('Notes / Batch') }}</th>
                            <th style="width: 50px;" class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <!-- Dynamic Rows will be rendered here -->
                    </tbody>
                    <tfoot style="background: var(--snd-surface-soft); font-weight: 800; border-top: 2px solid var(--snd-border-strong);">
                        <tr>
                            <td colspan="3" class="text-right text-uppercase small text-muted">
                                Total Items: <span id="summary-items-count" class="text-dark h6 font-weight-bold mb-0">0</span>
                            </td>
                            <td class="text-right text-primary h6 font-weight-bold mb-0" id="summary-total-qty">
                                0.00
                            </td>
                            <td class="text-right small text-muted">
                                Total Valuation:
                            </td>
                            <td class="text-right text-success h5 font-weight-bold mb-0" id="summary-total-amount">
                                {{ config('settings.currency_symbol', 'Rs.') }}0.00
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Form Submission Bar -->
            <div class="card-footer bg-white p-3 border-top d-flex align-items-center justify-content-between flex-wrap">
                <div class="small text-muted font-weight-bold mb-2 mb-md-0">
                    <i class="fas fa-info-circle text-info mr-1"></i> Submitting records entries directly into the <code>stocks</code> ledger (Voucher Type 0: Opening).
                </div>
                <div>
                    <a href="{{ route('opening-stocks.index') }}" class="btn btn-outline-secondary font-weight-bold mr-2 px-3 py-2" style="border-radius: 10px;">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" id="submitBtn" class="btn btn-success font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 10px; background: linear-gradient(135deg, #1f7a3d 0%, #1f7a3d 100%); border: none;">
                        <i class="fas fa-check-circle mr-1"></i> {{ __('Submit Opening Inventory') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('css')
<style>
.table td, .table th {
    vertical-align: middle !important;
    padding: 8px 10px !important;
}
.row-index-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--snd-surface-soft);
    color: #505469;
    font-size: 0.75rem;
    font-weight: 700;
}
.remove-row-btn {
    border-radius: 8px;
    padding: 4px 8px;
}
</style>
@endsection

@section('js')
<script type="module">
jQuery(document).ready(function ($) {
    const currency = "{{ config('settings.currency_symbol', 'Rs.') }}";
    const productsData = @json($products);
    const branchesData = @json($branches);

    let rowIndex = 0;

    // Helper: Build product options
    function getProductOptions(selectedId = '') {
        let options = '<option value="">-- Select Product --</option>';
        productsData.forEach(function (p) {
            const isSelected = String(p.id) === String(selectedId) ? 'selected' : '';
            const cost = p.purchase_price ? parseFloat(p.purchase_price) : (p.price ? parseFloat(p.price) : 0);
            const stock = p.quantity !== undefined ? p.quantity : 0;
            const barcode = p.barcode ? ` [${p.barcode}]` : '';
            options += `<option value="${p.id}" data-cost="${cost}" data-stock="${stock}" ${isSelected}>${p.name}${barcode} (Stock: ${stock})</option>`;
        });
        return options;
    }

    // Helper: Build branch options
    function getBranchOptions(selectedId = '') {
        const defaultBranchId = $('#default_branch_id').val();
        const activeId = selectedId || defaultBranchId;
        let options = '';
        branchesData.forEach(function (b) {
            const isSelected = String(b.id) === String(activeId) ? 'selected' : '';
            options += `<option value="${b.id}" ${isSelected}>${b.name}</option>`;
        });
        return options;
    }

    // Add a new row to the table
    function addRow(prefilled = null) {
        const idx = rowIndex++;
        const defaultBranchId = $('#default_branch_id').val();

        const item_id = prefilled ? prefilled.item_id : '';
        const branch_id = prefilled ? prefilled.branch_id : defaultBranchId;
        const qty = prefilled ? prefilled.qty : '';
        const rate = prefilled ? prefilled.rate : '';
        const amount = prefilled ? prefilled.amount : '';
        const notes = prefilled ? prefilled.notes : '';

        const rowHtml = `
            <tr class="item-row" data-index="${idx}">
                <td class="text-center">
                    <span class="row-index-badge row-num-display">1</span>
                </td>
                <td>
                    <select name="items[${idx}][item_id]" class="form-control form-control-sm product-select font-weight-bold" style="border-radius: 8px; border: 1.5px solid var(--snd-border-strong);" required>
                        ${getProductOptions(item_id)}
                    </select>
                </td>
                <td>
                    <select name="items[${idx}][branch_id]" class="form-control form-control-sm branch-select font-weight-bold" style="border-radius: 8px; border: 1.5px solid var(--snd-border-strong);" required>
                        ${getBranchOptions(branch_id)}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" min="0.001" name="items[${idx}][qty]" class="form-control form-control-sm text-right qty-input font-weight-bold" value="${qty}" placeholder="0.00" style="border-radius: 8px; border: 1.5px solid var(--snd-border-strong);" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${idx}][rate]" class="form-control form-control-sm text-right rate-input font-weight-bold text-muted" value="${rate}" placeholder="0.00" style="border-radius: 8px; border: 1.5px solid var(--snd-border-strong);">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${idx}][amount]" class="form-control form-control-sm text-right amount-input font-weight-bold text-success" value="${amount}" placeholder="0.00" style="border-radius: 8px; border: 1.5px solid var(--snd-border-strong); background: var(--snd-surface-soft);" required>
                </td>
                <td>
                    <input type="text" name="items[${idx}][notes]" class="form-control form-control-sm row-notes-input" value="${notes}" placeholder="Optional row memo" style="border-radius: 8px; border: 1px solid var(--snd-border);">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Remove row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#itemsTableBody').append(rowHtml);
        updateRowNumbers();
        recalculateTotals();
    }

    // Update row numbers display
    function updateRowNumbers() {
        $('#itemsTableBody tr').each(function (i) {
            $(this).find('.row-num-display').text(i + 1);
        });
    }

    // Recalculate summary totals
    function recalculateTotals() {
        let totalItems = 0;
        let totalQty = 0;
        let totalAmount = 0;

        $('#itemsTableBody tr').each(function () {
            const productId = $(this).find('.product-select').val();
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            const amount = parseFloat($(this).find('.amount-input').val()) || 0;

            if (productId) {
                totalItems++;
            }
            totalQty += qty;
            totalAmount += amount;
        });

        $('#summary-items-count').text(totalItems);
        $('#summary-total-qty').text(totalQty.toFixed(2));
        $('#summary-total-amount').text(currency + totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // On Product Select: Auto-fill cost rate
    $(document).on('change', '.product-select', function () {
        const $row = $(this).closest('tr');
        const $selectedOption = $(this).find('option:selected');
        const costRate = parseFloat($selectedOption.data('cost')) || 0;

        const currentRate = parseFloat($row.find('.rate-input').val()) || 0;
        if (costRate > 0 && currentRate === 0) {
            $row.find('.rate-input').val(costRate.toFixed(2));
        }

        // Compute amount if qty exists
        const qty = parseFloat($row.find('.qty-input').val()) || 0;
        const rate = parseFloat($row.find('.rate-input').val()) || 0;
        if (qty > 0 && rate > 0) {
            $row.find('.amount-input').val((qty * rate).toFixed(2));
        }

        recalculateTotals();
    });

    // When Qty changes: Recalculate Amount (Qty * Rate)
    $(document).on('input', '.qty-input', function () {
        const $row = $(this).closest('tr');
        const qty = parseFloat($(this).val()) || 0;
        const rate = parseFloat($row.find('.rate-input').val()) || 0;

        if (qty > 0 && rate > 0) {
            $row.find('.amount-input').val((qty * rate).toFixed(2));
        }
        recalculateTotals();
    });

    // When Rate changes: Recalculate Amount (Qty * Rate)
    $(document).on('input', '.rate-input', function () {
        const $row = $(this).closest('tr');
        const rate = parseFloat($(this).val()) || 0;
        const qty = parseFloat($row.find('.qty-input').val()) || 0;

        if (qty > 0 && rate >= 0) {
            $row.find('.amount-input').val((qty * rate).toFixed(2));
        }
        recalculateTotals();
    });

    // When Amount changes: Recalculate Rate (Amount / Qty)
    $(document).on('input', '.amount-input', function () {
        const $row = $(this).closest('tr');
        const amount = parseFloat($(this).val()) || 0;
        const qty = parseFloat($row.find('.qty-input').val()) || 0;

        if (qty > 0 && amount >= 0) {
            $row.find('.rate-input').val((amount / qty).toFixed(2));
        }
        recalculateTotals();
    });

    // Remove row
    $(document).on('click', '.remove-row-btn', function () {
        const rowCount = $('#itemsTableBody tr').length;
        if (rowCount <= 1) {
            // Reset the only row
            const $row = $(this).closest('tr');
            $row.find('.product-select').val('');
            $row.find('.qty-input').val('');
            $row.find('.rate-input').val('');
            $row.find('.amount-input').val('');
            $row.find('.row-notes-input').val('');
        } else {
            $(this).closest('tr').remove();
            updateRowNumbers();
        }
        recalculateTotals();
    });

    // Add 1 Row Button
    $('#addRowBtn').on('click', function () {
        addRow();
    });

    // Add 5 Rows Button
    $('#addFiveRowsBtn').on('click', function () {
        for (let i = 0; i < 5; i++) {
            addRow();
        }
    });

    // Clear all rows
    $('#clearAllRowsBtn').on('click', function () {
        if (confirm('Clear all item rows in this form?')) {
            $('#itemsTableBody').empty();
            addRow();
        }
    });

    // Default Branch change: update rows that haven't been manually set
    $('#default_branch_id').on('change', function () {
        const newBranchId = $(this).val();
        $('.branch-select').val(newBranchId);
    });

    // Prevent submitting empty form & ignore blank rows
    $('#openingStockForm').on('submit', function (e) {
        let hasValidRow = false;
        $('#itemsTableBody tr').each(function () {
            const product = $(this).find('.product-select').val();
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            if (product && qty > 0) {
                hasValidRow = true;
            }
        });

        if (!hasValidRow) {
            e.preventDefault();
            alert('Please select at least one product with a closing quantity greater than 0.');
            return false;
        }

        // Disable inputs for unselected or zero-qty rows so they don't submit invalid data
        $('#itemsTableBody tr').each(function () {
            const product = $(this).find('.product-select').val();
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            if (!product || qty <= 0) {
                $(this).find('input, select').prop('disabled', true);
            }
        });

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');
    });

    // Initialize with 3 rows on start
    @if(old('items'))
        const oldItems = @json(old('items'));
        oldItems.forEach(function (item) {
            addRow(item);
        });
    @else
        addRow();
        addRow();
        addRow();
    @endif
});
</script>
@endsection
