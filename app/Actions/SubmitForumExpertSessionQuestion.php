<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class SubmitForumExpertSessionQuestion
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumExpertSession $session,
        string $body,
        string $idempotencyKey,
    ): ForumExpertSessionQuestion {
        $this->gate->forUser($actor)->authorize('submitQuestion', $session);

        $validated = validator([
            'body' => trim($body),
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        $existing = ForumExpertSessionQuestion::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (
                $existing->author_user_id !== $actor->id
                || $existing->forum_expert_session_id !== $session->id
            ) {
                throw ValidationException::withMessages([
                    'questionForm.body' => __('forum_expert_sessions.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $rateLimitKey = 'forum-expert-question:'.hash('sha256', $actor->actor_key);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            throw ValidationException::withMessages([
                'questionForm.body' => __('forum_expert_sessions.validation.question_rate_limit'),
            ]);
        }

        return DB::transaction(function () use ($actor, $idempotencyKey, $rateLimitKey, $session, $validated): ForumExpertSessionQuestion {
            $lockedSession = ForumExpertSession::query()
                ->lockForUpdate()
                ->findOrFail($session->id);
            $this->gate->forUser($actor)->authorize('submitQuestion', $lockedSession);

            $existing = ForumExpertSessionQuestion::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $this->validatedReplay($existing, $actor, $lockedSession);
            }

            if ($lockedSession->questions()->where('author_user_id', $actor->id)->count() >= 5) {
                throw ValidationException::withMessages([
                    'questionForm.body' => __('forum_expert_sessions.validation.question_limit'),
                ]);
            }

            $position = (int) $lockedSession->questions()->max('queue_position') + 1;
            $question = ForumExpertSessionQuestion::query()->createOrFirst(
                ['idempotency_key' => $idempotencyKey],
                [
                    'forum_expert_session_id' => $lockedSession->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'question-'.Str::lower((string) Str::ulid()),
                    'body' => $validated['body'],
                    'status' => ForumExpertQuestionStatus::Queued,
                    'moderation_status' => ForumExpertQuestionModerationStatus::Pending,
                    'queue_position' => $position,
                ],
            );

            if (! $question->wasRecentlyCreated) {
                return $this->validatedReplay($question, $actor, $lockedSession);
            }

            $this->audit->record(
                session: $lockedSession,
                actor: $actor,
                eventType: 'question-submitted',
                reasonCode: 'question-submitted',
                summaryTranslationKey: 'forum_expert_sessions.history.question_submitted',
                question: $question,
                toStatus: ForumExpertQuestionStatus::Queued->value,
                metadata: ['queue_position' => $position],
                idempotencyKey: 'expert-session:question:'.$idempotencyKey,
            );

            RateLimiter::hit($rateLimitKey, 3600);

            return $question;
        }, 3);
    }

    private function validatedReplay(
        ForumExpertSessionQuestion $question,
        User $actor,
        ForumExpertSession $session,
    ): ForumExpertSessionQuestion {
        if (
            $question->author_user_id !== $actor->id
            || $question->forum_expert_session_id !== $session->id
        ) {
            throw ValidationException::withMessages([
                'questionForm.body' => __('forum_expert_sessions.validation.idempotency_conflict'),
            ]);
        }

        return $question;
    }
}
