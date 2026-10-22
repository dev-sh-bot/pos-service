<?php

namespace App\Models;

use App\Traits\PurchaseScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property numeric $total_amount
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Supplier $supplier
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase bySupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase dateRange(?string $from = null, ?string $to = null)
 * @method static \Database\Factories\PurchaseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase search(?string $search = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase status(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUserId($value)
 * @mixin \Eloquent
 */
class Purchase extends Model
{
    use HasFactory;
    use PurchaseScopes;

    protected $fillable = [
        'purchase_no',
        'supplier_id',
        'user_id',
        'branch_id',
        'counter_id',
        'purchase_date',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'discount_percent',
        'discount_amount',
        'total_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'purchase_date' => 'date',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'user_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(related: Supplier::class, foreignKey: 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(related: Branch::class, foreignKey: 'branch_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(related: Counter::class, foreignKey: 'counter_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(related: PurchaseItem::class, foreignKey: 'purchase_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(related: PurchaseReturn::class, foreignKey: 'purchase_id');
    }
}
