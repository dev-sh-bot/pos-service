<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\AddToPurchaseCartRequest;
use App\Http\Requests\Purchase\ChangePurchaseCartQtyRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseCartController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:purchases.create')->only(['index', 'store', 'changeQty', 'changePrice', 'delete', 'empty']);
    }

    /**
     * Get purchase cart items
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $cart = $user->purchaseCart()->with('category')->get();

        $formattedCart = $cart->map(function ($item): array {
            assert($item instanceof Product);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'image_url' => $item->image_url,
                'stock' => (float) $item->quantity,
                'quantity' => (float) $item->quantity,
                'category_name' => $item->category?->name ?? 'General',
                'pivot' => [
                    'quantity' => (float) $item->pivot->quantity,
                    'purchase_price' => (float) $item->pivot->purchase_price,
                    'product_id' => $item->id,
                    'user_id' => $item->pivot->user_id,
                ],
            ];
        })->values();

        return response()->json($formattedCart);
    }

    /**
     * Add product to purchase cart
     */
    public function store(AddToPurchaseCartRequest $request): JsonResponse
    {
        $product = null;
        if ($request->filled('product_id')) {
            $product = Product::find($request->product_id);
        } elseif ($request->filled('barcode')) {
            $product = Product::where('barcode', $request->input('barcode'))->first();
        }

        if (!$product) {
            return response()->json([
                'message' => __('Product not found!')
            ], 404);
        }

        /** @var User $user */
        $user = Auth::user();

        $cartItem = $user->purchaseCart()
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem !== null) {
            // Update existing cart item
            $currentQuantity = (float) $cartItem->pivot->quantity;
            $user->purchaseCart()->updateExistingPivot($product->id, [
                'quantity' => $currentQuantity + 1,
            ]);
        } else {
            // Add new item to cart
            $price = !empty($product->purchase_price) ? (float) $product->purchase_price : (!empty($product->price) ? (float) $product->price : 0);
            $user->purchaseCart()->attach($product->id, [
                'quantity' => 1,
                'purchase_price' => $price,
            ]);
        }

        return response()->json([
            'message' => __('Product added to cart!')
        ]);
    }

    /**
     * Change quantity in purchase cart
     */
    public function changeQty(ChangePurchaseCartQtyRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->purchaseCart()->updateExistingPivot($request->product_id, [
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => __('Quantity updated!')
        ]);
    }

    /**
     * Change purchase price in cart
     */
    public function changePrice(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->purchaseCart()->updateExistingPivot($request->product_id, [
            'purchase_price' => $request->purchase_price,
        ]);

        return response()->json([
            'message' => __('Purchase price updated!')
        ]);
    }

    /**
     * Remove product from purchase cart
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->purchaseCart()->detach($request->product_id);

        return response()->json([
            'message' => __('Product removed from cart!')
        ]);
    }

    /**
     * Empty purchase cart
     */
    public function empty(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->purchaseCart()->detach();

        return response()->json([
            'message' => __('Cart emptied!')
        ]);
    }
}
