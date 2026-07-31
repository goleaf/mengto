<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumExpertSessionCorrectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int $actor_user_id
 * @property string $corrected_body
 * @property array<int, array{label: string, url: string}> $corrected_source_links
 * @property CarbonImmutable $created_at
 * @property int $forum_expert_session_answer_id
 * @property int $forum_expert_session_id
 * @property int $id
 * @property string $previous_body
 * @property array<int, array{label: string, url: string}> $previous_source_links
 * @property string $reason
 * @property int $version
 * @property-read User $actor
 * @property-read ForumExpertSessionAnswer $answer
 * @property-read ForumExpertSession $session
 */
final class ForumExpertSessionCorrection extends Model
{
    /** @use HasFactory<ForumExpertSessionCorrectionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_expert_session_id',
        'forum_expert_session_answer_id',
        'actor_user_id',
        'version',
        'previous_body',
        'previous_source_links',
        'corrected_body',
        'corrected_source_links',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'previous_source_links' => 'array',
            'corrected_source_links' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum expert session corrections are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum expert session corrections are append-only.',
        ));
    }

    /** @return BelongsTo<ForumExpertSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ForumExpertSession::class, 'forum_expert_session_id');
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
