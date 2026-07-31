<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumMentorshipEventType;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;
use App\Models\User;
use App\Services\MentorshipAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class SubmitMentorshipFeedback
{
    public function __construct(
        private Gate $gate,
        private MentorshipAudit $audit,
    ) {}

    public function handle(
        User $author,
        ForumMentorship $mentorship,
        int $rating,
        string $summary,
        ?bool $wouldRecommend,
        ?string $privateNote,
    ): ForumMentorshipFeedback {
        Validator::make([
            'rating' => $rating,
            'summary' => $summary,
            'private_note' => $privateNote,
        ], [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'summary' => ['required', 'string', 'min:2', 'max:1000'],
            'private_note' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use (
            $author,
            $mentorship,
            $privateNote,
            $rating,
            $summary,
            $wouldRecommend,
        ): ForumMentorshipFeedback {
            $locked = ForumMentorship::query()
                ->lockForUpdate()
                ->findOrFail($mentorship->id);
            $this->gate->forUser($author)->authorize('feedback', $locked);

            if ($locked->feedback()->where('author_user_id', $author->id)->exists()) {
                throw ValidationException::withMessages([
                    'feedback' => __('forum_mentorship.validation.feedback_already_submitted'),
                ]);
            }

            $recipientId = $locked->counterpartId($author);

            if ($recipientId === null) {
                throw ValidationException::withMessages([
                    'feedback' => __('forum_mentorship.validation.participant_required'),
                ]);
            }

            $feedback = ForumMentorshipFeedback::query()->create([
                'forum_mentorship_id' => $locked->id,
                'author_user_id' => $author->id,
                'recipient_user_id' => $recipientId,
                'rating' => $rating,
                'summary' => trim($summary),
                'would_recommend' => $wouldRecommend,
                'private_note' => filled($privateNote) ? trim((string) $privateNote) : null,
                'created_at' => now(),
            ]);

            $this->audit->record(
                mentorship: $locked,
                actor: $author,
                eventType: ForumMentorshipEventType::FeedbackSubmitted,
                reasonCode: 'feedback-submitted',
                summaryTranslationKey: 'forum_mentorship.events.feedback-submitted',
                metadata: ['rating' => $rating],
                idempotencyKey: "mentorship:{$locked->id}:feedback:{$author->id}",
            );

            return $feedback;
        }, 3);
    }
}
