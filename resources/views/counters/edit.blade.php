@extends('layouts.admin')
@section('title', 'Edit Counter — ' . $counter->name)
@section('content-header', 'Edit Counter')

@section('content-actions')
    <a href="{{ route('counters.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    {{-- Left: counter details --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('counters.update', $counter) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="receipt" />
                        <div>
                            <h3>Counter Details</h3>
                            <p>Update this checkout counter and its POS access password.</p>
                        </div>
                        <span class="snd-form-identity-badge">{{ $counter->branch->name ?? '—' }}</span>
                    </div>

                    <div class="form-group">
                        <label>Branch <span style="color:#ef4444">*</span></label>
                        <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ old('branch_id', $counter->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Counter Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $counter->name) }}">
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code <span style="color:#ef4444">*</span></label>
                                <input type="text" name="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $counter->code) }}"
                                       style="text-transform:uppercase;">
                                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $counter->is_active) ? 'checked' : '' }}
                                   style="accent-color:#0ea5b0;width:16px;height:16px;">
                            <span style="font-weight:500;">Counter is Active</span>
                        </label>
                    </div>

                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading">
                            <x-snd-icon name="lock" />Change POS Password
                        </div>
                        <p class="snd-form-subsection-description">Leave blank to keep current password.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="password" id="counter_password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 4 characters" autocomplete="new-password">
                                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <label class="snd-form-toggle">
                            <input type="checkbox" id="show_pass" style="accent-color:#0ea5b0;">
                            Show passwords
                        </label>
                    </div>

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="save" class="mr-1" /> Update Counter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: assign users --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <x-snd-icon name="users" class="mr-2" style="color:#0ea5b0;" />Assign Users
                <span class="badge badge-primary ml-2">{{ $counter->users_count }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('counters.assign-users', $counter) }}" method="POST">
                    @csrf
                    <p style="font-size:0.82rem;color:#6b7280;margin-bottom:12px;">
                        Only users assigned to <strong>{{ $counter->branch->name ?? 'this branch' }}</strong> are shown.
                    </p>
                    @if($allUsers->isEmpty())
                        <div style="text-align:center;color:#9ca3af;padding:20px 0;font-size:0.84rem;">
                            <x-snd-icon name="info" class="mr-1" />
                            No users are assigned to this branch yet.
                            <br>
                            <a href="{{ route('branches.edit', $counter->branch_id) }}" class="mt-1 d-inline-block">
                                Assign users to the branch first
                            </a>
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;gap:6px;max-height:320px;overflow-y:auto;">
                            @foreach($allUsers as $user)
                            @php $isBranchUser = in_array($counter->branch_id, $user->branches->pluck('id')->toArray()); @endphp
                            <label style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;cursor:{{ $isBranchUser ? 'pointer' : 'not-allowed' }};opacity:{{ $isBranchUser ? '1' : '0.45' }};"
                                   class="user-assign-row">
                                <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                       {{ in_array($user->id, $assignedUsers) ? 'checked' : '' }}
                                       {{ !$isBranchUser ? 'disabled' : '' }}
                                       style="accent-color:#0ea5b0;width:15px;height:15px;flex-shrink:0;">
                                <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#0ea5b0,#0d3b45);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:600;flex-shrink:0;">
                                    {{ strtoupper(substr($user->first_name,0,1)) }}
                                </div>
                                <div>
                                    <div style="font-size:0.84rem;font-weight:600;color:#0d3b45;">{{ $user->getFullname() }}</div>
                                    <div style="font-size:0.75rem;color:#9ca3af;">{{ $user->email }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" style="width:100%;">
                        <x-snd-icon name="save" class="mr-1" /> Save User Assignment
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.getElementById('show_pass').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('counter_password').type = type;
    document.querySelector('[name="password_confirmation"]').type = type;
});
document.querySelector('[name="code"]').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
document.querySelectorAll('.user-assign-row input[type="checkbox"]:not([disabled])').forEach(cb => {
    const row = cb.closest('.user-assign-row');
    const update = () => row.style.borderColor = cb.checked ? '#0ea5b0' : '#e2e8f0';
    update();
    cb.addEventListener('change', update);
});
</script>
@endsection
