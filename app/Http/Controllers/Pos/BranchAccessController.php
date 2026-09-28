<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Counter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles the two-step branch + counter password verification
 * that guards the POS sale screen.
 *
 * All endpoints return JSON so the React Cart component can
 * consume them from SweetAlert2 password modals.
 *
 * Session keys used:
 *   pos_branch_id   – ID of the verified branch for this session
 *   pos_counter_id  – ID of the verified counter for this session
 */
class BranchAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Step 1 ────────────────────────────────────────────────

    /**
     * Return the branches assigned to the authenticated user
     * so the front-end can populate the branch selector.
     */
    public function branches(Request $request): JsonResponse
    {
        $user = $request->user();

        // Super-admins see every active branch
        $branches = $user->isSuperAdmin()
            ? Branch::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code'])
            : $user->branches()->where('is_active', true)->orderBy('name')->get(['branches.id', 'branches.name', 'branches.code']);

        return response()->json($branches);
    }

    /**
     * Verify the branch password.
     * On success stores pos_branch_id in session and clears any
     * stale counter session so the user must re-verify the counter.
     */
    public function verifyBranch(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'password'  => ['required', 'string'],
        ]);

        $user   = $request->user();
        $branch = Branch::findOrFail($request->branch_id);

        // Access check – super-admins bypass assignment check
        if (! $user->isSuperAdmin()) {
            $assigned = $user->branches()->where('branches.id', $branch->id)->exists();
            if (! $assigned) {
                return response()->json(['message' => 'You are not assigned to this branch.'], 403);
            }
        }

        if (! $branch->is_active) {
            return response()->json(['message' => 'This branch is inactive.'], 403);
        }

        if (! $branch->verifyPassword($request->password)) {
            return response()->json(['message' => 'Incorrect branch password.'], 422);
        }

        // Store verified branch; invalidate any previous counter
        $request->session()->put('pos_branch_id', $branch->id);
        $request->session()->forget('pos_counter_id');

        return response()->json([
            'success'   => true,
            'branch_id' => $branch->id,
            'branch'    => $branch->only(['id', 'name', 'code']),
        ]);
    }

    // ── Step 2 ────────────────────────────────────────────────

    /**
     * Return counters for the session-verified branch that are
     * also assigned to the authenticated user.
     */
    public function counters(Request $request): JsonResponse
    {
        $branchId = $request->session()->get('pos_branch_id');

        if (! $branchId) {
            return response()->json(['message' => 'Branch not verified yet.'], 403);
        }

        $branch = Branch::findOrFail($branchId);
        $user   = $request->user();

        $counters = $user->isSuperAdmin()
            ? $branch->counters()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code'])
            : $user->counters()
                ->where('counters.branch_id', $branchId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['counters.id', 'counters.name', 'counters.code']);

        return response()->json($counters);
    }

    /**
     * Verify the counter password.
     * Counter must belong to the session-verified branch.
     * On success stores pos_counter_id in session.
     */
    public function verifyCounter(Request $request): JsonResponse
    {
        $request->validate([
            'counter_id' => ['required', 'integer', 'exists:counters,id'],
            'password'   => ['required', 'string'],
        ]);

        $branchId = $request->session()->get('pos_branch_id');

        if (! $branchId) {
            return response()->json(['message' => 'Branch not verified. Please verify branch first.'], 403);
        }

        $counter = Counter::findOrFail($request->counter_id);

        // Counter must belong to the already-verified branch
        if ((int) $counter->branch_id !== (int) $branchId) {
            return response()->json(['message' => 'Counter does not belong to the verified branch.'], 422);
        }

        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            $assigned = $user->counters()->where('counters.id', $counter->id)->exists();
            if (! $assigned) {
                return response()->json(['message' => 'You are not assigned to this counter.'], 403);
            }
        }

        if (! $counter->is_active) {
            return response()->json(['message' => 'This counter is inactive.'], 403);
        }

        if (! $counter->verifyPassword($request->password)) {
            return response()->json(['message' => 'Incorrect counter password.'], 422);
        }

        $request->session()->put('pos_counter_id', $counter->id);

        return response()->json([
            'success'    => true,
            'counter_id' => $counter->id,
            'counter'    => $counter->only(['id', 'name', 'code']),
        ]);
    }

    // ── Status ────────────────────────────────────────────────

    /**
     * Return the current POS session state so the React app
     * can decide whether to show password modals or go straight
     * to the sale screen on page load / refresh.
     */
    public function status(Request $request): JsonResponse
    {
        $branchId  = $request->session()->get('pos_branch_id');
        $counterId = $request->session()->get('pos_counter_id');

        $branch  = $branchId  ? Branch::find($branchId)?->only(['id', 'name', 'code'])  : null;
        $counter = $counterId ? Counter::find($counterId)?->only(['id', 'name', 'code']) : null;

        return response()->json([
            'branch_verified'  => (bool) $branch,
            'counter_verified' => (bool) $counter,
            'branch'           => $branch,
            'counter'          => $counter,
        ]);
    }

    // ── Clear ─────────────────────────────────────────────────

    /**
     * Clear POS session (e.g. when user deliberately switches branch/counter).
     */
    public function clearSession(Request $request): JsonResponse
    {
        $request->session()->forget(['pos_branch_id', 'pos_counter_id']);

        return response()->json(['success' => true]);
    }
}
