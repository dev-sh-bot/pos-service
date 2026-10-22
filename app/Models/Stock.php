<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    const TYPE_OPENING = 0;
    const TYPE_PURCHASE = 1;
    const TYPE_PURCHASE_RETURN = 2;
    const TYPE_SALE = 3;
    const TYPE_SALE_RETURN = 4;
    const TYPE_STOCK_TRANSFER = 5;

    protected $fillable = [
        'item_id',
        'voucher_no',
        'voucher_type',
        'voucher_date',
        'customer_id',
        'supplier_id',
        'branch_id',
        'from_branch_id',
        'to_branch_id',
        'qty',
        'rate',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'voucher_type' => 'integer',
        'voucher_date' => 'date',
        'qty'          => 'decimal:2',
        'rate'         => 'decimal:2',
        'amount'       => 'decimal:2',
        'status'       => 'integer',
    ];

    protected $appends = ['voucher_type_name', 'voucher_type_badge'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function getVoucherTypeNameAttribute(): string
    {
        return match ((int) $this->voucher_type) {
            self::TYPE_OPENING          => 'Opening Stock',
            self::TYPE_PURCHASE         => 'Purchase',
            self::TYPE_PURCHASE_RETURN  => 'Purchase Return',
            self::TYPE_SALE             => 'Sale',
            self::TYPE_SALE_RETURN      => 'Sale Return',
            self::TYPE_STOCK_TRANSFER   => 'Stock Transfer',
            default                     => 'Other',
        };
    }

    public function getVoucherTypeBadgeAttribute(): string
    {
        return match ((int) $this->voucher_type) {
            self::TYPE_OPENING          => 'badge-secondary',
            self::TYPE_PURCHASE         => 'badge-success',
            self::TYPE_PURCHASE_RETURN  => 'badge-warning',
            self::TYPE_SALE             => 'badge-primary',
            self::TYPE_SALE_RETURN      => 'badge-info',
            self::TYPE_STOCK_TRANSFER   => 'badge-dark',
            default                     => 'badge-light',
        };
    }

    /**
     * Compute current weighted average cost for a given product.
     */
    public static function calculateAverageCost(int $productId): float
    {
        $inRecords = self::where('item_id', $productId)
            ->whereIn('voucher_type', [self::TYPE_OPENING, self::TYPE_PURCHASE])
            ->where('qty', '>', 0)
            ->get();

        $totalQty = (float) $inRecords->sum('qty');
        $totalCost = (float) $inRecords->sum('amount');

        if ($totalQty > 0 && $totalCost > 0) {
            return round($totalCost / $totalQty, 2);
        }

        $product = Product::find($productId);
        if ($product) {
            if (!empty($product->purchase_price) && (float) $product->purchase_price > 0) {
                return round((float) $product->purchase_price, 2);
            }
            if (!empty($product->price) && (float) $product->price > 0) {
                return round((float) $product->price, 2);
            }
        }

        return 0.00;
    }
}
