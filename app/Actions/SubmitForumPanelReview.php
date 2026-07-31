<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewDecision;
use App\Enums\ForumReviewPanelState;
use App\Models\ForumCommunityNote;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\CommunityReviewEligibility;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class SubmitForumPanelReview
{
    public function __construct(
        private CommunityReviewEligibility $eligibility,
        private ForumReviewAudit $audit,
    ) {}

    public function handle(
        User $reviewer,
        ForumReviewAssignment $assignment,
        ForumReviewDecision $decision,
        string $reasoning,
        bool $hasConflict = false,
        ?string $conflictType = null,
    ): ForumReviewAssignment {
        if (
            $assignment->reviewer_user_id !== $reviewer->id
            || ! $this->eligibility->canReview($reviewer)
        ) {
            throw new AuthorizationException;
        }

        $reasoning = trim($reasoning);

        if (mb_strlen($reasoning) < 20 || mb_strlen($reasoning) > 2_000) {
            throw ValidationException::withMessages([
                'reasoning' => __('forum_review.validation.reasoning_length'),
            ]);
        }

        if ($hasConflict && blank($conflictType)) {
            throw ValidationException::withMessages([
                'conflict_type' => __('forum_review.validation.conflict_required'),
            ]);
        }

        /** @var array{assignment: ForumReviewAssignment, expired: bool} $result */
        $result = DB::transaction(function () use (
            $assignment,
            $conflictType,
            $decision,
            $hasConflict,
            $reasoning,
            $reviewer,
        ): array {
            $lockedAssignment = ForumReviewAssignment::query()
                ->lockForUpdate()
                ->findOrFail($assignment->id);
            $panel = ForumReviewPanel::query()
                ->lockForUpdate()
                ->findOrFail($lockedAssignment->forum_review_panel_id);

            if ($lockedAssignment->state !== ForumReviewAssignmentState::Assigned) {
                throw ValidationException::withMessages([
                    'review' => __('forum_review.validation.assignment_closed'),
                ]);
            }

            if (! $panel->state->isOpen() || $panel->review_deadline_at->isPast()) {
                $this->expirePanel($panel, $lockedAssignment);

                return [
                    'assignment' => $lockedAssignment->refresh(),
                    'expired' => true,
                ];
            }

            if ($hasConflict) {
                $lockedAssignment->forceFill([
                    'state' => ForumReviewAssignmentState::Recused,
                    'reasoning' => $reasoning,
                    'has_conflict' => true,
                    'conflict_type' => trim((string) $conflictType),
                    'recused_at' => now(),
                ])->save();
                $this->audit->panelEvent(
                    $panel,
                    $reviewer,
                    'reviewer-recused',
                    $panel->state->value,
                    $panel->state->value,
                    'reviewer-conflict',
                    ['assignment_id' => $lockedAssignment->id],
                    "assignment:{$lockedAssignment->id}:recused",
                );
                $this->assignReplacement($panel, $lockedAssignment);

                return [
                    'assignment' => $lockedAssignment->refresh(),
                    'expired' => false,
                ];
            }

            $lockedAssignment->forceFill([
                'state' => ForumReviewAssignmentState::Submitted,
                'decision' => $decision,
                'reasoning' => $reasoning,
                'has_conflict' => false,
                'submitted_at' => now(),
            ])->save();
            $this->audit->panelEvent(
                $panel,
                $reviewer,
                'review-submitted',
                $panel->state->value,
                $panel->state->value,
                'independent-review',
                [
                    'assignment_id' => $lockedAssignment->id,
                    'decision' => $decision->value,
                ],
                "assignment:{$lockedAssignment->id}:submitted",
            );
            $this->recalculate($panel, $reviewer);

            return [
                'assignment' => $lockedAssignment->refresh(),
                'expired' => false,
            ];
        }, 3);

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'review' => __('forum_review.validation.panel_expired'),
            ]);
        }

        return $result['assignment'];
    }

    private function assignReplacement(
        ForumReviewPanel $panel,
        ForumReviewAssignment $replaced,
    ): void {
        $excluded = array_map(
            'intval',
            (array) data_get($panel->metadata, 'excluded_user_ids', []),
        );
        $reviewer = $this->eligibility
            ->balancedReviewers($panel, $excluded, 1)
            ->first();

        if (! $reviewer instanceof User) {
            return;
        }

        ForumReviewAssignment::query()->create([
            'forum_review_panel_id' => $panel->id,
            'reviewer_user_id' => $reviewer->id,
            'state' => ForumReviewAssignmentState::Assigned,
            'anonymous_reviewer_key' => hash_hmac(
                'sha256',
                "{$panel->id}:{$reviewer->id}",
                (string) config('app.key'),
            ),
            'replacement_for_assignment_id' => $replaced->id,
            'assigned_at' => now(),
            'review_deadline_at' => $panel->review_deadline_at,
            'idempotency_key' => "panel:{$panel->id}:reviewer:{$reviewer->id}",
        ]);
        $this->audit->panelEvent(
            $panel,
            null,
            'reviewer-replaced',
            $panel->state->value,
            $panel->state->value,
            'balanced-replacement',
            ['replaced_assignment_id' => $replaced->id],
        );
    }

    private function recalculate(ForumReviewPanel $panel, User $actor): void
    {
        $decisions = $panel->assignments()
            ->where('state', ForumReviewAssignmentState::Submitted->value)
            ->whereIn('decision', [
                ForumReviewDecision::Support->value,
                ForumReviewDecision::Oppose->value,
                ForumReviewDecision::ChangesRequested->value,
            ])
            ->pluck('decision');

        if ($decisions->count() < $panel->required_reviewers) {
            return;
        }

        $counts = $decisions->countBy();
        $support = (int) $counts->get(ForumReviewDecision::Support->value, 0);
        $oppose = (int) $counts->get(ForumReviewDecision::Oppose->value, 0);
        $changes = (int) $counts->get(ForumReviewDecision::ChangesRequested->value, 0);
        $decision = $support > max($oppose, $changes)
            ? ForumReviewDecision::Support
            : ($oppose > $changes
                ? ForumReviewDecision::Oppose
                : ForumReviewDecision::ChangesRequested);
        $fromState = $panel->state;
        $panel->forceFill([
            'state' => ForumReviewPanelState::Decided,
            'decision' => $decision,
            'decision_reason' => 'community-panel-quorum',
            'decided_at' => now(),
            'closed_at' => now(),
            'active_key' => null,
        ])->save();
        $this->audit->panelEvent(
            $panel,
            $actor,
            'decision-reached',
            $fromState->value,
            ForumReviewPanelState::Decided->value,
            'community-panel-quorum',
            [
                'support' => $support,
                'oppose' => $oppose,
                'changes_requested' => $changes,
            ],
            "panel:{$panel->id}:decision",
        );

        $note = ForumCommunityNote::query()
            ->where('forum_review_panel_id', $panel->id)
            ->lockForUpdate()
            ->first();

        if (! $note instanceof ForumCommunityNote) {
            return;
        }

        $note->forceFill([
            'status' => ForumCommunityNoteStatus::CommunityAssessed,
            'current_version' => $note->current_version + 1,
            'lock_version' => $note->lock_version + 1,
        ])->save();
        $this->audit->noteVersion(
            $note,
            $actor,
            'community-panel-assessment',
            'community-assessed',
            ['panel_decision' => $decision->value],
        );
    }

    private function expirePanel(
        ForumReviewPanel $panel,
        ForumReviewAssignment $assignment,
    ): void {
        $fromState = $panel->state;
        $panel->forceFill([
            'state' => ForumReviewPanelState::Expired,
            'closed_at' => now(),
            'active_key' => null,
        ])->save();
        $assignment->forceFill([
            'state' => ForumReviewAssignmentState::Expired,
        ])->save();
        $this->audit->panelEvent(
            $panel,
            null,
            'expired',
            $fromState->value,
            ForumReviewPanelState::Expired->value,
            'review-deadline-expired',
            [],
            "panel:{$panel->id}:expired",
        );
    }
}
