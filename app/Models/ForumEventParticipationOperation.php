<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $forum_event_id
 * @property int|null $forum_event_occurrence_id
 * @property int|null $forum_event_registration_id
 * @property int|null $actor_user_id
 * @property string $principal_key
 * @property string $operation_type
 * @property string $idempotency_key
 * @property string $request_checksum
 * @property string $status
 * @property string|null $result_type
 * @property int|null $result_id
 * @property string|null $result_status
 * @property CarbonImmutable|null $completed_at
 */
final class ForumEventParticipationOperation extends Model
{
    protected $fillable = [
        'forum_event_id',
        'forum_event_occurrence_id',
        'forum_event_registration_id',
        'actor_user_id',
        'principal_key',
        'operation_type',
        'idempotency_key',
        'request_checksum',
        'status',
        'result_type',
        'result_id',
        'result_status',
        'expected_version',
        'result_version',
        'completed_at',
        'lock_version',
    ];

    protected $hidden = [
        'idempotency_key',
        'request_checksum',
    ];

    protected $attributes = [
        'status' => 'processing',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'expected_version' => 'integer',
            'result_version' => 'integer',
            'completed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<ForumEventOccurrence, $this> */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(ForumEventOccurrence::class, 'forum_event_occurrence_id');
    }

    /** @return BelongsTo<ForumEventRegistration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ForumEventRegistration::class, 'forum_event_registration_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
