<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceStoreRequest;
use App\Http\Requests\Service\ServiceUpdateRequest;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:services.view')->only(['index', 'show']);
        $this->middleware('can:services.create')->only(['create', 'store']);
        $this->middleware('can:services.edit')->only(['edit', 'update']);
        $this->middleware('can:services.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Service::query()
            ->with('category')
            ->when($request->search, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when($request->category_id, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->latest();

        if ($request->wantsJson()) {
            if ($request->boolean('all')) {
                return response()->json(['data' => $query->get()]);
            }

            return response()->json($query->paginate(10));
        }

        return view('services.index', [
            'services' => $query->paginate(10)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View|Factory
    {
        return view('services.create', [
            'categories' => Category::where('status', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(ServiceStoreRequest $request): RedirectResponse
    {
        $serviceData = $request->validated();
        $serviceData['barcode'] = $serviceData['barcode'] ?: $this->generateBarcode();

        if ($request->hasFile('image')) {
            $serviceData['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($serviceData);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function show(Service $service): void
    {
        //
    }

    public function edit(Service $service): View|Factory
    {
        return view('services.edit', [
            'service' => $service,
            'categories' => Category::where('status', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(ServiceUpdateRequest $request, Service $service): RedirectResponse
    {
        $serviceData = $request->validated();
        $serviceData['barcode'] = $serviceData['barcode'] ?: $service->barcode ?: $this->generateBarcode();

        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $serviceData['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($serviceData);

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): JsonResponse
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return response()->json(['success' => true]);
    }

    private function generateBarcode(): string
    {
        do {
            $barcode = 'SRV-' . strtoupper(Str::random(8));
        } while (Service::where('barcode', $barcode)->exists());

        return $barcode;
    }
}
