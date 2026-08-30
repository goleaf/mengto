<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceInvitationStatus;
use Database\Factories\PlaceInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlaceInvitation extends Model
{
    /** @use HasFactory<PlaceInvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'invitation_key', 'place_id', 'sender_user_id', 'recipient_user_id',
        'responded_by_user_id', 'revoked_by_user_id', 'status', 'visibility',
        'message', 'proposed_at', 'sent_at', 'expires_at', 'responded_at',
        'revoked_at', 'reason_code', 'idempotency_key', 'response_key',
        'revocation_key', 'open_key', 'lock_version',
    ];

    protected $hidden = [
        'message', 'idempotency_key', 'response_key', 'revocation_key',
        'open_key', 'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlaceInvitationStatus::class,
            'message' => 'encrypted',
            'proposed_at' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'invitation_key';
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /** @return HasMany<PlaceInvitationEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceInvitationEvent::class);
    }
}
