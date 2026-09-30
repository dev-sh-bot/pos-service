@extends('layouts.admin')

@section('title', 'New Purchase')

@section('content-eyebrow', __('Purchases'))
@section('content-header', __('New Purchase'))
@section('content-actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary"><x-snd-icon name="arrow-left" class="mr-1" />{{ __('Back') }}</a>
@endsection

@section('content')
    <div class="container-fluid">
        <div id="purchase"></div>
    </div>
@endsection

@push('styles')
    <style>
        /* Purchase Page Custom Styles */
        .purchase-container {
            padding: 0;
        }

        /* Product Grid Styling */
        .order-product {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            max-height: calc(100vh - 300px);
            overflow-y: auto;
            padding: 10px;
        }

        .order-product .item {
            background: #fff;
            border: 2px solid var(--snd-border-strong);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .order-product .item:hover {
            border-color: var(--snd-button-primary);
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(81,69,162,.18);
        }

        .order-product .item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .order-product .item h5 {
            font-size: 0.9rem;
            margin: 0;
            color: var(--snd-ink);
            font-weight: 600;
        }

        .order-product .item small {
            font-size: 0.75rem;
            color: var(--snd-muted);
        }

        /* Cart Table Styling */
        .purchase-cart .table {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .purchase-cart .table thead th {
            background-color: var(--snd-surface-soft);
            border-bottom: 2px solid var(--snd-border);
            font-weight: 600;
            color: var(--snd-ink-soft);
            padding: 0.5rem;
        }

        .purchase-cart .table tbody td {
            vertical-align: middle;
            padding: 0.5rem;
        }

        .purchase-cart .form-control-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        /* Status Radio Buttons */
        .status-selector {
            background: var(--snd-surface-soft);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .status-selector .form-check-label {
            font-size: 0.875rem;
            margin-left: 5px;
        }

        /* Supplier & Date Section */
        .purchase-info {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }

        /* Total Section */
        .purchase-total {
            background: linear-gradient(135deg, var(--snd-primary-deep) 0%, var(--snd-button-primary-hover) 100%);
            color: var(--snd-button-primary-text);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(103,88,189,.24);
        }

        .purchase-total .amount {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Action Buttons */
        .purchase-actions .btn {
            font-weight: 600;
            padding: 10px;
            border-radius: 6px;
        }

        /* Search Box */
        .product-search {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            padding-bottom: 10px;
        }

        .product-search input {
            border: 2px solid var(--snd-border-strong);
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 1rem;
        }

        .product-search input:focus {
            border-color: var(--snd-button-primary);
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }

        /* Cart Card */
        .cart-card {
            position: sticky;
            top: 20px;
        }

        /* Scrollbar Styling */
        .order-product::-webkit-scrollbar {
            width: 8px;
        }

        .order-product::-webkit-scrollbar-track {
            background: var(--snd-workspace-deep);
            border-radius: 10px;
        }

        .order-product::-webkit-scrollbar-thumb {
            background: var(--snd-primary-deep);
            border-radius: 10px;
        }

        .order-product::-webkit-scrollbar-thumb:hover {
            background: var(--snd-primary-hover);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .order-product {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 10px;
            }

            .order-product .item {
                padding: 10px;
            }

            .order-product .item img {
                height: 100px;
            }
        }
    </style>
@endpush
