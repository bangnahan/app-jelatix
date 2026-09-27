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

    protected static function booted(): void
    {
        static::created(function (Event $event) {
            if ($event->jerseySizes()->count() === 0) {
                $standardSizes = [
                    ['size_name' => 'XS', 'gender_type' => 'unisex', 'chest_width_cm' => 46, 'body_length_cm' => 66, 'stock' => 50],
                    ['size_name' => 'S', 'gender_type' => 'unisex', 'chest_width_cm' => 48, 'body_length_cm' => 68, 'stock' => 100],
                    ['size_name' => 'M', 'gender_type' => 'unisex', 'chest_width_cm' => 50, 'body_length_cm' => 70, 'stock' => 200],
                    ['size_name' => 'L', 'gender_type' => 'unisex', 'chest_width_cm' => 52, 'body_length_cm' => 72, 'stock' => 200],
                    ['size_name' => 'XL', 'gender_type' => 'unisex', 'chest_width_cm' => 54, 'body_length_cm' => 74, 'stock' => 150],
                    ['size_name' => 'XXL', 'gender_type' => 'unisex', 'chest_width_cm' => 56, 'body_length_cm' => 76, 'stock' => 100],
                    ['size_name' => '3XL', 'gender_type' => 'unisex', 'chest_width_cm' => 58, 'body_length_cm' => 78, 'stock' => 50],
                    ['size_name' => '4XL', 'gender_type' => 'unisex', 'chest_width_cm' => 60, 'body_length_cm' => 80, 'stock' => 50],
                    ['size_name' => '5XL', 'gender_type' => 'unisex', 'chest_width_cm' => 62, 'body_length_cm' => 82, 'stock' => 50],
                ];

                foreach ($standardSizes as $size) {
                    $event->jerseySizes()->create($size);
                }
            }
        });
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    public function jerseySizes(): HasMany
    {
        return $this->hasMany(JerseySize::class)
            ->orderByRaw("CASE size_name 
                WHEN 'XS' THEN 1 
                WHEN 'S' THEN 2 
                WHEN 'M' THEN 3 
                WHEN 'L' THEN 4 
                WHEN 'XL' THEN 5 
                WHEN 'XXL' THEN 6 
                WHEN '2XL' THEN 6 
                WHEN '3XL' THEN 7 
                WHEN '4XL' THEN 8 
                WHEN '5XL' THEN 9 
                ELSE 10 
            END, chest_width_cm ASC");
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
