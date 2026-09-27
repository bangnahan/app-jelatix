<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JerseySize extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_unlimited' => 'boolean',
        'stock' => 'integer',
        'allocated_stock' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function isUnlimited(): bool
    {
        return $this->is_unlimited || $this->stock === null;
    }

    public function getAvailableStockAttribute(): ?int
    {
        if ($this->isUnlimited()) {
            return null;
        }

        return max(0, (int) $this->stock - (int) $this->allocated_stock);
    }

    public function hasStock(int $needed = 1): bool
    {
        if ($this->isUnlimited()) {
            return true;
        }

        return ((int) $this->allocated_stock + $needed) <= (int) $this->stock;
    }
}
