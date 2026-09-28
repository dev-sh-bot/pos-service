<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'original_amount',
        'discounted_amount',
        'discount_percentage',
        'barcode',
        'status',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discounted_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'deal_services')
            ->withTimestamps();
    }
}
