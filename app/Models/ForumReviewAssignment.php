<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewDecision;
use Carbon\CarbonImmutable;
use Database\Factories\ForumReviewAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $anonymous_reviewer_key
 * @property ForumReviewDecision|null $decision
 * @property int $forum_review_panel_id
 * @property bool $has_conflict
 * @property int $id
 * @property string|null $reasoning
 * @property CarbonImmutable $review_deadline_at
 * @property int $reviewer_user_id
 * @property ForumReviewAssignmentState $state
 */
final class ForumReviewAssignment extends Model
{
    /** @use HasFactory<ForumReviewAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_review_panel_id',
        'reviewer_user_id',
        'state',
        'decision',
        'reasoning',
        'has_conflict',
        'conflict_type',
        'anonymous_reviewer_key',
        'replacement_for_assignment_id',
        'assigned_at',
        'review_deadline_at',
        'submitted_at',
        'recused_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'state' => ForumReviewAssignmentState::class,
            'decision' => ForumReviewDecision::class,
            'has_conflict' => 'boolean',
            'assigned_at' => 'immutable_datetime',
            'review_deadline_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'recused_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumReviewPanel, $this> */
    public function panel(): BelongsTo
    {
        return $this->belongsTo(ForumReviewPanel::class, 'forum_review_panel_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return BelongsTo<ForumReviewAssignment, $this> */
    public function replacementFor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replacement_for_assignment_id');
    }
}
