@extends('layouts.admin')
@section('title', 'Create Branch')
@section('content-header', 'Create Branch')

@section('content-actions')
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('branches.store') }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="git-branch" />
                        <div>
                            <h3>Branch Details</h3>
                            <p>Set up a {{ \App\Helpers\CommonHelper::getBrandName() }} location and its access controls.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name <span style="color:#c9342d">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. Main Branch">
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code <span style="color:#c9342d">*</span></label>
                                <input type="text" name="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" placeholder="e.g. MB01"
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
                                       value="{{ old('phone') }}" placeholder="+92 300 0000000">
                                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:32px;">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           style="accent-color:var(--snd-primary-deep);width:16px;height:16px;">
                                    <span style="font-weight:500;">Branch is Active</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="2"
                                  class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Full address">{{ old('address') }}</textarea>
                        @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    {{-- Password section --}}
                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading">
                            <x-snd-icon name="lock" />Branch POS Password
                        </div>
                        <p class="snd-form-subsection-description">
                            Cashiers must enter this password before accessing the POS sale screen for this branch.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span style="color:#c9342d">*</span></label>
                                    <input type="password" name="password" id="branch_password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 4 characters" autocomplete="new-password">
                                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirm Password <span style="color:#c9342d">*</span></label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control" placeholder="Repeat password"
                                           autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="snd-form-toggle">
                            <label>
                                <input type="checkbox" id="show_pass" style="accent-color:var(--snd-primary-deep);">
                                Show passwords
                            </label>
                        </div>
                    </div>

                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading">
                            <x-snd-icon name="file-text" />Branch SRB Registration
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>SRB POS Registration ID <span style="color:#c9342d">*</span></label><input type="number" min="1" name="srb_pos_id" class="form-control @error('srb_pos_id') is-invalid @enderror" value="{{ old('srb_pos_id') }}">@error('srb_pos_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>SRB POS User <span style="color:#c9342d">*</span></label><input type="text" name="srb_pos_user" class="form-control @error('srb_pos_user') is-invalid @enderror" value="{{ old('srb_pos_user') }}">@error('srb_pos_user')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                            <div class="col-md-4"><div class="form-group"><label>SRB POS Password <span style="color:#c9342d">*</span></label><input type="password" name="srb_pos_password" class="form-control @error('srb_pos_password') is-invalid @enderror" value="{{ old('srb_pos_password') }}" autocomplete="new-password">@error('srb_pos_password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div></div>
                        </div>
                    </div>

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="save" class="mr-1" /> Create Branch
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
document.getElementById('show_pass').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';
    document.getElementById('branch_password').type = type;
    document.querySelector('[name="password_confirmation"]').type = type;
});
// Auto-uppercase code
document.querySelector('[name="code"]').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
</script>
@endsection
