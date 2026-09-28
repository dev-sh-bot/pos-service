<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Service;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DealController extends Controller
{
    public function index(Request $request): View|Factory|JsonResponse
    {
        $query = Deal::query()
            ->with(['services.category'])
            ->when($request->has('status'), fn ($q) => $q->where('status', $request->boolean('status')))
            ->when($request->search, fn ($q, $term) => $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            }))
            ->latest();

        if ($request->wantsJson()) {
            if ($request->boolean('all')) {
                return response()->json(['data' => $query->get()]);
            }
            return response()->json($query->paginate(10));
        }

        return view('deals.index', [
            'deals' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View|Factory
    {
        $categories = Category::query()
            ->with(['services' => fn ($q) => $q->where('status', true)->orderBy('name')])
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorizedServices = Service::query()
            ->whereNull('category_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('deals.create', [
            'categories' => $categories,
            'uncategorizedServices' => $uncategorizedServices,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'original_amount' => 'required|numeric|min:0',
            'discounted_amount' => 'required|numeric|min:0',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'barcode' => 'nullable|string|max:100|unique:deals,barcode',
            'status' => 'nullable|boolean',
        ]);

        $original = (float) $validated['original_amount'];
        $discounted = (float) $validated['discounted_amount'];
        $discountPercent = $original > 0 ? max(0, round((($original - $discounted) / $original) * 100, 2)) : 0;

        $deal = Deal::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'original_amount' => $original,
            'discounted_amount' => $discounted,
            'discount_percentage' => $discountPercent,
            'barcode' => $validated['barcode'] ?: $this->generateBarcode(),
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        $deal->services()->sync($validated['services']);

        return redirect()->route('deals.index')->with('success', __('Deal created successfully!'));
    }

    public function edit(Deal $deal): View|Factory
    {
        $deal->load('services');

        $categories = Category::query()
            ->with(['services' => fn ($q) => $q->where('status', true)->orderBy('name')])
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $uncategorizedServices = Service::query()
            ->whereNull('category_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $selectedServiceIds = $deal->services->pluck('id')->toArray();

        return view('deals.edit', [
            'deal' => $deal,
            'categories' => $categories,
            'uncategorizedServices' => $uncategorizedServices,
            'selectedServiceIds' => $selectedServiceIds,
        ]);
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'original_amount' => 'required|numeric|min:0',
            'discounted_amount' => 'required|numeric|min:0',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'barcode' => 'nullable|string|max:100|unique:deals,barcode,' . $deal->id,
            'status' => 'nullable|boolean',
        ]);

        $original = (float) $validated['original_amount'];
        $discounted = (float) $validated['discounted_amount'];
        $discountPercent = $original > 0 ? max(0, round((($original - $discounted) / $original) * 100, 2)) : 0;

        $deal->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'original_amount' => $original,
            'discounted_amount' => $discounted,
            'discount_percentage' => $discountPercent,
            'barcode' => $validated['barcode'] ?: $deal->barcode ?: $this->generateBarcode(),
            'status' => $request->has('status') ? (bool) $request->status : false,
        ]);

        $deal->services()->sync($validated['services']);

        return redirect()->route('deals.index')->with('success', __('Deal updated successfully!'));
    }

    public function destroy(Deal $deal): JsonResponse
    {
        $deal->services()->detach();
        $deal->delete();

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Deal $deal): JsonResponse
    {
        $deal->update(['status' => !$deal->status]);
        return response()->json(['success' => true, 'status' => $deal->status]);
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = 'DEAL-' . strtoupper(Str::random(8));
        } while (Deal::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
