@extends('layouts.admin')

@section('title', 'Permissions')
@section('content-header', 'Permissions')

@section('content')
@forelse($permissions->groupBy('group_name') as $group => $groupPerms)
<div class="card mb-3">
    <div class="card-header d-flex align-items-center" style="gap: 8px;">
        <span style="width:8px;height:8px;border-radius:50%;background:var(--snd-primary);display:inline-block;"></span>
        <span style="text-transform:capitalize;font-weight:700;color:var(--snd-ink);">{{ $group ?? 'General' }}</span>
        <span class="badge badge-primary ml-1">{{ $groupPerms->count() }}</span>
    </div>
    <div class="card-body" style="padding:14px 18px;">
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            @foreach($groupPerms as $permission)
            <div style="background:var(--snd-surface-soft, #f8fafc);border:1px solid var(--snd-border);border-radius:8px;padding:6px 14px;">
                <span style="font-size:0.8rem;font-weight:600;color:var(--snd-primary-deep);">{{ $permission->name }}</span>
                @if($permission->description)
                    <span style="font-size:0.75rem;color:var(--snd-muted);margin-left:6px;">— {{ $permission->description }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body">
        <x-snd-empty-state icon="shield" message="No permissions found." />
    </div>
</div>
@endforelse
@endsection
