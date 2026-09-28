@extends('layouts.admin')

@section('title', 'Roles')
@section('content-header', 'Roles')

@section('content-actions')
    @can('roles.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <x-snd-icon name="plus" class="mr-1" /> Add Role
        </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
        <table class="table" id="roles-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>
                        <span class="font-weight-600" style="color:#0d3b45;">{{ $role->name }}</span>
                        @if($role->is_system)
                            <span class="badge badge-info ml-1">System</span>
                        @endif
                    </td>
                    <td><code style="background:#f0f9fa;color:#0ea5b0;padding:2px 7px;border-radius:5px;font-size:0.78rem;">{{ $role->slug }}</code></td>
                    <td style="color:#6b7280;font-size:0.84rem;">{{ $role->description ?? '—' }}</td>
                    <td>
                        <span class="badge badge-primary">{{ $role->permissions_count ?? $role->permissions->count() }}</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $role->users_count ?? '—' }}</span>
                    </td>
                    <td style="font-size:0.82rem;color:#9ca3af;">{{ $role->created_at->format('d M Y') }}</td>
                    <td>
                        @can('roles.edit')
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-primary" title="Edit">
                                <x-snd-icon name="square-pen" />
                            </a>
                        @endcan
                        @can('roles.delete')
                            @unless($role->is_system)
                                <button class="btn btn-sm btn-danger btn-delete"
                                        data-url="{{ route('roles.destroy', $role) }}"
                                        title="Delete">
                                    <x-snd-icon name="trash" />
                                </button>
                            @endunless
                        @endcan
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="8" class="snd-table-empty-cell">
                        <x-snd-empty-state
                            icon="shield-check"
                            message="No roles found."
                            :url="auth()->user()->can('roles.create') ? route('roles.create') : null"
                            action-label="Add First Role"
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$roles" />
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
        title: 'Delete role?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (result.isConfirmed) {
            $.post(url, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                .done(() => location.reload())
                .fail(xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Could not delete.', 'error'));
        }
    });
    });
});
</script>
@endsection
