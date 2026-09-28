@extends('layouts.admin')

@section('title', 'Deals Management')
@section('content-header', 'Deals Management')
@section('content-actions')
<a href="{{ route('deals.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create New Deal</a>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
<style>
.badge-discount { background: var(--snd-success-soft, #eaf9ee); color: #15803d; font-weight: 700; font-size: 0.78rem; padding: 4px 10px; border-radius: 20px; }
.badge-service-chip { background: var(--snd-primary-soft, #f0edff); color: var(--snd-primary-deep, #6758bd); border: 1px solid var(--snd-border); font-size: 0.73rem; font-weight: 600; border-radius: 12px; padding: 2px 8px; margin: 2px; display: inline-block; }
.original-price { text-decoration: line-through; color: var(--snd-muted); font-weight: 600; font-size: 0.85rem; margin-right: 6px; }
.discounted-price { color: var(--snd-primary-deep, #6758bd); font-weight: 800; font-size: 1rem; }
</style>
@endsection

@section('content')
<div class="card snd-order-filter-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('deals.index') }}" class="snd-order-filter-form">
            <div class="snd-order-filter-grid">
                <div class="form-group mb-0">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search deals by name or barcode..." value="{{ request('search') }}">
                </div>
                <div class="snd-order-filter-action">
                    <button type="submit" class="btn btn-primary"><x-snd-icon name="search" /> Search</button>
                    @if(request('search'))
                        <a href="{{ route('deals.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Deal Name</th>
                        <th>Included Services</th>
                        <th>Before Price</th>
                        <th>After Discount</th>
                        <th>Discount Savings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deals as $deal)
                    <tr>
                        <td>#{{ $deal->id }}</td>
                        <td>
                            <div class="font-weight-bold">{{ $deal->name }}</div>
                            @if($deal->barcode)
                                <small class="text-muted">{{ $deal->barcode }}</small>
                            @endif
                        </td>
                        <td>
                            <div style="max-width: 280px;">
                                @forelse($deal->services as $srv)
                                    <span class="badge-service-chip">{{ $srv->name }}</span>
                                @empty
                                    <span class="text-muted small">No services assigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td><span class="original-price">{{ config('settings.currency_symbol') }} {{ number_format($deal->original_amount, 2) }}</span></td>
                        <td><span class="discounted-price">{{ config('settings.currency_symbol') }} {{ number_format($deal->discounted_amount, 2) }}</span></td>
                        <td>
                            @if($deal->discount_percentage > 0)
                                <span class="badge-discount">{{ number_format($deal->discount_percentage, 0) }}% OFF</span>
                            @else
                                <span class="badge badge-light">Standard</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $deal->status ? 'success' : 'danger' }}">
                                {{ $deal->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('deals.edit', $deal) }}" class="btn btn-primary"><x-snd-icon name="square-pen" /></a>
                            <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('deals.destroy', $deal) }}"><x-snd-icon name="trash" /></button>
                        </td>
                    </tr>
                    @empty
                    <tr class="snd-empty-row">
                        <td colspan="8" class="snd-table-empty-cell">
                            <x-snd-empty-state
                                icon="tags"
                                message="No deals found."
                                :url="route('deals.create')"
                                action-label="Create First Deal"
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-snd-pagination :paginator="$deals" />
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.btn-delete');
            if (!trigger) return;

            event.preventDefault();
            const url = trigger.dataset.url;

            Swal.fire({
                title: 'Delete Deal?',
                text: 'Are you sure you want to delete this deal package?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete deal',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(response => response.json())
                .then((res) => {
                    if (res.success) {
                        trigger.closest('tr').remove();
                        Swal.fire('Deleted!', 'Deal has been removed successfully.', 'success');
                    }
                });
            });
        });
    });
</script>
@endsection
