<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:payment_methods.view')->only(['index', 'show']);
        $this->middleware('can:payment_methods.edit')->only(['create', 'store', 'edit', 'update', 'toggleStatus']);
        $this->middleware('can:payment_methods.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|Factory|JsonResponse
    {
        $query = PaymentMethod::query()
            ->when($request->search, function ($query, $term) {
                $term = trim($term);
                $query->where(function ($q) use ($term) {
                    $q->where('bank_name', 'like', "%{$term}%")
                      ->orWhere('account_title', 'like', "%{$term}%")
                      ->orWhere('account_number', 'like', "%{$term}%")
                      ->orWhere('iban', 'like', "%{$term}%")
                      ->orWhere('branch_name', 'like', "%{$term}%")
                      ->orWhere('branch_code', 'like', "%{$term}%")
                      ->orWhere('name', 'like', "%{$term}%")
                      ->orWhere('id', $term);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', (bool)$request->status);
            })
            ->orderBy('id', 'asc');

        if ($request->wantsJson() && !$request->has('ajax_table')) {
            return response()->json([
                'data' => $query->get(),
            ]);
        }

        $paymentMethods = $query->paginate(10)->withQueryString();

        if ($request->ajax() || $request->has('ajax_table')) {
            return view('payment_methods.partials.table', [
                'paymentMethods' => $paymentMethods,
            ]);
        }

        $totalMethods    = PaymentMethod::count();
        $activeMethods   = PaymentMethod::where('status', true)->count();
        $inactiveMethods = PaymentMethod::where('status', false)->count();

        return view('payment_methods.index', [
            'paymentMethods'  => $paymentMethods,
            'totalMethods'    => $totalMethods,
            'activeMethods'   => $activeMethods,
            'inactiveMethods' => $inactiveMethods,
        ]);
    }

    /**
     * Display the details of a payment method.
     */
    public function show(PaymentMethod $paymentMethod): View|Factory
    {
        return view('payment_methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory
    {
        return view('payment_methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'account_title'  => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'iban'           => 'nullable|string|max:100',
            'branch_name'    => 'nullable|string|max:255',
            'branch_code'    => 'nullable|string|max:50',
            'swift_code'     => 'nullable|string|max:50',
            'status'         => 'nullable',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $validated['type']   = 'card';
        $validated['name']   = trim(($validated['bank_name'] ?? '') . ($request->filled('account_title') ? ' (' . $validated['account_title'] . ')' : '')) ?: 'Card Terminal';
        $validated['code']   = null;
        $validated['status'] = $request->boolean('status', true);

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Card Payment Method added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod): View|Factory
    {
        return view('payment_methods.edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'account_title'  => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'iban'           => 'nullable|string|max:100',
            'branch_name'    => 'nullable|string|max:255',
            'branch_code'    => 'nullable|string|max:50',
            'swift_code'     => 'nullable|string|max:50',
            'status'         => 'nullable',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $validated['type']   = 'card';
        $validated['name']   = trim(($validated['bank_name'] ?? '') . ($request->filled('account_title') ? ' (' . $validated['account_title'] . ')' : '')) ?: ($paymentMethod->name ?: 'Card Terminal');
        $validated['code']   = null;
        $validated['status'] = $request->has('status');

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')->with('success', 'Card Payment Method updated successfully.');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->update([
            'status' => !$paymentMethod->status,
        ]);

        return response()->json([
            'success' => true,
            'status'  => $paymentMethod->status,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment Method deleted successfully.',
        ]);
    }
}
