@extends('layouts.admin')

@section('title', 'Edit Role — ' . $role->name)
@section('content-header', 'Edit Role')

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
                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="snd-form-section-heading">
                        <x-snd-icon name="id-card" />
                        <div>
                            <h3>Basic Information</h3>
                            <p>Update the role identity and the access it represents in {{ \App\Helpers\CommonHelper::getBrandName() }}.</p>
                        </div>
                        @if($role->is_system)
                            <span class="badge badge-info ml-auto">System Role</span>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name <span style="color:#ef4444">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $role->name) }}"
                                       {{ $role->is_system ? 'readonly' : '' }}>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" name="slug" id="slug"
                                       class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug', $role->slug) }}"
                                       {{ $role->is_system ? 'readonly' : '' }}>
                                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="2"
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $role->description) }}</textarea>
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

                        {{-- Select all / none quick toggle --}}
                        <div class="snd-permissions-toolbar">
                            <button type="button" onclick="toggleAll(true)"
                                    class="btn btn-outline-primary btn-sm">
                                Select All
                            </button>
                            <button type="button" onclick="toggleAll(false)"
                                    class="btn btn-outline-secondary btn-sm">
                                Clear All
                            </button>
                        </div>

                        @foreach ($permissions->groupBy('group_name') as $group => $groupPermissions)
                        <div class="snd-permission-group mb-3">
                            <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#0ea5b0;margin-bottom:6px;">
                                {{ $group ?? 'General' }}
                            </div>
                            <div class="snd-permission-chips" style="display:flex;flex-wrap:wrap;gap:8px;">
                                @foreach($groupPermissions as $permission)
                                <label style="display:flex;align-items:center;gap:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:5px 12px;cursor:pointer;font-size:0.82rem;font-weight:500;color:#374151;margin:0;transition:border-color 0.12s;">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                           class="perm-check"
                                           {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                           style="accent-color:#0ea5b0;">
                                    {{ $permission->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endcan

                    <div class="snd-form-actions">
                        <button type="submit" class="btn btn-primary">
                            <x-snd-icon name="save" class="mr-1" /> Update Role
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
function toggleAll(state) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = state);
}
</script>
@endsection
