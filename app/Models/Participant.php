<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Participant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'birth_date' => 'date',
        'is_vip' => 'boolean',
        'is_custom_bib' => 'boolean',
        'custom_fields_data' => 'array',
        'waiver_accepted' => 'boolean',
        'waiver_accepted_at' => 'datetime',
        'is_rpc_claimed' => 'boolean',
        'rpc_claimed_at' => 'datetime',
        'is_proxy_claimed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($participant) {
            if (empty($participant->qr_token)) {
                $participant->qr_token = Str::random(32);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function jerseySize(): BelongsTo
    {
        return $this->belongsTo(JerseySize::class);
    }

    public function rpcClaimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rpc_claimed_by_user_id');
    }

    public function getEventAttribute(): ?Event
    {
        return $this->category?->event;
    }
}
