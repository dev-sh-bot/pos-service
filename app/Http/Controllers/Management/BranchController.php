<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\BranchStoreRequest;
use App\Http\Requests\Branch\BranchUpdateRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:branches.view')->only(['index']);
        $this->middleware('can:branches.create')->only(['create', 'store']);
        $this->middleware('can:branches.edit')->only(['edit', 'update', 'assignUsers']);
        $this->middleware('can:branches.delete')->only(['destroy']);
    }

    public function index(): View
    {
        $branches = Branch::withCount(['counters', 'users'])->latest()->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(BranchStoreRequest $request): RedirectResponse
    {
        $data              = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        Branch::create($data);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch): View
    {
        $branch->loadCount(['counters', 'users']);
        $allUsers      = User::orderBy('first_name')->get();
        $assignedUsers = $branch->users->pluck('id')->toArray();

        return view('branches.edit', compact('branch', 'allUsers', 'assignedUsers'));
    }

    public function update(BranchUpdateRequest $request, Branch $branch): RedirectResponse
    {
        $data              = $request->validated();
        $data['is_active'] = $request->boolean('is_active', $branch->is_active);

        // Only update password if a new one was supplied
        if (empty($data['password'])) {
            unset($data['password']);
        }
        if (empty($data['srb_pos_password'])) {
            unset($data['srb_pos_password']);
        }

        $branch->update($data);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->orders()->exists()) {
            return back()->with('error', 'Cannot delete branch: it has existing orders.');
        }

        $branch->users()->detach();
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    /**
     * Sync users assigned to a branch (POST from edit page user-assignment panel).
     */
    public function assignUsers(Request $request, Branch $branch): RedirectResponse
    {
        $request->validate([
            'users'   => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],
        ]);

        $branch->users()->sync($request->input('users', []));

        return back()->with('success', 'Users assigned to branch successfully.');
    }
}
