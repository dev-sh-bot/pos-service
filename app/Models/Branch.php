<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'password',
        'srb_pos_id',
        'srb_pos_user',
        'srb_pos_password',
        'is_active',
    ];

    protected $hidden = ['password', 'srb_pos_password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    /** Hash password before saving if it is plain-text. */
    public function setPasswordAttribute(string $value): void
    {
        // Only hash if the value isn't already bcrypt/argon
        $this->attributes['password'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    /** Verify a plain-text password against the stored hash. */
    public function verifyPassword(string $plain): bool
    {
        return Hash::check($plain, $this->attributes['password']);
    }
}
