@extends('layouts.admin')

@section('title', 'Categories')
@section('content-header', 'Categories')
@section('content-actions')
<a href="{{ route('categories.create') }}" class="btn btn-primary"><x-snd-icon name="plus" /> Create Category</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Services</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ $category->services_count }}</td>
                    <td>{{ $category->sort_order }}</td>
                    <td>
                        <span class="right badge badge-{{ $category->status ? 'success' : 'danger' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>{{ $category->created_at }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary"><x-snd-icon name="square-pen" /></a>
                        <button class="btn btn-danger btn-delete" data-url="{{ route('categories.destroy', $category) }}"><x-snd-icon name="trash" /></button>
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="8" class="snd-table-empty-cell">
                        <x-snd-empty-state
                            icon="list"
                            message="No categories found."
                            :url="route('categories.create')"
                            action-label="Create First Category"
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$categories" />
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
                title: 'Delete category?',
                text: 'Services in this category will become uncategorized.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
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
                    }
                });
            });
        });
    });
</script>
@endsection
