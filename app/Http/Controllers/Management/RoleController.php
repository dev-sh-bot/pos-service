<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:roles.view')->only(['index']);
        $this->middleware('can:roles.create')->only(['create', 'store']);
        $this->middleware('can:roles.edit')->only(['edit', 'update']);
        $this->middleware('can:roles.delete')->only(['destroy']);
        $this->middleware('can:roles.assign-permissions')->only(['create', 'store', 'edit', 'update']);
    }

    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->latest()->paginate(10);

        return view('roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'groups' => config('permissions'),
            'permissions' => Permission::orderBy('group_name')->orderBy('name')->get(),
        ]);
    }

    public function store(RoleStoreRequest $request): RedirectResponse
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', __('Role created successfully.'));
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('roles.edit', [
            'role' => $role,
            'groups' => config('permissions'),
            'permissions' => Permission::orderBy('group_name')->orderBy('name')->get(),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->validated());
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system || $role->users()->exists()) {
            return back()->with('error', __('This role cannot be deleted safely.'));
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')->with('success', __('Role deleted successfully.'));
    }
}
