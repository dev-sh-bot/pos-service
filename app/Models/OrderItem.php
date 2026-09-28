<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float $price
 * @property int $quantity
 * @property int $order_id
 * @property int|null $item_id
 * @property int $item_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @method static Builder<static>|OrderItem newModelQuery()
 * @method static Builder<static>|OrderItem newQuery()
 * @method static Builder<static>|OrderItem query()
 * @method static Builder<static>|OrderItem whereCreatedAt($value)
 * @method static Builder<static>|OrderItem whereId($value)
 * @method static Builder<static>|OrderItem whereOrderId($value)
 * @method static Builder<static>|OrderItem wherePrice($value)
 * @method static Builder<static>|OrderItem whereProductId($value)
 * @method static Builder<static>|OrderItem whereQuantity($value)
 * @method static Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends Model
{
    protected $fillable = [
        'price',
        'quantity',
        'item_id',
        'order_id',
        'item_type',
        'item_name'
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'item_type' => 'integer',
    ];

    /**
     * Get the product (only for products, not services).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id', 'id');
    }

    /**
     * Get the service (only for services, not products).
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'item_id', 'id');
    }

    /**
     * Get the deal (only for deals).
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'item_id', 'id');
    }

    /**
     * Get the item (product, service, or deal) based on item_type.
     */
    public function item()
    {
        if ($this->item_type === 2) {
            return $this->deal;
        }
        return $this->item_type === 1 ? $this->service : $this->product;
    }

    /**
     * Get the item name, fallback to item relation if item_name is empty.
     */
    public function getItemNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        $item = $this->item();
        return $item ? $item->name : null;
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get subtotal for this item.
     */
    public function subtotal(): float
    {
        return $this->price;
    }

    /**
     * Get unit price.
     */
    public function unitPrice(): float
    {
        return $this->quantity > 0 ? $this->price / $this->quantity : 0;
    }
}
