@extends('layouts.admin')

@section('title', __('Purchase'))

@section('content-eyebrow', __('Purchases'))

@section('content')

<div class="management-page management-page--purchases management-page--list">
    @include('management.context', ['module' => 'purchases'])
    <div class="management-workspace">
<div id="purchase"></div>


    </div>
</div>
@endsection