<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumMentorshipFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int $author_user_id
 * @property CarbonImmutable $created_at
 * @property int $forum_mentorship_id
 * @property int $id
 * @property string|null $private_note
 * @property int $rating
 * @property int $recipient_user_id
 * @property string $summary
 * @property bool|null $would_recommend
 */
final class ForumMentorshipFeedback extends Model
{
    /** @use HasFactory<ForumMentorshipFeedbackFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_mentorship_id',
        'author_user_id',
        'recipient_user_id',
        'rating',
        'summary',
        'would_recommend',
        'private_note',
        'created_at',
    ];

    protected $hidden = [
        'private_note',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'would_recommend' => 'boolean',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum mentorship feedback is append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum mentorship feedback is append-only.',
        ));
    }

    /** @return BelongsTo<ForumMentorship, $this> */
    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(ForumMentorship::class, 'forum_mentorship_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
