<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id', 'requester_id', 'requester_key', 'requester_name',
        'idempotency_key', 'status', 'message', 'exchange_method', 'proposed_at',
        'expires_at', 'responded_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'proposed_at' => 'datetime',
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
