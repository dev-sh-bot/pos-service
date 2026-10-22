<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerStoreRequest;
use App\Http\Requests\Customer\CustomerUpdateRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers.view')->only(['index']);
        $this->middleware('can:customers.create')->only(['create', 'store']);
        $this->middleware('can:customers.edit')->only(['edit', 'update']);
        $this->middleware('can:customers.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response|View|JsonResponse
    {
        $query = Customer::query()
            ->when($request->search, function ($query, $term) {
                $term = trim($term);
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                      ->orWhere('last_name', 'like', "%{$term}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%")
                      ->orWhere('address', 'like', "%{$term}%")
                      ->orWhere('id', $term);
                });
            })
            ->latest();

        if ($request->wantsJson() && !$request->has('ajax_table')) {
            return response()->json(
                $request->has('search') ? $query->get() : Customer::all()
            );
        }

        $customers = $query->paginate(10)->withQueryString();

        if ($request->ajax() || $request->has('ajax_table')) {
            return view('customers.partials.table', [
                'customers' => $customers,
            ]);
        }

        return view('customers.index', [
            'customers' => $customers,
            'totalCustomersCount' => Customer::count(),
            'withPhoneCount' => Customer::whereNotNull('phone')->where('phone', '!=', '')->count(),
            'withEmailCount' => Customer::whereNotNull('email')->where('email', '!=', '')->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(CustomerStoreRequest $request)
    {
        $customerData = $request->validated();
        $customerData['user_id'] = $request->user()->id;
        $customerData['last_name'] = $customerData['last_name'] ?? '';

        if ($request->hasFile('avatar')) {
            $customerData['avatar'] = $request->file('avatar')->store('customers', 'public');
        }

        $customer = Customer::create($customerData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => __('customer.success_creating')
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', __('customer.success_creating'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Factory|View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $customerData = $request->validated();
        if (array_key_exists('last_name', $customerData)) {
            $customerData['last_name'] = $customerData['last_name'] ?? '';
        }

        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }
            $customerData['avatar'] = $request->file('avatar')->store('customers', 'public');
        }

        $customer->update($customerData);

        return redirect()->route('customers.index')
            ->with('success', __('customer.success_updating'));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->avatar) {
            Storage::disk('public')->delete($customer->avatar);
        }

        $customer->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
