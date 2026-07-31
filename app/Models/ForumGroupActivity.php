<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupActivityFormat;
use App\Enums\ForumGroupActivityStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $archived_at
 * @property int|null $capacity
 * @property string $creation_idempotency_key
 * @property int $created_by_user_id
 * @property CarbonImmutable $ends_at
 * @property ForumGroupActivityFormat $format
 * @property int|null $forum_event_id
 * @property int $forum_group_id
 * @property int $id
 * @property string|null $location_scope
 * @property int $lock_version
 * @property string|null $participation_notes
 * @property CarbonImmutable $starts_at
 * @property ForumGroupActivityStatus $status
 * @property string $summary
 * @property string $title
 * @property string $timezone
 * @property-read User $creator
 * @property-read ForumEvent|null $event
 * @property-read ForumGroup $group
 */
final class ForumGroupActivity extends Model
{
    /** @use HasFactory<ForumGroupActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'forum_event_id',
        'created_by_user_id',
        'stable_key',
        'creation_idempotency_key',
        'title',
        'summary',
        'format',
        'status',
        'starts_at',
        'ends_at',
        'timezone',
        'location_scope',
        'capacity',
        'participation_notes',
        'lock_version',
        'archived_at',
    ];

    protected $attributes = [
        'status' => 'scheduled',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'format' => ForumGroupActivityFormat::class,
            'status' => ForumGroupActivityStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'capacity' => 'integer',
            'lock_version' => 'integer',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
