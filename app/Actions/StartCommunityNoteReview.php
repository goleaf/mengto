<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumReviewPanelType;
use App\Models\ForumCommunityNote;
use App\Models\User;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class StartCommunityNoteReview
{
    public function __construct(
        private CreateForumReviewPanel $createPanel,
        private ForumReviewAudit $audit,
    ) {}

    public function handle(User $actor, ForumCommunityNote $note): ForumCommunityNote
    {
        if (
            ! $actor->isAdministrator()
            && $note->proposer_user_id !== $actor->id
        ) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $note): ForumCommunityNote {
            $note = ForumCommunityNote::query()
                ->lockForUpdate()
                ->findOrFail($note->id);

            if ($note->forum_review_panel_id !== null) {
                return $note;
            }

            if (
                ! in_array($note->status, [
                    ForumCommunityNoteStatus::Proposed,
                    ForumCommunityNoteStatus::GatheringEvidence,
                ], true)
                || $note->evidence === []
            ) {
                throw ValidationException::withMessages([
                    'note' => __('forum_review.validation.note_not_ready'),
                ]);
            }

            $panel = $this->createPanel->handle(
                $actor,
                'community-note',
                $note->id,
                ForumReviewPanelType::ContentQuality,
                3,
                array_values(array_filter([
                    $note->proposer_user_id,
                    $note->subject_author_user_id,
                ])),
            );
            $note->forceFill([
                'forum_review_panel_id' => $panel->id,
                'status' => ForumCommunityNoteStatus::InReview,
                'current_version' => $note->current_version + 1,
                'lock_version' => $note->lock_version + 1,
            ])->save();
            $this->audit->noteVersion(
                $note,
                $actor,
                'independent-review-started',
                'review-started',
                ['panel_id' => $panel->id],
            );

            return $note->refresh();
        }, 3);
    }
}
