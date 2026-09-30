@extends('layouts.admin')

@section('title', 'Create Role')
@section('content-header', 'Create Role')

@section('content-actions')
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
        <x-snd-icon name="arrow-left" class="mr-1" /> Back
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="id-card" />
                        <div>
                            <h3>Basic Information</h3>
                            <p>Define the role identity and the access it represents in {{ \App\Helpers\CommonHelper::getBrandName() }}.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name <span style="color:#c9342d">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g. Store Manager">
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug <span style="color:#c9342d">*</span></label>
                                <input type="text" name="slug" id="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" placeholder="e.g. store-manager">
                                <small>Unique identifier, lowercase with hyphens.</small>
                                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="2"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="What can this role do?">{{ old('description') }}</textarea>
                                @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>

                    @can('roles.assign-permissions')
                    <div class="snd-form-section-heading mt-4">
                        <x-snd-icon name="shield" />
                        <div>
                            <h3>Permissions</h3>
                            <p>Choose the actions this role can perform across the POS workspace.</p>
                        </div>
                    </div>
                    <div class="snd-permissions-list">
                        @foreach ($permissions->groupBy('group_name') as $group => $groupPermissions)
                        <div class="snd-permission-group mb-3">
                            <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--snd-primary-deep);margin-bottom:6px;">
                                {{ $group ?? 'General' }}
                            </div>
                            <div class="snd-permission-chips" style="display:flex;flex-wrap:wrap;gap:8px;">
                                @foreach($groupPermissions as $permission)
                                <label style="display:flex;align-items:center;gap:6px;background:var(--snd-surface-soft);border:1px solid var(--snd-border-strong);border-radius:8px;padding:5px 12px;cursor:pointer;font-size:0.82rem;font-weight:500;color:var(--snd-ink-soft);margin:0;transition:border-color 0.12s;">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                           style="accent-color:var(--snd-primary-deep);">
                                    {{ $permission->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endcan

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="plus" class="mr-1" /> Create Role
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
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function () {
    const slug = document.getElementById('slug');
    if (!slug.dataset.modified) {
        slug.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('slug').addEventListener('input', function () {
    this.dataset.modified = '1';
});
</script>
@endsection
