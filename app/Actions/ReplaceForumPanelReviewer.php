<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumReviewAssignmentState;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\CommunityReviewEligibility;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ReplaceForumPanelReviewer
{
    public function __construct(
        private CommunityReviewEligibility $eligibility,
        private ForumReviewAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumReviewAssignment $assignment,
        string $reason,
    ): ForumReviewAssignment {
        if (! $actor->isAdministrator()) {
            throw new AuthorizationException;
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages([
                'reason' => __('forum_review.validation.replacement_reason'),
            ]);
        }

        return DB::transaction(function () use ($actor, $assignment, $reason): ForumReviewAssignment {
            $assignment = ForumReviewAssignment::query()
                ->lockForUpdate()
                ->findOrFail($assignment->id);
            $panel = ForumReviewPanel::query()
                ->lockForUpdate()
                ->findOrFail($assignment->forum_review_panel_id);

            if (
                $assignment->state !== ForumReviewAssignmentState::Assigned
                || ! $panel->state->isOpen()
            ) {
                throw ValidationException::withMessages([
                    'review' => __('forum_review.validation.assignment_closed'),
                ]);
            }

            $assignment->forceFill([
                'state' => ForumReviewAssignmentState::Replaced,
                'reasoning' => trim($reason),
            ])->save();
            $excluded = array_map(
                'intval',
                (array) data_get($panel->metadata, 'excluded_user_ids', []),
            );
            $reviewer = $this->eligibility
                ->balancedReviewers($panel, $excluded, 1)
                ->first();

            if (! $reviewer instanceof User) {
                throw ValidationException::withMessages([
                    'review' => __('forum_review.validation.no_replacement'),
                ]);
            }

            $replacement = ForumReviewAssignment::query()->create([
                'forum_review_panel_id' => $panel->id,
                'reviewer_user_id' => $reviewer->id,
                'state' => ForumReviewAssignmentState::Assigned,
                'anonymous_reviewer_key' => hash_hmac(
                    'sha256',
                    "{$panel->id}:{$reviewer->id}",
                    (string) config('app.key'),
                ),
                'replacement_for_assignment_id' => $assignment->id,
                'assigned_at' => now(),
                'review_deadline_at' => $panel->review_deadline_at,
                'idempotency_key' => "panel:{$panel->id}:reviewer:{$reviewer->id}",
            ]);
            $this->audit->panelEvent(
                $panel,
                $actor,
                'reviewer-replaced',
                $panel->state->value,
                $panel->state->value,
                'manual-replacement',
                [
                    'replaced_assignment_id' => $assignment->id,
                    'replacement_assignment_id' => $replacement->id,
                ],
            );

            return $replacement;
        }, 3);
    }
}
