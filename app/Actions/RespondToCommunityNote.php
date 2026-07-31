<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumCommunityNoteStatus;
use App\Models\ForumCommunityNote;
use App\Models\User;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RespondToCommunityNote
{
    public function __construct(private ForumReviewAudit $audit) {}

    public function handle(
        User $author,
        ForumCommunityNote $note,
        string $response,
    ): ForumCommunityNote {
        if (
            ! $author->isActive()
            || $note->subject_author_user_id !== $author->id
        ) {
            throw new AuthorizationException;
        }

        $response = trim($response);

        if (mb_strlen($response) < 20 || mb_strlen($response) > 2_000) {
            throw ValidationException::withMessages([
                'author_response' => __('forum_review.validation.author_response'),
            ]);
        }

        return DB::transaction(function () use ($author, $note, $response): ForumCommunityNote {
            $note = ForumCommunityNote::query()
                ->lockForUpdate()
                ->findOrFail($note->id);

            if (! in_array($note->status, [
                ForumCommunityNoteStatus::InReview,
                ForumCommunityNoteStatus::AwaitingAuthorResponse,
                ForumCommunityNoteStatus::CommunityAssessed,
            ], true)) {
                throw ValidationException::withMessages([
                    'note' => __('forum_review.validation.author_response_closed'),
                ]);
            }

            $note->forceFill([
                'author_response' => $response,
                'status' => ForumCommunityNoteStatus::AwaitingAuthorResponse,
                'current_version' => $note->current_version + 1,
                'lock_version' => $note->lock_version + 1,
            ])->save();
            $this->audit->noteVersion(
                $note,
                $author,
                'subject-author-response',
                'author-response',
            );

            return $note->refresh();
        }, 3);
    }
}
