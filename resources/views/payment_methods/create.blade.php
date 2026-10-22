@extends('layouts.admin')

@section('title', 'Add Card Payment Method')
@section('content-header', 'Add Card Payment Method')
@section('content-actions')
<a href="{{ route('payment-methods.index') }}" class="btn btn-outline-secondary font-weight-bold" style="border-radius: 8px;">
    <i class="fas fa-arrow-left mr-1"></i> Back to Listing
</a>
@endsection

@section('css')
<style>
.input-group > .input-group-prepend > .input-group-text {
    border-right: 0;
}
.input-group > .form-control {
    border-left: 0;
}
.input-group > .input-group-prepend > .input-group-text,
.input-group > .form-control {
    border-radius: 8px !important;
}
.input-group > .input-group-prepend > .input-group-text {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
.input-group > .form-control {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <div style="width:34px;height:34px;border-radius:8px;background:#f0edff;color:#6758bd;display:flex;align-items:center;justify-content:center;margin-right:12px;">
                <i class="fas fa-credit-card"></i>
            </div>
            <div>
                <h6 class="mb-0 font-weight-bold">Add Card Payment Method</h6>
                <small class="text-muted">POS card machine and receiving bank account details.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form action="{{ route('payment-methods.store') }}" method="POST">
            @csrf

            <input type="hidden" name="type" value="card">

            <div class="form-group row">
                <div class="col-md-6">
                    <label for="bank_name" class="font-weight-600">Bank Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fas fa-university text-muted"></i></span>
                        </div>
                        <input type="text" name="bank_name" id="bank_name"
                               class="form-control @error('bank_name') is-invalid @enderror"
                               placeholder="e.g. Bank Alfalah Ltd, Meezan Bank, HBL"
                               value="{{ old('bank_name') }}" required>
                    </div>
                    @error('bank_name')<span class="text-danger small"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="col-md-6">
                    <label for="account_title" class="font-weight-600">Account Title</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fas fa-user-check text-muted"></i></span>
                        </div>
                        <input type="text" name="account_title" id="account_title"
                               class="form-control @error('account_title') is-invalid @enderror"
                               placeholder="e.g. Zargham POS Merchant"
                               value="{{ old('account_title') }}">
                    </div>
                    @error('account_title')<span class="text-danger small"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-6">
                    <label for="account_number" class="font-weight-600">Account Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                        </div>
                        <input type="text" name="account_number" id="account_number"
                               class="form-control @error('account_number') is-invalid @enderror"
                               placeholder="e.g. 01020304050607"
                               value="{{ old('account_number') }}">
                    </div>
                    @error('account_number')<span class="text-danger small"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="col-md-6">
                    <label for="iban" class="font-weight-600">IBAN</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fas fa-globe text-muted"></i></span>
                        </div>
                        <input type="text" name="iban" id="iban"
                               class="form-control @error('iban') is-invalid @enderror"
                               placeholder="e.g. PK36MEZN0001020304050601"
                               value="{{ old('iban') }}">
                    </div>
                    @error('iban')<span class="text-danger small"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-4">
                    <label for="branch_name" class="font-weight-600">Branch Name</label>
                    <input type="text" name="branch_name" id="branch_name"
                           class="form-control @error('branch_name') is-invalid @enderror"
                           placeholder="e.g. Commercial Area Branch"
                           value="{{ old('branch_name') }}">
                    @error('branch_name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="branch_code" class="font-weight-600">Branch Code</label>
                    <input type="text" name="branch_code" id="branch_code"
                           class="form-control @error('branch_code') is-invalid @enderror"
                           placeholder="e.g. 0102"
                           value="{{ old('branch_code') }}">
                    @error('branch_code')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="swift_code" class="font-weight-600">Swift Code</label>
                    <input type="text" name="swift_code" id="swift_code"
                           class="form-control @error('swift_code') is-invalid @enderror"
                           placeholder="e.g. MEZNPKKA"
                           value="{{ old('swift_code') }}">
                    @error('swift_code')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-4">
                    <label class="font-weight-600 d-block">Status</label>
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" name="status" class="custom-control-input" id="statusSwitch" value="1" {{ old('status', 1) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-600" for="statusSwitch" id="statusLabel">
                            {{ old('status', 1) ? 'Active' : 'Inactive' }}
                        </label>
                    </div>
                    <small class="text-muted">Inactive methods hidden at checkout.</small>
                </div>
                <div class="col-md-8">
                    <label for="notes" class="font-weight-600">Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Optional notes for cashiers...">{{ old('notes') }}</textarea>
                    @error('notes')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end pt-2 border-top">
                <a href="{{ route('payment-methods.index') }}" class="btn btn-default mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle mr-1"></i> Save Card Method
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#statusSwitch').on('change', function() {
        $('#statusLabel').text(this.checked ? 'Active' : 'Inactive');
    });
});
</script>
@endsection
