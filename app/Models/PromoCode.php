<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isValidForAmount(float $amount): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lessThan($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->greaterThan($this->valid_until)) {
            return false;
        }

        if ($amount < (float) $this->min_order_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($amount * (float) $this->discount_value) / 100, 2);
        }

        return min((float) $this->discount_value, $amount);
    }
}
