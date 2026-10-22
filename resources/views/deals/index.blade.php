@extends('layouts.admin')

@section('title', 'Deals Management')
@section('content-header', 'Deals Management')
@section('content-subtitle', 'Bundle services and manage your promotional pricing.')
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
.deals-list-card { overflow: hidden; }
.deals-list-toolbar { display: grid; grid-template-columns: minmax(220px, .7fr) minmax(0, 1.3fr); align-items: center; gap: 24px; padding: 16px 20px; background: var(--snd-surface-solid, #fff); border-bottom: 1px solid var(--snd-border, #e5e7eb); }
.deals-list-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
.deals-list-icon { display: grid; place-items: center; flex: 0 0 40px; width: 40px; height: 40px; border-radius: 12px; color: var(--snd-primary-deep, #6758bd); background: var(--snd-primary-soft, #f0edff); }
.deals-list-heading h2 { margin: 0; color: var(--snd-ink, #202033); font-size: .9rem; font-weight: 700; }
.deals-list-heading p { margin: 3px 0 0; color: var(--snd-muted, #697386); font-size: .72rem; }
.deals-search { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; justify-self: stretch; gap: 10px; width: 100%; max-width: none; min-width: 0; margin: 0; }
.deals-search-field { min-width: 0; margin: 0; }
.deals-search-field label { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
.deals-search-field .form-control { min-height: 40px; border-color: var(--snd-border-strong, #d7d9e2); border-radius: 10px; }
.deals-search-actions { display: flex; align-items: center; gap: 8px; }
.deals-search-actions .btn { min-height: 40px; white-space: nowrap; }
.deals-empty-state { display: grid; justify-items: center; gap: 10px; min-height: 280px; padding: 42px 20px; text-align: center; }
.deals-empty-icon { display: grid; place-items: center; width: 54px; height: 54px; border: 1px solid var(--snd-primary-border, #ddd6fe); border-radius: 16px; color: var(--snd-primary-deep, #6758bd); background: var(--snd-primary-soft, #f0edff); font-size: 1.2rem; }
.deals-empty-state h2 { margin: 2px 0 0; color: var(--snd-ink, #202033); font-size: 1rem; font-weight: 700; }
.deals-empty-state p { max-width: 440px; margin: 0; color: var(--snd-muted, #697386); font-size: .78rem; line-height: 1.6; }
.deals-empty-state .btn { margin-top: 4px; }
@media (max-width: 991.98px) {
    .deals-list-toolbar { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 14px; }
    .deals-search { max-width: none; }
}
@media (max-width: 575.98px) {
    .deals-search { grid-template-columns: minmax(0, 1fr); }
    .deals-search-actions { width: 100%; }
    .deals-search-actions .btn { flex: 1; }
    .deals-empty-state { min-height: 240px; padding: 32px 16px; }
}
</style>
@endsection

@section('content')
<div class="card deals-list-card">
    <div class="card-header deals-list-toolbar">
        <div class="deals-list-heading">
            <span class="deals-list-icon"><x-snd-icon name="tags" /></span>
            <div>
                <h2>Deals catalog</h2>
                <p>Search and manage your service bundles</p>
            </div>
        </div>
        <form method="GET" action="{{ route('deals.index') }}" class="deals-search">
            <div class="form-group deals-search-field">
                <label for="search">Search deals</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by deal name or barcode..." value="{{ request('search') }}">
            </div>
            <div class="deals-search-actions">
                <button type="submit" class="btn btn-primary"><x-snd-icon name="search" /> Search</button>
                @if(request('search'))
                    <a href="{{ route('deals.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
    <div class="card-body">
        @if($deals->count() === 0)
            <div class="deals-empty-state">
                <span class="deals-empty-icon"><x-snd-icon name="tags" /></span>
                @if(request()->filled('search'))
                    <h2>No matching deals</h2>
                    <p>We couldn't find a deal matching “{{ request('search') }}”. Try another name or barcode.</p>
                    <a href="{{ route('deals.index') }}" class="btn btn-secondary">Clear search</a>
                @else
                    <h2>No deals yet</h2>
                    <p>Create a deal to bundle services and offer customers a clear discounted price.</p>
                    <a href="{{ route('deals.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create your first deal</a>
                @endif
            </div>
        @else
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
                    @foreach ($deals as $deal)
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
                            <a href="{{ route('deals.edit', $deal) }}" class="btn btn-primary snd-table-action"><x-snd-icon name="square-pen" /></a>
                            <button type="button" class="btn btn-danger snd-table-action snd-table-action--danger btn-delete" data-url="{{ route('deals.destroy', $deal) }}"><x-snd-icon name="trash" /></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-snd-pagination :paginator="$deals" />
        @endif
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
