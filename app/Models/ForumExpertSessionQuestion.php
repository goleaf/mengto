<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumExpertSessionQuestionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property CarbonImmutable|null $answered_at
 * @property int $author_user_id
 * @property string $body
 * @property CarbonImmutable|null $declined_at
 * @property int $forum_expert_session_id
 * @property int $id
 * @property string $idempotency_key
 * @property int $lock_version
 * @property string|null $moderation_reason
 * @property string|null $moderation_reason_code
 * @property ForumExpertQuestionModerationStatus $moderation_status
 * @property int $queue_position
 * @property CarbonImmutable|null $removed_at
 * @property CarbonImmutable|null $selected_at
 * @property ForumExpertQuestionStatus $status
 * @property string $stable_key
 * @property CarbonImmutable|null $withdrawn_at
 * @property-read ForumExpertSessionAnswer|null $answer
 * @property-read User $author
 * @property-read Collection<int, ForumExpertSessionHistory> $history
 * @property-read ForumExpertSession $session
 */
final class ForumExpertSessionQuestion extends Model
{
    /** @use HasFactory<ForumExpertSessionQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_expert_session_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'body',
        'status',
        'moderation_status',
        'queue_position',
        'moderation_reason_code',
        'moderation_reason',
        'selected_at',
        'answered_at',
        'declined_at',
        'withdrawn_at',
        'removed_at',
        'lock_version',
    ];

    protected $hidden = ['idempotency_key'];

    protected $attributes = [
        'status' => 'queued',
        'moderation_status' => 'pending',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumExpertQuestionStatus::class,
            'moderation_status' => ForumExpertQuestionModerationStatus::class,
            'selected_at' => 'immutable_datetime',
            'answered_at' => 'immutable_datetime',
            'declined_at' => 'immutable_datetime',
            'withdrawn_at' => 'immutable_datetime',
            'removed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<ForumExpertSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ForumExpertSession::class, 'forum_expert_session_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return HasOne<ForumExpertSessionAnswer, $this> */
    public function answer(): HasOne
    {
        return $this->hasOne(ForumExpertSessionAnswer::class);
    }

    /** @return HasMany<ForumExpertSessionHistory, $this> */
    public function history(): HasMany
    {
        return $this->hasMany(ForumExpertSessionHistory::class);
    }
}
