<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumExpertSessionHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property string $event_type
 * @property int|null $forum_expert_session_answer_id
 * @property int $forum_expert_session_id
 * @property int|null $forum_expert_session_question_id
 * @property string|null $from_status
 * @property int $id
 * @property string|null $idempotency_key
 * @property array<string, mixed>|null $metadata
 * @property string $reason_code
 * @property string $summary_translation_key
 * @property string|null $to_status
 * @property-read User|null $actor
 * @property-read ForumExpertSessionAnswer|null $answer
 * @property-read ForumExpertSessionQuestion|null $question
 * @property-read ForumExpertSession $session
 */
final class ForumExpertSessionHistory extends Model
{
    /** @use HasFactory<ForumExpertSessionHistoryFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'forum_expert_session_history';

    protected $fillable = [
        'forum_expert_session_id',
        'forum_expert_session_question_id',
        'forum_expert_session_answer_id',
        'actor_user_id',
        'event_type',
        'from_status',
        'to_status',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected $hidden = ['metadata', 'idempotency_key'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum expert session history is append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum expert session history is append-only.',
        ));
    }

    /** @return BelongsTo<ForumExpertSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ForumExpertSession::class, 'forum_expert_session_id');
    }

    /** @return BelongsTo<ForumExpertSessionQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            ForumExpertSessionQuestion::class,
            'forum_expert_session_question_id',
        );
    }

    /** @return BelongsTo<ForumExpertSessionAnswer, $this> */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(
            ForumExpertSessionAnswer::class,
            'forum_expert_session_answer_id',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
