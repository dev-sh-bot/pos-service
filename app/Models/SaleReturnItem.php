<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_return_id',
        'order_item_id',
        'item_id',
        'item_type',
        'item_name',
        'quantity',
        'unit_price',
        'refund_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'item_type' => 'integer',
        'unit_price' => 'float',
        'refund_amount' => 'float',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class, 'sale_return_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'item_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'item_id');
    }
}
