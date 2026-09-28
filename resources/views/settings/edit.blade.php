@extends('layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('settings.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="snd-form-section-heading">
                <x-snd-icon name="sliders" />
                <div>
                    <h3>POS Configuration</h3>
                    <p>Configure the catalog, checkout, receipt, and tax settings used by {{ \App\Helpers\CommonHelper::getBrandName() }}.</p>
                </div>
            </div>

            <div class="row">
            <div class="col-md-4 form-group">
                <label for="app_name">{{ __('settings.app_name') }}</label>
                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name" placeholder="{{ __('settings.App_name') }}" value="{{ old('app_name', config('settings.app_name')) }}">
                @error('app_name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="app_description">{{ __('settings.app_description') }}</label>
                <textarea name="app_description" class="form-control @error('app_description') is-invalid @enderror" id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                @error('app_description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="currency_symbol">{{ __('settings.Currency_symbol') }}</label>
                <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" placeholder="{{ __('settings.Currency_symbol') }}" value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                @error('currency_symbol')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="col-md-4 form-group">
                <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                <input type="text" name="warning_quantity" class="form-control @error('warning_quantity') is-invalid @enderror" id="warning_quantity" placeholder="{{ __('settings.warning_quantity') }}" value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                @error('warning_quantity')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="show_products">Show Products in Sale Screen</label>
                <select name="show_products" class="form-control @error('show_products') is-invalid @enderror" id="show_products">
                    <option value="1" {{ old('show_products', config('settings.show_products', true)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('show_products', config('settings.show_products', true)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="show_services">Show Services in Sale Screen</label>
                <select name="show_services" class="form-control @error('show_services') is-invalid @enderror" id="show_services">
                    <option value="1" {{ old('show_services', config('settings.show_services', false)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('show_services', config('settings.show_services', false)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="srb_enabled">Enable SRB Fiscal Integration</label>
                <select name="srb_enabled" class="form-control @error('srb_enabled') is-invalid @enderror" id="srb_enabled">
                    <option value="1" {{ old('srb_enabled', config('settings.srb_enabled', false)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('srb_enabled', config('settings.srb_enabled', false)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="business_logo">Business Logo (Receipt Header)</label>
                <input type="file" name="business_logo" class="form-control-file @error('business_logo') is-invalid @enderror" id="business_logo" accept="image/png,image/jpeg,image/webp">
                <small class="form-text text-muted">PNG, JPG, or WebP; maximum 2 MB.</small>
                @if(config('settings.business_logo'))
                    <img src="{{ \App\Helpers\CommonHelper::getLogoUrl() }}" alt="Business logo" style="display:block;max-height:54px;max-width:180px;margin-top:8px;object-fit:contain;">
                @endif
                @error('business_logo')
                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="col-md-4 form-group">
                <label for="srb_business_name">SRB Registered Business Name</label>
                <input type="text" name="srb_business_name" class="form-control" id="srb_business_name" value="{{ old('srb_business_name', config('settings.srb_business_name')) }}">
                <small class="form-text text-muted">Use the SRB-registered name, without special characters.</small>
            </div>

            <div class="col-md-4 form-group">
                <label for="srb_ntn">SRB NTN</label>
                <input type="text" name="srb_ntn" class="form-control" id="srb_ntn" value="{{ old('srb_ntn', config('settings.srb_ntn')) }}">
                <small class="form-text text-muted">Enter NTN without a leading S or a hyphen suffix.</small>
            </div>

            <div class="col-md-4 form-group">
                <label for="srb_transaction_type">SRB Transaction Type</label>
                <select name="srb_transaction_type" class="form-control" id="srb_transaction_type">
                    <option value="Test" {{ old('srb_transaction_type', config('settings.srb_transaction_type', 'Test')) === 'Test' ? 'selected' : '' }}>Test</option>
                    <option value="Live" {{ old('srb_transaction_type', config('settings.srb_transaction_type')) === 'Live' ? 'selected' : '' }}>Live</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="tax_enabled">Enable Tax on Sale Screen</label>
                <select name="tax_enabled" class="form-control" id="tax_enabled">
                    <option value="1" {{ old('tax_enabled', config('settings.tax_enabled', false)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('tax_enabled', config('settings.tax_enabled', false)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="discount_enabled">Enable Discount on Sale Screen</label>
                <select name="discount_enabled" class="form-control" id="discount_enabled">
                    <option value="1" {{ old('discount_enabled', config('settings.discount_enabled', false)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('discount_enabled', config('settings.discount_enabled', false)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="col-md-4 form-group">
                <label for="editable_item_rate">Allow Editing Item Rate on Sale Screen</label>
                <select name="editable_item_rate" class="form-control" id="editable_item_rate">
                    <option value="1" {{ old('editable_item_rate', config('settings.editable_item_rate', false)) == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('editable_item_rate', config('settings.editable_item_rate', false)) == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            </div>

            <div class="snd-form-actions">
                <button type="submit" class="btn btn-primary">{{ __('settings.Change_Setting') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
