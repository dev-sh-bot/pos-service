<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counter\CounterStoreRequest;
use App\Http\Requests\Counter\CounterUpdateRequest;
use App\Models\Branch;
use App\Models\Counter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:counters.view')->only(['index']);
        $this->middleware('can:counters.create')->only(['create', 'store']);
        $this->middleware('can:counters.edit')->only(['edit', 'update', 'assignUsers']);
        $this->middleware('can:counters.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $counters = Counter::with('branch')
            ->withCount('users')
            ->when($request->input('branch_id'), fn ($q, $id) => $q->where('branch_id', $id))
            ->latest()
            ->paginate(15);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('counters.index', compact('counters', 'branches'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('counters.create', compact('branches'));
    }

    public function store(CounterStoreRequest $request): RedirectResponse
    {
        $data              = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        Counter::create($data);

        return redirect()->route('counters.index')
            ->with('success', 'Counter created successfully.');
    }

    public function edit(Counter $counter): View
    {
        $counter->load('branch');
        $counter->loadCount('users');
        $branches      = Branch::where('is_active', true)->orderBy('name')->get();
        $allUsers      = User::with('branches')->orderBy('first_name')->get();
        $assignedUsers = $counter->users->pluck('id')->toArray();

        return view('counters.edit', compact('counter', 'branches', 'allUsers', 'assignedUsers'));
    }

    public function update(CounterUpdateRequest $request, Counter $counter): RedirectResponse
    {
        $data              = $request->validated();
        $data['is_active'] = $request->boolean('is_active', $counter->is_active);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $counter->update($data);

        return redirect()->route('counters.index')
            ->with('success', 'Counter updated successfully.');
    }

    public function destroy(Counter $counter): RedirectResponse
    {
        $counter->users()->detach();
        $counter->delete();

        return redirect()->route('counters.index')
            ->with('success', 'Counter deleted successfully.');
    }

    /**
     * Sync users assigned to a counter (POST from edit page).
     * Only users already assigned to the counter's branch are eligible.
     */
    public function assignUsers(Request $request, Counter $counter): RedirectResponse
    {
        $request->validate([
            'users'   => ['nullable', 'array'],
            'users.*' => ['integer', 'exists:users,id'],
        ]);

        // Restrict to users that belong to this counter's branch
        $eligible = $counter->branch->users()->pluck('users.id')->toArray();
        $toSync   = array_intersect($request->input('users', []), $eligible);

        $counter->users()->sync($toSync);

        return back()->with('success', 'Users assigned to counter successfully.');
    }
}
