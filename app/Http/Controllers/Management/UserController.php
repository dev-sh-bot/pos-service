<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Branch;
use App\Models\Counter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users.view')->only(['index']);
        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:users.delete')->only(['destroy']);
        $this->middleware('can:users.assign-roles')->only(['create', 'store', 'edit', 'update']);
    }

    public function index(): View
    {
        $users = User::with('roles')->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', [
            'roles'       => Role::orderBy('name')->get(),
            'permissions' => Permission::orderBy('group_name')->orderBy('name')->get(),
            'branches'    => Branch::where('is_active', true)->orderBy('name')->get(),
            'allCounters' => Counter::where('is_active', true)
                ->get(['id', 'branch_id', 'name', 'code'])
                ->groupBy('branch_id'),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data              = $request->validated();
        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);
        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);
        $user->branches()->sync($request->input('branches', []));

        // Sync counters only if they belong to an assigned branch
        $this->syncUserCounters($user, $request->input('counters', []));

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'permissions', 'branches', 'counters']);

        $assignedBranchIds = $user->branches->pluck('id')->toArray();

        // Only show counters that belong to the user's assigned branches
        $availableCounters = Counter::whereIn('branch_id', $assignedBranchIds)
            ->where('is_active', true)
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('users.edit', [
            'user'             => $user,
            'roles'            => Role::orderBy('name')->get(),
            'permissions'      => Permission::orderBy('group_name')->orderBy('name')->get(),
            'branches'         => Branch::where('is_active', true)->orderBy('name')->get(),
            'allCounters'      => Counter::where('is_active', true)
                ->get(['id', 'branch_id', 'name', 'code'])
                ->groupBy('branch_id'),
            'availableCounters'=> $availableCounters,
            'assignedBranchIds'=> $assignedBranchIds,
            'assignedCounterIds'=> $user->counters->pluck('id')->toArray(),
            'assignedPermissionIds'=> $user->permissions->pluck('id')->toArray(),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', $user->is_active);

        $user->update($data);
        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);
        $user->branches()->sync($request->input('branches', []));
        $this->syncUserCounters($user, $request->input('counters', []));

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        $user->roles()->detach();
        $user->permissions()->detach();
        $user->branches()->detach();
        $user->counters()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', __('User status updated successfully.'));
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Sync counters ensuring they belong to branches the user is assigned to.
     */
    private function syncUserCounters(User $user, array $counterIds): void
    {
        if (empty($counterIds)) {
            $user->counters()->detach();
            return;
        }

        $assignedBranchIds = $user->branches()->pluck('branches.id')->toArray();

        $eligible = Counter::whereIn('id', $counterIds)
            ->whereIn('branch_id', $assignedBranchIds)
            ->pluck('id')
            ->toArray();

        $user->counters()->sync($eligible);
    }
}
