<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'commission_rate' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(OrganizerPayout::class);
    }
}
