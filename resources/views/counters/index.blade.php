@extends('layouts.admin')
@section('title', 'Counters')
@section('content-header', 'Counters')

@section('content-actions')
    @can('counters.create')
        <a href="{{ route('counters.create') }}" class="btn btn-primary">
            <x-snd-icon name="plus" class="mr-1" /> Add Counter
        </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Branch filter --}}
        <form method="GET" action="{{ route('counters.index') }}" class="snd-index-filter">
            <div class="form-group">
                <label for="counter-branch-filter">Branch</label>
                <select id="counter-branch-filter" name="branch_id" class="form-control">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            @if(request('branch_id'))
                <a href="{{ route('counters.index') }}" class="btn btn-secondary btn-sm">Clear</a>
            @endif
        </form>

        <div class="table-responsive snd-table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Branch</th>
                    <th>Counter Name</th>
                    <th>Code</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($counters as $counter)
                <tr>
                    <td>{{ $counter->id }}</td>
                    <td>
                        <span style="font-size:0.82rem;color:#0ea5b0;font-weight:600;">
                            {{ $counter->branch->name ?? '—' }}
                        </span>
                    </td>
                    <td style="font-weight:600;color:#0d3b45;">{{ $counter->name }}</td>
                    <td>
                        <code style="background:#f0f9fa;color:#0ea5b0;padding:2px 8px;border-radius:5px;font-size:0.78rem;">
                            {{ $counter->code }}
                        </code>
                    </td>
                    <td><span class="badge badge-secondary">{{ $counter->users_count }}</span></td>
                    <td>
                        @if($counter->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @can('counters.edit')
                            <a href="{{ route('counters.edit', $counter) }}" class="btn btn-sm btn-primary" title="Edit">
                                <x-snd-icon name="square-pen" />
                            </a>
                        @endcan
                        @can('counters.delete')
                            <button class="btn btn-sm btn-danger btn-delete"
                                    data-url="{{ route('counters.destroy', $counter) }}"
                                    title="Delete">
                                <x-snd-icon name="trash" />
                            </button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr class="snd-empty-row">
                    <td colspan="7" class="snd-table-empty-cell">
                        <div class="snd-table-empty">
                            <span class="snd-table-empty-icon">
                                <x-snd-icon name="monitor" />
                            </span>
                            <p>No counters found.</p>
                            <a href="{{ route('counters.create') }}" class="btn btn-primary">
                                <x-snd-icon name="plus" /> Create one
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <x-snd-pagination :paginator="$counters" />
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
        title: 'Delete counter?',
        text: 'This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        confirmButtonColor: '#ef4444',
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
