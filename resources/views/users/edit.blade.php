@extends('layouts.admin')
@section('title', 'Edit User — ' . $user->getFullname())
@section('content-header', 'Edit User')

@section('content-actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('users.update', $user) }}" method="POST" id="user-form">
                    @csrf
                    @method('PUT')

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="square-pen" />
                        <div>
                            <h3>Basic Details</h3>
                            <p>Update the operator profile and sign-in details for {{ \App\Helpers\CommonHelper::getBrandName() }}.</p>
                        </div>
                        <span class="snd-form-identity-badge">{{ $user->getFullname() }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $user->first_name) }}">
                                @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $user->last_name) }}">
                                @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span style="color:#ef4444">*</span></label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}">
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>New Password <small style="color:#9ca3af;">(leave blank to keep)</small></label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 characters">
                                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group snd-form-toggle">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   style="accent-color:#0ea5b0;width:16px;height:16px;">
                            Active (user can log in)
                        </label>
                    </div>

                    @can('users.assign-roles')
                    <div class="snd-form-section-heading mt-4">
                        <x-snd-icon name="shield" />
                        <div>
                            <h3>Access & Permissions</h3>
                            <p>Assign roles and direct permissions for this POS user.</p>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>Roles</label>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                            @foreach($roles as $role)
                            <label class="assign-chip">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                       {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                       style="accent-color:#0ea5b0;">
                                {{ $role->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                    @can('users.assign-roles')
                    <div class="form-group mt-3">
                        <label>Direct Permissions</label>
                        @foreach($permissions->groupBy('group_name') as $group => $groupPermissions)
                        <div class="mb-3">
                            <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#0ea5b0;margin-bottom:6px;">
                                {{ $group ?? 'General' }}
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                                @foreach($groupPermissions as $permission)
                                <label class="assign-chip">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                           {{ in_array($permission->id, old('permissions', $assignedPermissionIds)) ? 'checked' : '' }}
                                           style="accent-color:#0ea5b0;">
                                    {{ $permission->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endcan

                    {{-- ── Branch & Counter Assignment ────────────────── --}}
                    <div class="snd-form-section-heading mt-4">
                        <x-snd-icon name="git-branch" />
                        <div>
                            <h3>Branch & Counter Access</h3>
                            <p>Control which locations and tills this user can open in the POS.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Assign Branches</label>
                        <p>
                            Checking a branch grants the user access to its POS screen after password verification.
                        </p>
                        @if($branches->isEmpty())
                            <div class="snd-empty-form-state">
                                No active branches. <a href="{{ route('branches.create') }}">Create one</a>.
                            </div>
                        @else
                        <div style="display:flex;flex-direction:column;gap:6px;" id="branch-list">
                            @foreach($branches as $branch)
                            <label class="assign-row branch-row">
                                <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                       {{ in_array($branch->id, old('branches', $assignedBranchIds)) ? 'checked' : '' }}
                                       class="branch-check"
                                       style="accent-color:#0ea5b0;width:15px;height:15px;flex-shrink:0;">
                                <div>
                                    <span style="font-weight:600;font-size:0.84rem;color:#0d3b45;">{{ $branch->name }}</span>
                                    <code style="background:#f0f9fa;color:#0ea5b0;padding:1px 7px;border-radius:4px;font-size:0.74rem;margin-left:6px;">{{ $branch->code }}</code>
                                    <span style="font-size:0.74rem;color:#9ca3af;margin-left:6px;">{{ $branch->counters->count() }} counter(s)</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="form-group mt-3" id="counter-section"
                         style="{{ count(old('branches', $assignedBranchIds)) ? '' : 'display:none;' }}">
                        <label>Assign Counters</label>
                        <p>
                            Only counters belonging to the selected branches are shown.
                        </p>
                        <div id="counter-list" style="display:flex;flex-direction:column;gap:6px;"></div>
                    </div>

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="save" class="mr-1" /> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
const allCounters     = @json($allCounters);
const savedCounters   = @json(old('counters', $assignedCounterIds));

function refreshCounters() {
    const checkedBranches = [...document.querySelectorAll('.branch-check:checked')]
        .map(el => parseInt(el.value));

    const counterList    = document.getElementById('counter-list');
    const counterSection = document.getElementById('counter-section');
    counterList.innerHTML = '';

    let available = [];
    checkedBranches.forEach(bid => {
        if (allCounters[bid]) available = available.concat(allCounters[bid]);
    });

    if (available.length === 0) {
        counterSection.style.display = 'none';
        return;
    }

    counterSection.style.display = 'block';
    available.forEach(c => {
        const checked = savedCounters.includes(c.id) ? 'checked' : '';
        counterList.insertAdjacentHTML('beforeend', `
            <label class="assign-row counter-row">
                <input type="checkbox" name="counters[]" value="${c.id}" ${checked}
                       class="counter-check"
                       style="accent-color:#0ea5b0;width:15px;height:15px;flex-shrink:0;">
                <div>
                    <span style="font-weight:600;font-size:0.84rem;color:#0d3b45;">${c.name}</span>
                    <code style="background:#f0f9fa;color:#0ea5b0;padding:1px 7px;border-radius:4px;font-size:0.74rem;margin-left:6px;">${c.code}</code>
                </div>
            </label>`);
    });

    document.querySelectorAll('.counter-check').forEach(applyHighlight);
}

function applyHighlight(cb) {
    const row = cb.closest('.assign-row');
    if (!row) return;
    const update = () => row.style.borderColor = cb.checked ? '#0ea5b0' : '#e2e8f0';
    update();
    cb.addEventListener('change', update);
}

document.querySelectorAll('.branch-check').forEach(cb => {
    const row = cb.closest('.assign-row');
    const update = () => { if (row) row.style.borderColor = cb.checked ? '#0ea5b0' : '#e2e8f0'; };
    update();
    cb.addEventListener('change', () => { update(); refreshCounters(); });
});

refreshCounters();
</script>

<style>
.assign-chip {
    display:flex;align-items:center;gap:6px;background:#f8fafc;
    border:1px solid #e2e8f0;border-radius:8px;padding:6px 14px;
    cursor:pointer;font-size:0.84rem;font-weight:500;color:#374151;margin:0;
    transition:border-color 0.12s;
}
.assign-row {
    display:flex;align-items:center;gap:10px;padding:8px 12px;
    border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;
    transition:border-color 0.12s;margin:0;
}
</style>
@endsection
