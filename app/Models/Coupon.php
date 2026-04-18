<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPES = ['fixed', 'percent'];

    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_amount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function isAvailableForAmount(float $amount): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->startOfDay()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->startOfDay()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $amount >= (float) $this->minimum_amount;
    }

    public function discountFor(float $amount): float
    {
        $discount = $this->type === 'percent'
            ? ($amount * ((float) $this->value / 100))
            : (float) $this->value;

        return round(min($amount, $discount), 2);
    }
}
