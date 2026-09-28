@extends('layouts.admin')

@section('title', __('order.title'))

@section('content')
    <div id="cart"></div>
@endsection

@section('css')
<style>
.content-wrapper {
    padding-bottom: 0 !important;
    background: #f1f5f9 !important;
    min-height: calc(100vh - 57px) !important;
    height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}
.content-header {
    display: none !important;
}
.main-footer {
    display: none !important;
}
.content {
    padding: 0 !important;
    margin: 0 !important;
    height: calc(100vh - 57px) !important;
    max-height: calc(100vh - 57px) !important;
    overflow: hidden !important;
}
#cart {
    width: 100%;
    height: 100%;
}
/* Ensure background never turns white when print/receipt modal is open */
body:not(.printing-receipt) {
    background-color: #f1f5f9 !important;
}
body.swal2-shown {
    background-color: #f1f5f9 !important;
}
.swal2-container.swal2-backdrop-show {
    background-color: rgba(15, 23, 42, 0.5) !important;
    backdrop-filter: blur(2px);
}
.swal2-container.swal2-backdrop-hide,
.swal2-container:not(.swal2-backdrop-show) {
    pointer-events: none !important;
}
#receipt-print-frame {
    pointer-events: none !important;
}
</style>
@endsection

