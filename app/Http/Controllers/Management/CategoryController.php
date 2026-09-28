<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:categories.view')->only(['index', 'show']);
        $this->middleware('can:categories.create')->only(['create', 'store']);
        $this->middleware('can:categories.edit')->only(['edit', 'update']);
        $this->middleware('can:categories.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('services')
            ->when($request->search, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        if ($request->wantsJson()) {
            $list = Category::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'sort_order']);

            return response()->json(['data' => $list]);
        }

        return view('categories.index', ['categories' => $categories]);
    }

    public function create(): View|Factory
    {
        return view('categories.create');
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category): void
    {
        //
    }

    public function edit(Category $category): View|Factory
    {
        return view('categories.edit', ['category' => $category]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['success' => true]);
    }
}
