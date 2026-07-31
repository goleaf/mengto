<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewPanelState;
use App\Enums\ForumReviewPanelType;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\CommunityReviewEligibility;
use App\Services\ForumReviewAudit;
use App\Services\ForumReviewSubjectResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumReviewPanel
{
    public function __construct(
        private CommunityReviewEligibility $eligibility,
        private ForumReviewSubjectResolver $subjects,
        private ForumReviewAudit $audit,
    ) {}

    /**
     * @param  list<int>  $conflictedUserIds
     */
    public function handle(
        User $requester,
        string $subjectType,
        int $subjectId,
        ForumReviewPanelType $panelType,
        int $requiredReviewers = 3,
        array $conflictedUserIds = [],
    ): ForumReviewPanel {
        if (! $this->eligibility->canPropose($requester)) {
            throw new AuthorizationException;
        }

        if ($requiredReviewers < 2 || $requiredReviewers > 7) {
            throw ValidationException::withMessages([
                'required_reviewers' => __('forum_review.validation.reviewer_count'),
            ]);
        }

        $subject = $this->subjects->resolve($requester, $subjectType, $subjectId);
        $activeKey = implode(':', [$subjectType, $subjectId, $panelType->value]);
        $excluded = array_values(array_unique(array_filter([
            $requester->id,
            $subject->authorUserId,
            ...$conflictedUserIds,
        ], static fn (?int $id): bool => $id !== null)));

        return DB::transaction(function () use (
            $activeKey,
            $excluded,
            $panelType,
            $requester,
            $requiredReviewers,
            $subject,
            $subjectId,
            $subjectType,
        ): ForumReviewPanel {
            $existing = ForumReviewPanel::query()
                ->where('active_key', $activeKey)
                ->lockForUpdate()
                ->first();

            if ($existing instanceof ForumReviewPanel) {
                return $existing;
            }

            $deadline = now()->addDays(7);
            $panel = ForumReviewPanel::query()->create([
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'panel_type' => $panelType,
                'risk_class' => 'low',
                'requested_by_user_id' => $requester->id,
                'state' => ForumReviewPanelState::AwaitingAssignment,
                'required_reviewers' => $requiredReviewers,
                'active_key' => $activeKey,
                'review_deadline_at' => $deadline,
                'public_context' => $subject->publicContext(),
                'metadata' => [
                    'excluded_user_ids' => $excluded,
                    'private_evidence_available' => false,
                ],
            ]);

            $reviewers = $this->eligibility->balancedReviewers(
                $panel,
                $excluded,
                $requiredReviewers,
            );

            foreach ($reviewers as $reviewer) {
                ForumReviewAssignment::query()->create([
                    'forum_review_panel_id' => $panel->id,
                    'reviewer_user_id' => $reviewer->id,
                    'state' => ForumReviewAssignmentState::Assigned,
                    'anonymous_reviewer_key' => hash_hmac(
                        'sha256',
                        "{$panel->id}:{$reviewer->id}",
                        (string) config('app.key'),
                    ),
                    'assigned_at' => now(),
                    'review_deadline_at' => $deadline,
                    'idempotency_key' => "panel:{$panel->id}:reviewer:{$reviewer->id}",
                ]);
            }

            $state = $reviewers->isEmpty()
                ? ForumReviewPanelState::AwaitingAssignment
                : ForumReviewPanelState::InReview;
            $panel->forceFill(['state' => $state])->save();
            $this->audit->panelEvent(
                $panel,
                $requester,
                'created',
                null,
                $state->value,
                'community-review-requested',
                [
                    'assigned_reviewer_count' => $reviewers->count(),
                    'required_reviewer_count' => $requiredReviewers,
                ],
                "panel:{$panel->id}:created",
            );

            return $panel->refresh();
        }, 3);
    }
}
