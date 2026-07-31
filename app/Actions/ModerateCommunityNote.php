<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumReviewDecision;
use App\Enums\ForumReviewPanelState;
use App\Models\ForumCommunityNote;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ModerateCommunityNote
{
    public function __construct(private ForumReviewAudit $audit) {}

    public function handle(
        User $moderator,
        ForumCommunityNote $note,
        ForumCommunityNoteStatus $targetStatus,
        string $reason,
    ): ForumCommunityNote {
        if (! $moderator->isAdministrator()) {
            throw new AuthorizationException;
        }

        if (! in_array($targetStatus, [
            ForumCommunityNoteStatus::ModeratorReview,
            ForumCommunityNoteStatus::Published,
            ForumCommunityNoteStatus::Revised,
            ForumCommunityNoteStatus::Rejected,
            ForumCommunityNoteStatus::Archived,
            ForumCommunityNoteStatus::RevalidationDue,
        ], true) || mb_strlen(trim($reason)) < 20 || mb_strlen(trim($reason)) > 2_000) {
            throw ValidationException::withMessages([
                'decision' => __('forum_review.validation.moderator_decision'),
            ]);
        }

        return DB::transaction(function () use (
            $moderator,
            $note,
            $reason,
            $targetStatus,
        ): ForumCommunityNote {
            $note = ForumCommunityNote::query()
                ->lockForUpdate()
                ->findOrFail($note->id);
            $panel = $note->forum_review_panel_id === null
                ? null
                : ForumReviewPanel::query()
                    ->lockForUpdate()
                    ->find($note->forum_review_panel_id);
            $now = now();
            $decision = match ($targetStatus) {
                ForumCommunityNoteStatus::ModeratorReview => null,
                ForumCommunityNoteStatus::Published,
                ForumCommunityNoteStatus::Revised,
                ForumCommunityNoteStatus::RevalidationDue => ForumReviewDecision::Support,
                ForumCommunityNoteStatus::Rejected,
                ForumCommunityNoteStatus::Archived => ForumReviewDecision::Oppose,
            };

            $note->forceFill([
                'status' => $targetStatus,
                'moderator_user_id' => $moderator->id,
                'moderator_decision' => $decision?->value,
                'decision_reason' => trim($reason),
                'current_version' => $note->current_version + 1,
                'lock_version' => $note->lock_version + 1,
                'published_at' => in_array($targetStatus, [
                    ForumCommunityNoteStatus::Published,
                    ForumCommunityNoteStatus::Revised,
                    ForumCommunityNoteStatus::RevalidationDue,
                ], true) ? ($note->published_at ?? $now) : $note->published_at,
                'revalidation_due_at' => match ($targetStatus) {
                    ForumCommunityNoteStatus::ModeratorReview => $note->revalidation_due_at,
                    ForumCommunityNoteStatus::Published,
                    ForumCommunityNoteStatus::Revised => $now->copy()->addDays(
                        $note->is_safety_notice ? 90 : 180,
                    ),
                    ForumCommunityNoteStatus::RevalidationDue => $now,
                    ForumCommunityNoteStatus::Rejected,
                    ForumCommunityNoteStatus::Archived => null,
                },
                'archived_at' => $targetStatus === ForumCommunityNoteStatus::Archived
                    ? $now
                    : $note->archived_at,
            ])->save();
            $this->audit->noteVersion(
                $note,
                $moderator,
                trim($reason),
                "moderator-{$targetStatus->value}",
            );

            if ($panel instanceof ForumReviewPanel) {
                $fromState = $panel->state;

                if ($targetStatus === ForumCommunityNoteStatus::ModeratorReview) {
                    $this->audit->panelEvent(
                        $panel,
                        $moderator,
                        'moderator-review-requested',
                        $fromState->value,
                        $fromState->value,
                        'moderator-review-required',
                        ['note_status' => $targetStatus->value],
                    );

                    return $note->refresh();
                }

                assert($decision instanceof ForumReviewDecision);
                $panel->forceFill([
                    'state' => ForumReviewPanelState::Overridden,
                    'decision' => $decision,
                    'decision_reason' => trim($reason),
                    'moderator_override_by_user_id' => $moderator->id,
                    'decided_at' => $now,
                    'closed_at' => $now,
                    'active_key' => null,
                ])->save();
                $this->audit->panelEvent(
                    $panel,
                    $moderator,
                    'moderator-decision',
                    $fromState->value,
                    ForumReviewPanelState::Overridden->value,
                    'moderator-override',
                    [
                        'decision' => $decision->value,
                        'note_status' => $targetStatus->value,
                    ],
                );
            }

            return $note->refresh();
        }, 3);
    }
}
