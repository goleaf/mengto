<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumModerationAppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumModerationAppeal extends Model
{
    /** @use HasFactory<ForumModerationAppealFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_moderation_action_id',
        'appellant_user_id',
        'reviewer_user_id',
        'status',
        'reason',
        'evidence',
        'decision_reason',
        'submitted_at',
        'decided_at',
    ];

    protected $hidden = ['evidence'];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'submitted_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumModerationAction, $this> */
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(
            ForumModerationAction::class,
            'forum_moderation_action_id',
        );
    }

    /** @return BelongsTo<User, $this> */
    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appellant_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return HasMany<ForumReviewPanel, $this> */
    public function reviewPanels(): HasMany
    {
        return $this->hasMany(ForumReviewPanel::class);
    }
}
