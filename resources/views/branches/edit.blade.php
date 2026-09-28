@extends('layouts.admin')
@section('title', 'Edit Branch — ' . $branch->name)
@section('content-header', 'Edit Branch')

@section('content-actions')
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    {{-- Left: branch details --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('branches.update', $branch) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="git-branch" />
                        <div>
                            <h3>Branch Details</h3>
                            <p>Update the {{ \App\Helpers\CommonHelper::getBrandName() }} location and its access controls.</p>
                        </div>
                        <span class="snd-form-identity-badge">{{ $branch->code }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $branch->name) }}">
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code <span style="color:#ef4444">*</span></label>
                                <input type="text" name="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $branch->code) }}"
                                       style="text-transform:uppercase;">
                                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $branch->phone) }}">
                                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:32px;">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $branch->is_active) ? 'checked' : '' }}
                                           style="accent-color:#0ea5b0;width:16px;height:16px;">
                                    <span style="font-weight:500;">Branch is Active</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="2"
                                  class="form-control @error('address') is-invalid @enderror">{{ old('address', $branch->address) }}</textarea>
                        @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading">
                            <x-snd-icon name="lock" />Change POS Password
                        </div>
                        <p class="snd-form-subsection-description">Leave blank to keep the current password.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="password" id="branch_password"
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

                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading"><x-snd-icon name="file-text" />Branch SRB Registration</div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>SRB POS Registration ID <span style="color:#ef4444">*</span></label><input type="number" min="1" name="srb_pos_id" class="form-control @error('srb_pos_id') is-invalid @enderror" value="{{ old('srb_pos_id', $branch->srb_pos_id) }}">@error('srb_pos_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>SRB POS User <span style="color:#ef4444">*</span></label><input type="text" name="srb_pos_user" class="form-control @error('srb_pos_user') is-invalid @enderror" value="{{ old('srb_pos_user', $branch->srb_pos_user) }}">@error('srb_pos_user')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>SRB POS Password</label><input type="password" name="srb_pos_password" class="form-control @error('srb_pos_password') is-invalid @enderror" placeholder="Leave blank to keep current" autocomplete="new-password">@error('srb_pos_password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                        </div>
                    </div>

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="save" class="mr-1" /> Update Branch
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
                <span class="badge badge-primary ml-2">{{ $branch->users_count }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('branches.assign-users', $branch) }}" method="POST">
                    @csrf
                    <p style="font-size:0.82rem;color:#6b7280;margin-bottom:12px;">
                        Select which users can access this branch on the POS screen.
                    </p>
                    <div style="display:flex;flex-direction:column;gap:6px;max-height:320px;overflow-y:auto;">
                        @foreach($allUsers as $user)
                        <label style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;transition:border-color 0.12s;"
                               class="user-assign-row">
                            <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                   {{ in_array($user->id, $assignedUsers) ? 'checked' : '' }}
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
    document.getElementById('branch_password').type = type;
    document.querySelector('[name="password_confirmation"]').type = type;
});
document.querySelector('[name="code"]').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
// Highlight checked rows
document.querySelectorAll('.user-assign-row input').forEach(cb => {
    const row = cb.closest('.user-assign-row');
    const update = () => row.style.borderColor = cb.checked ? '#0ea5b0' : '#e2e8f0';
    update();
    cb.addEventListener('change', update);
});
</script>
@endsection
