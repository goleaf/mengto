<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumExpertAnswerStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumExpertSessionAnswerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable $answered_at
 * @property int $author_user_id
 * @property string $body
 * @property int $current_version
 * @property int $forum_expert_session_id
 * @property int $forum_expert_session_question_id
 * @property int $id
 * @property string $idempotency_key
 * @property array<int, array{label: string, url: string}> $source_links
 * @property ForumExpertAnswerStatus $status
 * @property string $stable_key
 * @property-read User $author
 * @property-read Collection<int, ForumExpertSessionCorrection> $corrections
 * @property-read ForumExpertSessionQuestion $question
 * @property-read ForumExpertSession $session
 */
final class ForumExpertSessionAnswer extends Model
{
    /** @use HasFactory<ForumExpertSessionAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_expert_session_id',
        'forum_expert_session_question_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'body',
        'source_links',
        'status',
        'current_version',
        'answered_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected $attributes = [
        'status' => 'published',
        'current_version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'source_links' => 'array',
            'status' => ForumExpertAnswerStatus::class,
            'current_version' => 'integer',
            'answered_at' => 'immutable_datetime',
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

    /** @return BelongsTo<ForumExpertSessionQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            ForumExpertSessionQuestion::class,
            'forum_expert_session_question_id',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return HasMany<ForumExpertSessionCorrection, $this> */
    public function corrections(): HasMany
    {
        return $this->hasMany(ForumExpertSessionCorrection::class);
    }
}
