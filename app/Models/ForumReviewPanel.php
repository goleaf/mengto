<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumReviewDecision;
use App\Enums\ForumReviewPanelState;
use App\Enums\ForumReviewPanelType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumReviewPanelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $active_key
 * @property ForumReviewDecision|null $decision
 * @property CarbonImmutable|null $decided_at
 * @property int $id
 * @property ForumReviewPanelType $panel_type
 * @property int $required_reviewers
 * @property CarbonImmutable $review_deadline_at
 * @property string $risk_class
 * @property ForumReviewPanelState $state
 * @property int $subject_id
 * @property string $subject_type
 */
final class ForumReviewPanel extends Model
{
    /** @use HasFactory<ForumReviewPanelFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'panel_type',
        'risk_class',
        'requested_by_user_id',
        'state',
        'required_reviewers',
        'decision',
        'decision_reason',
        'moderator_override_by_user_id',
        'forum_moderation_case_id',
        'forum_moderation_appeal_id',
        'appealed_by_user_id',
        'appeal_reason',
        'appealed_at',
        'active_key',
        'review_deadline_at',
        'decided_at',
        'closed_at',
        'public_context',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'panel_type' => ForumReviewPanelType::class,
            'state' => ForumReviewPanelState::class,
            'decision' => ForumReviewDecision::class,
            'required_reviewers' => 'integer',
            'review_deadline_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'appealed_at' => 'immutable_datetime',
            'public_context' => 'array',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function moderatorOverrideActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_override_by_user_id');
    }

    /** @return BelongsTo<ForumModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ForumModerationCase::class);
    }

    /** @return BelongsTo<ForumModerationAppeal, $this> */
    public function moderationAppeal(): BelongsTo
    {
        return $this->belongsTo(ForumModerationAppeal::class);
    }

    /** @return BelongsTo<User, $this> */
    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appealed_by_user_id');
    }

    /** @return HasMany<ForumReviewAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(ForumReviewAssignment::class);
    }

    /** @return HasMany<ForumReviewPanelEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumReviewPanelEvent::class);
    }
}
