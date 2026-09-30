@extends('layouts.admin')

@section('title', 'Services')
@section('content-header', 'Services')
@section('content-actions')
<a href="{{ route('services.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create Service</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card snd-order-filter-card snd-filter-card-single mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('services.index') }}" class="snd-order-filter-form">
            <div class="snd-order-filter-grid">
                <div class="form-group mb-0">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
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
                    <th>Name</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Barcode</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->category?->name ?? '—' }}</td>
                    <td>
                        <img class="product-img" src="{{ $service->image_url }}" alt="{{ $service->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                    </td>
                    <td>{{ $service->barcode ?? '—' }}</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($service->rate, 2) }}</td>
                    <td>
                        <span class="right badge badge-{{ $service->status ? 'success' : 'danger' }}">{{ $service->status ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>{{ $service->created_at }}</td>
                    <td>
                        <a href="{{ route('services.edit', $service) }}" class="btn btn-primary"><x-snd-icon name="square-pen" /></a>
                        <button class="btn btn-danger btn-delete" data-url="{{ route('services.destroy', $service) }}"><x-snd-icon name="trash" /></button>
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="9" class="snd-table-empty-cell">
                        <x-snd-empty-state
                            icon="briefcase"
                            message="No services found."
                            :url="route('services.create')"
                            action-label="Create First Service"
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$services" />
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
                title: 'Delete service?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            });
        });
    });
</script>
@endsection
