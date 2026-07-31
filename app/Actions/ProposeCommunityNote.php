<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CommunityNoteData;
use App\Enums\ForumCommunityNoteStatus;
use App\Models\ForumCommunityNote;
use App\Models\User;
use App\Services\CommunityNoteEvidenceValidator;
use App\Services\CommunityReviewEligibility;
use App\Services\ForumReviewAudit;
use App\Services\ForumReviewSubjectResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final readonly class ProposeCommunityNote
{
    public function __construct(
        private CommunityReviewEligibility $eligibility,
        private ForumReviewSubjectResolver $subjects,
        private ForumReviewAudit $audit,
        private CommunityNoteEvidenceValidator $evidenceValidator,
    ) {}

    public function handle(User $proposer, CommunityNoteData $data): ForumCommunityNote
    {
        if (! $this->eligibility->canPropose($proposer)) {
            throw new AuthorizationException;
        }

        if (! in_array($data->subjectType, ['forum-topic', 'forum-answer'], true)) {
            throw ValidationException::withMessages([
                'subject_type' => __('forum_review.validation.note_subject'),
            ]);
        }

        $body = trim($data->body);

        if (mb_strlen($body) < 40 || mb_strlen($body) > 2_000) {
            throw ValidationException::withMessages([
                'body' => __('forum_review.validation.note_body'),
            ]);
        }

        $this->evidenceValidator->validate($data->evidence);
        $subject = $this->subjects->resolve($proposer, $data->subjectType, $data->subjectId);
        $rateKey = "community-note:{$proposer->id}:{$data->subjectType}:{$data->subjectId}";

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw ValidationException::withMessages([
                'body' => __('forum_review.validation.note_rate_limited'),
            ]);
        }

        $openCount = ForumCommunityNote::query()
            ->where('proposer_user_id', $proposer->id)
            ->where('subject_type', $data->subjectType)
            ->where('subject_id', $data->subjectId)
            ->whereNotIn('status', [
                ForumCommunityNoteStatus::Rejected->value,
                ForumCommunityNoteStatus::Archived->value,
            ])
            ->count();

        if ($openCount >= 3) {
            throw ValidationException::withMessages([
                'body' => __('forum_review.validation.note_open_limit'),
            ]);
        }

        RateLimiter::hit($rateKey, 86_400);

        return DB::transaction(function () use ($body, $data, $proposer, $subject): ForumCommunityNote {
            $note = ForumCommunityNote::query()->create([
                'subject_type' => $data->subjectType,
                'subject_id' => $data->subjectId,
                'proposer_user_id' => $proposer->id,
                'subject_author_user_id' => $subject->authorUserId,
                'note_type' => $data->type,
                'status' => ForumCommunityNoteStatus::Proposed,
                'body' => $body,
                'evidence' => $data->evidence,
                'jurisdiction' => filled($data->jurisdiction)
                    ? trim((string) $data->jurisdiction)
                    : null,
                'species_context' => filled($data->speciesContext)
                    ? trim((string) $data->speciesContext)
                    : null,
                'is_safety_notice' => $data->type->isSafetySensitive(),
                'current_version' => 1,
                'lock_version' => 0,
            ]);
            $this->audit->noteVersion(
                $note,
                $proposer,
                'community-note-proposed',
                'proposed',
            );

            return $note;
        }, 3);
    }
}
