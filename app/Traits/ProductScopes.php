<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait ProductScopes
{
    /**
     * Check if product is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity < 10;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Scope for active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope for low stock products.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('quantity', '<', 10);
    }

    /**
     * Scope for best selling products (total sold > 10).
     */
    public function scopeBestSelling(Builder $query): Builder
    {
        $sales = DB::table('order_items')
            ->select('item_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('item_type', 0) // Only products, not services
            ->groupBy('item_id');

        return $query
            ->select('products.*')
            ->selectRaw('COALESCE(sales.total_sold, 0) as total_sold')
            ->leftJoinSub($sales, 'sales', function ($join): void {
                $join->on('sales.item_id', '=', 'products.id');
            })
            ->orderByDesc('total_sold')
            ->limit(10);
    }

    /**
     * Scope for current month best selling products.
     */
    public function scopeCurrentMonthBestSelling(Builder $query): Builder
    {
        $sales = DB::table('order_items')
            ->select('order_items.item_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereYear('orders.created_at', now()->year)
            ->whereMonth('orders.created_at', now()->month)
            ->where('order_items.item_type', 0) // Only products, not services
            ->groupBy('order_items.item_id');

        return $query
            ->select('products.*')
            ->selectRaw('COALESCE(sales.total_sold, 0) as total_sold')
            ->leftJoinSub($sales, 'sales', function ($join): void {
                $join->on('sales.product_id', '=', 'products.id');
            })
            ->orderByDesc('total_sold')
            ->limit(10);
    }

    /**
     * Scope for past months hot products (6 months).
     */
    public function scopePastMonthsHotProducts(Builder $query): Builder
    {
        $sales = DB::table('order_items')
            ->select('order_items.item_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', now()->subMonths(6))
            ->where('order_items.item_type', 0) // Only products, not services
            ->groupBy('order_items.item_id');

        return $query
            ->select('products.*')
            ->selectRaw('COALESCE(sales.total_sold, 0) as total_sold')
            ->leftJoinSub($sales, 'sales', function ($join): void {
                $join->on('sales.item_id', '=', 'products.id');
            })
            ->orderByDesc('total_sold')
            ->limit(10);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($query, $term): void {
            $query->where('name', 'LIKE', "%{$term}%");
        });
    }
}
