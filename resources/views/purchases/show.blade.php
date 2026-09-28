@extends('layouts.admin')

@section('title', 'Purchase Details')

@section('content-eyebrow', __('Purchases'))
@section('content-header', __('Purchase') . ' #' . $purchase->id)
@section('content-actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary"><x-snd-icon name="arrow-left" class="mr-1" />{{ __('Back') }}</a>
    <a href="{{ route('purchases.receipt', $purchase) }}" class="btn btn-success" target="_blank"><x-snd-icon name="printer" class="mr-1" />{{ __('Print Receipt') }}</a>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Purchase Info -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <x-snd-icon name="info" /> {{ __('Purchase Information') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">{{ __('Purchase ID') }}</dt>
                            <dd class="col-sm-7">#{{ $purchase->id }}</dd>

                            <dt class="col-sm-5">{{ __('Date') }}</dt>
                            <dd class="col-sm-7">{{ $purchase->purchase_date->format('d M Y') }}</dd>

                            <dt class="col-sm-5">{{ __('Status') }}</dt>
                            <dd class="col-sm-7">
                                @if($purchase->status === 'completed')
                                    <span class="badge badge-success">
                                    <x-snd-icon name="circle-check" /> {{ __('Completed') }}
                                </span>
                                @elseif($purchase->status === 'pending')
                                    <span class="badge badge-warning">
                                    <x-snd-icon name="clock" /> {{ __('Pending') }}
                                </span>
                                @else
                                    <span class="badge badge-danger">
                                    <x-snd-icon name="circle-x" /> {{ __('Cancelled') }}
                                </span>
                                @endif
                            </dd>

                            <dt class="col-sm-5">{{ __('Total Amount') }}</dt>
                            <dd class="col-sm-7">
                                <strong class="text-lg">{{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount, 2) }}</strong>
                            </dd>

                            <dt class="col-sm-5">{{ __('Created By') }}</dt>
                            <dd class="col-sm-7">{{ $purchase->user->name ?? 'N/A' }}</dd>

                            <dt class="col-sm-5">{{ __('Created At') }}</dt>
                            <dd class="col-sm-7">
                                <small class="text-muted">{{ $purchase->created_at->format('d M Y, H:i') }}</small>
                            </dd>
                        </dl>

                        @if($purchase->notes)
                            <hr>
                            <p class="mb-0">
                                <strong>{{ __('Notes') }}:</strong><br>
                                <small>{{ $purchase->notes }}</small>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Supplier Info -->
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <x-snd-icon name="truck" /> {{ __('Supplier Information') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($purchase->supplier)
                            <dl class="row mb-0">
                                <dt class="col-sm-5">{{ __('Name') }}</dt>
                                <dd class="col-sm-7">{{ $purchase->supplier->first_name }} {{ $purchase->supplier->last_name }}</dd>

                                @if($purchase->supplier->email)
                                    <dt class="col-sm-5">{{ __('Email') }}</dt>
                                    <dd class="col-sm-7">
                                        <a href="mailto:{{ $purchase->supplier->email }}">{{ $purchase->supplier->email }}</a>
                                    </dd>
                                @endif

                                @if($purchase->supplier->phone)
                                    <dt class="col-sm-5">{{ __('Phone') }}</dt>
                                    <dd class="col-sm-7">
                                        <a href="tel:{{ $purchase->supplier->phone }}">{{ $purchase->supplier->phone }}</a>
                                    </dd>
                                @endif

                                @if($purchase->supplier->address)
                                    <dt class="col-sm-5">{{ __('Address') }}</dt>
                                    <dd class="col-sm-7">
                                        <small>{{ $purchase->supplier->address }}</small>
                                    </dd>
                                @endif
                            </dl>
                        @else
                            <p class="text-muted">{{ __('No supplier information available') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Purchase Items -->
            <div class="col-md-8">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <x-snd-icon name="shopping-bag" /> {{ __('Purchase Items') }}
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th width="60">{{ __('Image') }}</th>
                                    <th>{{ __('Product Name') }}</th>
                                    <th width="100" class="text-center">{{ __('Quantity') }}</th>
                                    <th width="120" class="text-right">{{ __('Unit Price') }}</th>
                                    <th width="120" class="text-right">{{ __('Subtotal') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($purchase->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->product)
                                                <img src="{{ $item->product->image_url }}"
                                                     alt="{{ $item->product->name }}"
                                                     class="img-thumbnail"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary" style="width: 50px; height: 50px;"></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->product)
                                                <strong>{{ $item->product->name }}</strong><br>
                                                <small class="text-muted">{{ $item->product->barcode }}</small>
                                            @else
                                                <span class="text-muted">{{ __('Product not found') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info badge-lg">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-right">
                                            {{ config('settings.currency_symbol') }}{{ number_format($item->purchase_price, 2) }}
                                        </td>
                                        <td class="text-right">
                                            <strong>{{ config('settings.currency_symbol') }}{{ number_format($item->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            {{ __('No items in this purchase') }}
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                @if($purchase->items->count() > 0)
                                    <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="4" class="text-right">
                                            <strong>{{ __('Total') }}:</strong>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-lg text-success">
                                                {{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
