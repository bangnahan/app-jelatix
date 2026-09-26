<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerPayout extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'platform_fee_deducted' => 'decimal:2',
        'net_payout_amount' => 'decimal:2',
        'transferred_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
