<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SrbLog extends Model
{
    use HasFactory;

    protected $table = 'srb_logs';

    protected $fillable = [
        'order_id',
        'sale_return_id',
        'branch_id',
        'invoice_no',
        'invoice_type',
        'request_type',
        'endpoint',
        'attempt',
        'status_code',
        'res_code',
        'is_success',
        'srb_invoice_id',
        'srb_qr_code_link',
        'payload',
        'response',
        'error_message',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'status_code' => 'integer',
        'attempt' => 'integer',
        'invoice_type' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
