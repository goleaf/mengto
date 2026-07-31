<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CommunityNoteData;
use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewPanelState;
use App\Models\ForumCommunityNote;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\CommunityNoteEvidenceValidator;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ReviseCommunityNote
{
    public function __construct(
        private ForumReviewAudit $audit,
        private CommunityNoteEvidenceValidator $evidenceValidator,
    ) {}

    public function handle(
        User $editor,
        ForumCommunityNote $note,
        CommunityNoteData $data,
        string $reason,
    ): ForumCommunityNote {
        if (
            ! $editor->isAdministrator()
            && $note->proposer_user_id !== $editor->id
        ) {
            throw new AuthorizationException;
        }

        $reason = trim($reason);
        $body = trim($data->body);

        if (
            mb_strlen($reason) < 10
            || mb_strlen($reason) > 2_000
            || mb_strlen($body) < 40
            || mb_strlen($body) > 2_000
        ) {
            throw ValidationException::withMessages([
                'body' => __('forum_review.validation.revision'),
            ]);
        }

        $this->evidenceValidator->validate($data->evidence);

        return DB::transaction(function () use ($body, $data, $editor, $note, $reason): ForumCommunityNote {
            $note = ForumCommunityNote::query()
                ->lockForUpdate()
                ->findOrFail($note->id);

            if ($note->lock_version !== $data->expectedLockVersion) {
                throw ValidationException::withMessages([
                    'note' => __('forum_review.validation.edit_conflict'),
                ]);
            }

            if (
                ! $editor->isAdministrator()
                && $note->status->isPublic()
            ) {
                throw new AuthorizationException;
            }

            if (! $note->status->isOpen()) {
                throw ValidationException::withMessages([
                    'note' => __('forum_review.validation.note_closed'),
                ]);
            }

            $wasPublic = $note->status->isPublic();
            $status = $wasPublic
                ? ForumCommunityNoteStatus::Revised
                : ForumCommunityNoteStatus::GatheringEvidence;
            $panel = $note->forum_review_panel_id === null
                ? null
                : ForumReviewPanel::query()
                    ->lockForUpdate()
                    ->find($note->forum_review_panel_id);

            if (! $wasPublic && $panel instanceof ForumReviewPanel && $panel->state->isOpen()) {
                $fromState = $panel->state;
                $panel->forceFill([
                    'state' => ForumReviewPanelState::Cancelled,
                    'closed_at' => now(),
                    'active_key' => null,
                ])->save();
                $panel->assignments()
                    ->where('state', ForumReviewAssignmentState::Assigned->value)
                    ->update(['state' => ForumReviewAssignmentState::Cancelled->value]);
                $this->audit->panelEvent(
                    $panel,
                    $editor,
                    'cancelled',
                    $fromState->value,
                    ForumReviewPanelState::Cancelled->value,
                    'note-revised-during-review',
                    ['note_id' => $note->id],
                    "panel:{$panel->id}:note-revised",
                );
            }

            $note->forceFill([
                'note_type' => $data->type,
                'body' => $body,
                'evidence' => $data->evidence,
                'jurisdiction' => filled($data->jurisdiction)
                    ? trim((string) $data->jurisdiction)
                    : null,
                'species_context' => filled($data->speciesContext)
                    ? trim((string) $data->speciesContext)
                    : null,
                'is_safety_notice' => $data->type->isSafetySensitive(),
                'status' => $status,
                'forum_review_panel_id' => $wasPublic
                    ? $note->forum_review_panel_id
                    : null,
                'current_version' => $note->current_version + 1,
                'lock_version' => $note->lock_version + 1,
                'revalidation_due_at' => $wasPublic
                    ? now()->addDays($data->type->isSafetySensitive() ? 90 : 180)
                    : $note->revalidation_due_at,
            ])->save();
            $this->audit->noteVersion(
                $note,
                $editor,
                $reason,
                'revised',
            );

            return $note->refresh();
        }, 3);
    }
}
