<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id', 'requester_id', 'requester_key', 'requester_name',
        'idempotency_key', 'status', 'request_kind', 'quantity', 'offered_price',
        'message', 'exchange_method', 'proposed_at', 'rental_starts_at',
        'rental_ends_at', 'questionnaire', 'terms_accepted', 'privacy_accepted',
        'expires_at', 'responded_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'offered_price' => 'decimal:2',
            'proposed_at' => 'datetime',
            'rental_starts_at' => 'datetime',
            'rental_ends_at' => 'datetime',
            'questionnaire' => 'array',
            'terms_accepted' => 'boolean',
            'privacy_accepted' => 'boolean',
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

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
