<?php

namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\ChangeQuantityRequest;
use App\Http\Requests\Cart\RemoveFromCartRequest;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:orders.create')->only(['index', 'store', 'changeQty', 'delete', 'empty', 'categories']);
    }

    /**
     * Display the cart.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json($this->cartItems($request->user()->id));
        }

        return view('cart.index');
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'sort_order']);

        return response()->json(['data' => $categories]);
    }

    /**
     * Add product to cart by barcode.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $product = Product::where('barcode', $request->barcode)->first();
        $service = Service::where('barcode', $request->barcode)->first();
        $deal = Deal::where('barcode', $request->barcode)->where('status', true)->first();

        $item = $product ?? $service ?? $deal;
        if (! $item) {
            return response()->json(['message' => __('cart.not_found')], 404);
        }

        $itemType = 0;
        if ($item instanceof Service) {
            $itemType = 1;
        } elseif ($item instanceof Deal) {
            $itemType = 2;
        }

        $cartItem = DB::table('user_cart')
            ->where('user_id', $request->user()->id)
            ->where('item_id', $item->id)
            ->where('item_type', $itemType)
            ->first();
        if ($cartItem) {
            return $this->incrementCartItem($cartItem, $item);
        }

        return $this->addNewCartItem($request, $item);
    }

    /**
     * Change quantity of a cart item.
     */
    public function changeQty(ChangeQuantityRequest $request): JsonResponse
    {
        $rawProductId = $request->product_id;
        [$productId, $itemType] = $this->parseCartItemId($rawProductId);

        if ($productId === null) {
            return response()->json(['success' => true]);
        }

        $product = match ($itemType) {
            1 => Service::findOrFail($productId),
            2 => Deal::findOrFail($productId),
            default => Product::findOrFail($productId),
        };
        $cartItem = DB::table('user_cart')->where('user_id', $request->user()->id)->where('item_id', $productId)->where('item_type', $itemType)->first();

        if (!$cartItem) {
            return response()->json(['success' => true]);
        }

        // Validate stock availability
        if ($itemType === 0 && $product->quantity < $request->quantity) {
            return response()->json([
                'message' => __('cart.available', ['quantity' => $product->quantity]),
            ], 400);
        }

        DB::table('user_cart')->where('user_id', $request->user()->id)->where('item_id', $productId)->where('item_type', $itemType)->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove product from cart.
     */
    public function delete(RemoveFromCartRequest $request): JsonResponse
    {
        $rawProductId = (string) $request->product_id;
        [$productId, $itemType] = $this->parseCartItemId($rawProductId);

        if ($productId === null) {
            return response()->json(['success' => true]);
        }

        DB::table('user_cart')->where('user_id', $request->user()->id)->where('item_id', $productId)->where('item_type', $itemType)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Empty the entire cart.
     */
    public function empty(Request $request): JsonResponse
    {
        DB::table('user_cart')->where('user_id', $request->user()->id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Increment quantity of existing cart item.
     */
    private function incrementCartItem($cartItem, $item): JsonResponse
    {
        if ($item instanceof Product && $item->quantity <= $cartItem->quantity) {
            return response()->json([
                'message' => __('cart.available', ['quantity' => $item->quantity]),
            ], 400);
        }

        DB::table('user_cart')->where('user_id', request()->user()->id)->where('item_id', $cartItem->item_id)->where('item_type', $cartItem->item_type)->increment('quantity');

        return response()->json(['success' => true]);
    }

    /**
     * Add new product to cart.
     */
    private function addNewCartItem(Request $request, $item): JsonResponse
    {
        if ($item instanceof Product && $item->quantity < 1) {
            return response()->json([
                'message' => __('cart.outstock'),
            ], 400);
        }

        $itemType = 0;
        if ($item instanceof Service) {
            $itemType = 1;
        } elseif ($item instanceof Deal) {
            $itemType = 2;
        }

        DB::table('user_cart')->insert(['user_id' => $request->user()->id, 'item_id' => $item->id, 'item_type' => $itemType, 'quantity' => 1]);

        return response()->json(['success' => true]);
    }

    private function parseCartItemId(string $value): array
    {
        if (str_contains($value, ':')) {
            [$type, $id] = explode(':', $value, 2);
            $itemType = match ($type) {
                'service' => 1,
                'deal' => 2,
                default => 0,
            };
            return [is_numeric($id) ? (int) $id : null, $itemType];
        }

        return [is_numeric($value) ? (int) $value : null, 0];
    }

    private function cartItems(int $userId): Collection
    {
        return DB::table('user_cart')->where('user_id', $userId)->get()->map(function ($row) {
            $item = null;
            $typeStr = 'product';
            if ((int) $row->item_type === 1) {
                $item = Service::find($row->item_id);
                $typeStr = 'service';
            } elseif ((int) $row->item_type === 2) {
                $item = Deal::find($row->item_id);
                $typeStr = 'deal';
            } else {
                $item = Product::find($row->item_id);
                $typeStr = 'product';
            }
            if (! $item) return null;
            $item->item_type = $typeStr;
            if ($item instanceof Deal) {
                $item->price = (float) $item->discounted_amount;
            }
            $item->pivot = (object) ['quantity' => (int) $row->quantity, 'product_id' => $row->item_id, 'item_id' => $row->item_id];
            return $item;
        })->filter()->values();
    }
}
