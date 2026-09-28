@extends('layouts.admin')
@section('title', 'Create Counter')
@section('content-header', 'Create Counter')

@section('content-actions')
    <a href="{{ route('counters.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('counters.store') }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="monitor" />
                        <div>
                            <h3>Counter Details</h3>
                            <p>Set up a checkout counter and its POS access password.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Branch <span style="color:#ef4444">*</span></label>
                        <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
                            <option value="">— Select Branch —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                                       value="{{ old('name') }}" placeholder="e.g. Counter 1">
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code <span style="color:#ef4444">*</span></label>
                                <input type="text" name="code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" placeholder="e.g. C1"
                                       style="text-transform:uppercase;">
                                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   style="accent-color:#0ea5b0;width:16px;height:16px;">
                            <span style="font-weight:500;">Counter is Active</span>
                        </label>
                    </div>

                    <div class="snd-form-subsection">
                        <div class="snd-form-subsection-heading">
                            <x-snd-icon name="lock" />Counter POS Password
                        </div>
                        <p class="snd-form-subsection-description">
                            After verifying the branch password, cashiers must enter this counter password.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password <span style="color:#ef4444">*</span></label>
                                    <input type="password" name="password" id="counter_password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 4 characters" autocomplete="new-password">
                                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Confirm Password <span style="color:#ef4444">*</span></label>
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
                            <x-snd-icon name="save" class="mr-1" /> Create Counter
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
    document.getElementById('counter_password').type = type;
    document.querySelector('[name="password_confirmation"]').type = type;
});
document.querySelector('[name="code"]').addEventListener('input', function () {
    this.value = this.value.toUpperCase();
});
</script>
@endsection
