<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['create', 'store']);
        $this->middleware('can:products.edit')->only(['edit', 'update']);
        $this->middleware('can:products.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = $request->wantsJson() ? (int) $request->get('per_page', 250) : 10;
        $products = $query
            ->search($request->search)
            ->latest()
            ->paginate($perPage);

        return $request->wantsJson()
            ? response()->json($products)
            : view('products.index', ['products' => $products]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Factory
    {
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(ProductStoreRequest $request)
    {
        $productData = $request->validated();

        if ($request->hasFile('image')) {
            $productData['image'] = $request->file('image')->store('products', 'public');
        }

        $productData['quantity'] = isset($productData['quantity']) ? (int) $productData['quantity'] : 0;
        if (empty($productData['barcode'])) {
            $productData['barcode'] = null;
        }

        Product::create($productData);

        return redirect()->route('products.index')
            ->with('success', __('product.success_creating'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Factory|View|\Illuminate\View\View
     */
    public function edit(Product $product)
    {
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return RedirectResponse
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $productData = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $productData['image'] = $request->file('image')->store('products', 'public');
        }

        if (array_key_exists('barcode', $productData) && empty($productData['barcode'])) {
            $productData['barcode'] = null;
        }

        $product->update($productData);

        return redirect()->route('products.index')
            ->with('success', __('product.success_updating'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['success' => true]);
    }
}
