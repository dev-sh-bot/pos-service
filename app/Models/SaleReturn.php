<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class SaleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'user_id',
        'branch_id',
        'counter_id',
        'return_no',
        'refund_subtotal',
        'refund_tax',
        'refund_discount',
        'refund_total',
        'refund_method',
        'card_holder_name',
        'card_last_four',
        'receiving_account_id',
        'return_reason',
        'notes',
        'srb_invoice_id',
        'srb_qr_code_link',
        'srb_status',
        'srb_response',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (SaleReturn $saleReturn): void {
            if (empty($saleReturn->created_at)) {
                $saleReturn->created_at = Carbon::now('Asia/Karachi');
            }
        });
    }

    protected $casts = [
        'refund_subtotal' => 'float',
        'refund_tax' => 'float',
        'refund_discount' => 'float',
        'refund_total' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class, 'sale_return_id');
    }

    public function receivingAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'receiving_account_id');
    }

    public function srbLogs(): HasMany
    {
        return $this->hasMany(SrbLog::class, 'sale_return_id');
    }

    public function getCustomerName(): string
    {
        return $this->customer
            ? "{$this->customer->first_name} {$this->customer->last_name}"
            : 'Walk-in Customer';
    }
}
