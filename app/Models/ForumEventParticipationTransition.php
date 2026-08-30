<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $forum_event_registration_id
 * @property int|null $forum_event_participation_operation_id
 * @property int|null $actor_user_id
 * @property int $version
 * @property string|null $from_status
 * @property string $to_status
 * @property string $reason_code
 * @property array<string, mixed>|null $metadata
 * @property CarbonImmutable $occurred_at
 */
final class ForumEventParticipationTransition extends Model
{
    protected $fillable = [
        'forum_event_registration_id',
        'forum_event_participation_operation_id',
        'actor_user_id',
        'version',
        'from_status',
        'to_status',
        'reason_code',
        'participant_explanation',
        'metadata',
        'occurred_at',
    ];

    protected $hidden = ['participant_explanation', 'metadata'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'participant_explanation' => 'encrypted',
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEventRegistration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ForumEventRegistration::class, 'forum_event_registration_id');
    }

    /** @return BelongsTo<ForumEventParticipationOperation, $this> */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(ForumEventParticipationOperation::class, 'forum_event_participation_operation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
