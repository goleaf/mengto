<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementNotificationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $attempt_count
 * @property string $deduplication_key
 * @property CarbonImmutable|null $delivered_at
 * @property int $id
 * @property string|null $last_error
 * @property CarbonImmutable|null $last_attempted_at
 * @property string $message_key
 * @property string $notification_kind
 * @property int $place_management_claim_event_id
 * @property int $recipient_user_id
 * @property array<string, mixed> $safe_payload
 * @property PlaceManagementNotificationStatus $status
 */
final class PlaceManagementNotificationIntent extends Model
{
    public const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'place_management_claim_event_id',
        'recipient_user_id',
        'notification_kind',
        'message_key',
        'safe_payload',
        'deduplication_key',
        'status',
        'attempt_count',
        'last_attempted_at',
        'delivered_at',
        'last_error',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['deduplication_key', 'last_error'];

    protected $attributes = ['status' => 'pending', 'attempt_count' => 0];

    protected function casts(): array
    {
        return [
            'safe_payload' => 'array',
            'status' => PlaceManagementNotificationStatus::class,
            'attempt_count' => 'integer',
            'last_attempted_at' => 'immutable_datetime',
            'delivered_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceManagementClaimEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaimEvent::class, 'place_management_claim_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
