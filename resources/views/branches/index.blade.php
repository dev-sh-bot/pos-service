@extends('layouts.admin')
@section('title', 'Branches')
@section('content-header', 'Branches')

@section('content-actions')
    @can('branches.create')
        <a href="{{ route('branches.create') }}" class="btn btn-primary">
            <x-snd-icon name="plus" class="mr-1" /> Add Branch
        </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Counters</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>
                        <span style="font-weight:600;color:#0d3b45;">{{ $branch->name }}</span>
                    </td>
                    <td>
                        <code style="background:#f0f9fa;color:#0ea5b0;padding:2px 8px;border-radius:5px;font-size:0.78rem;">
                            {{ $branch->code }}
                        </code>
                    </td>
                    <td style="color:#6b7280;font-size:0.84rem;">{{ $branch->phone ?? '—' }}</td>
                    <td style="color:#6b7280;font-size:0.84rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $branch->address ?? '—' }}
                    </td>
                    <td><span class="badge badge-primary">{{ $branch->counters_count }}</span></td>
                    <td><span class="badge badge-secondary">{{ $branch->users_count }}</span></td>
                    <td>
                        @if($branch->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @can('branches.edit')
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-primary" title="Edit">
                                <x-snd-icon name="square-pen" />
                            </a>
                        @endcan
                        @can('branches.delete')
                            <button class="btn btn-sm btn-danger btn-delete"
                                    data-url="{{ route('branches.destroy', $branch) }}"
                                    title="Delete">
                                <x-snd-icon name="trash" />
                            </button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="9" class="snd-table-empty-cell">
                        <div class="snd-table-empty">
                            <span class="snd-table-empty-icon">
                                <x-snd-icon name="git-branch" />
                            </span>
                            <p>No branches found.</p>
                            <a href="{{ route('branches.create') }}" class="btn btn-primary">
                                <x-snd-icon name="plus" /> Create one
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$branches" />
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;
    if (!$) return;

    $(document).on('click', '.btn-delete', function () {
    const url = $(this).data('url');
    Swal.fire({
        title: 'Delete branch?',
        text: 'All counters in this branch will also be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(r => {
        if (r.isConfirmed) {
            $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                .done(() => location.reload())
                .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Could not delete.', 'error'));
        }
    });
    });
});
</script>
@endsection
