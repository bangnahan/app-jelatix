<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'registration_open_date' => 'datetime',
        'registration_close_date' => 'datetime',
        'rpc_start_date' => 'datetime',
        'rpc_end_date' => 'datetime',
        'auto_assign_bib' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    public function jerseySizes(): HasMany
    {
        return $this->hasMany(JerseySize::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(EventCustomField::class)->orderBy('sort_order');
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(Participant::class, EventCategory::class);
    }

    public function isRegistrationOpen(): bool
    {
        $now = now();
        return $this->status === 'published' &&
            $now->between($this->registration_open_date, $this->registration_close_date);
    }
}
