<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventReviewStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventReview;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitForumEventReview
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        int $rating,
        string $title,
        string $body,
        string $idempotencyKey,
    ): ForumEventReview {
        $this->gate->forUser($actor)->authorize('review', $event);
        Validator::make([
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'idempotency_key' => $idempotencyKey,
        ], [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $body,
            $event,
            $idempotencyKey,
            $rating,
            $title,
        ): ForumEventReview {
            ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $existing = ForumEventReview::query()
                ->where('forum_event_id', $event->id)
                ->where('reviewer_user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (hash_equals($existing->idempotency_key, $idempotencyKey)) {
                    return $existing;
                }

                throw ValidationException::withMessages([
                    'reviewForm.body' => __('forum_events.validation.review_exists'),
                ]);
            }

            $review = ForumEventReview::query()->create([
                'forum_event_id' => $event->id,
                'reviewer_user_id' => $actor->id,
                'stable_key' => 'event-review-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $idempotencyKey,
                'rating' => $rating,
                'title' => trim($title),
                'body' => trim($body),
                'status' => ForumEventReviewStatus::Published,
            ]);
            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'review-submitted',
                reasonCode: 'post-event-review',
                summaryTranslationKey: 'forum_events.history.review_submitted',
                subject: $actor,
                metadata: [
                    'review_id' => $review->id,
                    'rating' => $rating,
                ],
                idempotencyKey: 'event:review:'.$idempotencyKey,
            );

            return $review;
        }, 3);
    }
}
