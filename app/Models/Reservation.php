<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property string $exchange_method
 * @property Carbon|null $expires_at
 * @property int $id
 * @property string $idempotency_key
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property string $message
 * @property numeric-string|null $offered_price
 * @property-read Order|null $order
 * @property bool $privacy_accepted
 * @property Carbon|null $proposed_at
 * @property int $quantity
 * @property array<array-key, mixed>|null $questionnaire
 * @property Carbon|null $rental_ends_at
 * @property Carbon|null $rental_starts_at
 * @property string $request_kind
 * @property-read User|null $requester
 * @property int|null $requester_id
 * @property string $requester_key
 * @property string $requester_name
 * @property Carbon|null $responded_at
 * @property ReservationStatus $status
 * @property bool $terms_accepted
 * @property Carbon|null $updated_at
 */
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

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** @return HasOne<\App\Models\Order, $this>*/
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
