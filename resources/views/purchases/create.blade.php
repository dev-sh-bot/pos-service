@extends('layouts.admin')

@section('title', 'New Purchase')

@section('content-eyebrow', __('Purchases'))
@section('content-header', __('New Purchase'))
@section('content-actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary"><x-snd-icon name="arrow-left" class="mr-1" />{{ __('Back') }}</a>
@endsection

@section('content')
    <div class="purchase-page-workspace">
        <div id="purchase"></div>
    </div>
@endsection
