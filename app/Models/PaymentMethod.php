<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'branch_name',
        'branch_code',
        'swift_code',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get badge color styling for payment method type.
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'bank'   => 'badge-bank',
            'cash'   => 'badge-cash',
            'card'   => 'badge-card',
            'wallet' => 'badge-wallet',
            default  => 'badge-other',
        };
    }

    /**
     * Get icon for payment method type.
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'bank'   => 'fas fa-university',
            'cash'   => 'fas fa-money-bill-wave',
            'card'   => 'fas fa-credit-card',
            'wallet' => 'fas fa-mobile-alt',
            default  => 'fas fa-wallet',
        };
    }

    /**
     * Get formatted human readable type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'card'   => 'Card',
            default  => ucfirst($this->type ?? 'Card'),
        };
    }

    /**
     * Get display title for the payment method.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->bank_name ?: ($this->name ?: 'Card Terminal');
    }
}
