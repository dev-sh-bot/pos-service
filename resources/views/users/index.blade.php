@extends('layouts.admin')

@section('title', 'Users')
@section('content-header', 'Users')

@section('content-actions')
    @can('users.create')
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <x-snd-icon name="user-plus" class="mr-1" /> Add User
        </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
        <table class="table" id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#0ea5b0,#0d3b45);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:#0d3b45;font-size:0.85rem;">{{ $user->getFullname() }}</div>
                                @if($user->id === auth()->id())
                                    <span style="font-size:0.7rem;color:#0ea5b0;font-weight:600;">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="color:#6b7280;font-size:0.84rem;">{{ $user->email }}</td>
                    <td>
                        @forelse($user->roles as $role)
                            <span class="badge badge-primary mr-1">{{ $role->name }}</span>
                        @empty
                            <span style="color:#d1d5db;font-size:0.8rem;">No roles</span>
                        @endforelse
                    </td>
                    <td>
                        @can('users.activate')
                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                    style="border:none;background:none;padding:0;cursor:pointer;"
                                    title="{{ $user->is_active ? 'Click to deactivate' : 'Click to activate' }}">
                                @if($user->is_active)
                                    <span class="badge badge-success" style="font-size:0.78rem;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="font-size:0.78rem;">Inactive</span>
                                @endif
                            </button>
                        </form>
                        @else
                            @if($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        @endcan
                    </td>
                    <td style="font-size:0.82rem;color:#9ca3af;">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary" title="Edit">
                                <x-snd-icon name="square-pen" />
                            </a>
                        @endcan
                        @can('users.delete')
                            @if($user->id !== auth()->id())
                                <button class="btn btn-sm btn-danger btn-delete"
                                        data-url="{{ route('users.destroy', $user) }}"
                                        title="Delete">
                                    <x-snd-icon name="trash" />
                                </button>
                            @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="7" class="snd-table-empty-cell">
                        <x-snd-empty-state
                            icon="users"
                            message="No users found."
                            :url="auth()->user()->can('users.create') ? route('users.create') : null"
                            action-label="Add First User"
                        />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$users" />
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
        title: 'Delete user?',
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
