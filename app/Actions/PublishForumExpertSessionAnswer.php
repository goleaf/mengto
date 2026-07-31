<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertAnswerStatus;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class PublishForumExpertSessionAnswer
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    /**
     * @param  array<int, array{label: string, url: string}>  $sourceLinks
     */
    public function handle(
        User $actor,
        ForumExpertSessionQuestion $question,
        string $body,
        array $sourceLinks,
        string $idempotencyKey,
    ): ForumExpertSessionAnswer {
        $this->gate->forUser($actor)->authorize('answer', $question->session);
        $validated = $this->validate($body, $sourceLinks, $idempotencyKey);

        $existing = ForumExpertSessionAnswer::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (
                $existing->author_user_id !== $actor->id
                || $existing->forum_expert_session_question_id !== $question->id
            ) {
                throw ValidationException::withMessages([
                    'answerForm.body' => __('forum_expert_sessions.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        return DB::transaction(function () use ($actor, $idempotencyKey, $question, $validated): ForumExpertSessionAnswer {
            $locked = ForumExpertSessionQuestion::query()
                ->with('session.expertProfile')
                ->lockForUpdate()
                ->findOrFail($question->id);
            $this->gate->forUser($actor)->authorize('answer', $locked->session);

            if (
                $locked->moderation_status !== ForumExpertQuestionModerationStatus::Approved
                || ! in_array($locked->status, [
                    ForumExpertQuestionStatus::Queued,
                    ForumExpertQuestionStatus::Selected,
                ], true)
                || $locked->answer()->exists()
            ) {
                throw ValidationException::withMessages([
                    'answerForm.body' => __('forum_expert_sessions.validation.question_not_answerable'),
                ]);
            }

            $answer = ForumExpertSessionAnswer::query()->create([
                'forum_expert_session_id' => $locked->forum_expert_session_id,
                'forum_expert_session_question_id' => $locked->id,
                'author_user_id' => $actor->id,
                'stable_key' => 'answer-'.Str::lower((string) Str::ulid()),
                'idempotency_key' => $idempotencyKey,
                'body' => $validated['body'],
                'source_links' => $validated['source_links'],
                'status' => ForumExpertAnswerStatus::Published,
                'current_version' => 1,
                'answered_at' => now(),
            ]);

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => ForumExpertQuestionStatus::Answered,
                'answered_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                session: $locked->session,
                actor: $actor,
                eventType: 'answer-published',
                reasonCode: 'host-answered',
                summaryTranslationKey: 'forum_expert_sessions.history.answer_published',
                question: $locked,
                answer: $answer,
                fromStatus: $fromStatus,
                toStatus: ForumExpertQuestionStatus::Answered->value,
                metadata: ['source_count' => count($validated['source_links'])],
                idempotencyKey: 'expert-session:answer:'.$idempotencyKey,
            );

            return $answer;
        }, 3);
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $sourceLinks
     * @return array{body: string, source_links: array<int, array{label: string, url: string}>}
     */
    private function validate(string $body, array $sourceLinks, string $idempotencyKey): array
    {
        /** @var array{body: string, source_links: array<int, array{label: string, url: string}>} $validated */
        $validated = validator([
            'body' => trim($body),
            'source_links' => $sourceLinks,
            'idempotency_key' => $idempotencyKey,
        ], [
            'body' => ['required', 'string', 'min:20', 'max:20000'],
            'source_links' => ['array', 'max:10'],
            'source_links.*' => ['array:label,url'],
            'source_links.*.label' => ['required', 'string', 'max:180'],
            'source_links.*.url' => ['required', 'url:http,https', 'max:2000', 'distinct'],
            'idempotency_key' => ['required', 'uuid'],
        ])->validate();

        return $validated;
    }
}
