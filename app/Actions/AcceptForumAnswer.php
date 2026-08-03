<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReputationEventData;
use App\Enums\ForumTopicStatus;
use App\Enums\ReputationEventStatus;
use App\Models\ForumAnswer;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use App\Models\ForumTopic;
use App\Models\ForumTopicAcceptance;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\ForumTopicLifecycle;
use App\Services\ForumTopicTypeSchemaRegistry;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AcceptForumAnswer
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private RecordReputationEvent $recordReputation,
        private ReverseReputationEvent $reverseReputation,
        private ForumTopicLifecycle $lifecycle,
        private ForumTopicTypeSchemaRegistry $topicTypeSchemas,
    ) {}

    public function handle(int $answerId): ForumTopic
    {
        return DB::transaction(function () use ($answerId): ForumTopic {
            $actor = $this->actor->requireUser();
            $answer = ForumAnswer::query()
                ->lockForUpdate()
                ->findOrFail($answerId);
            $topic = ForumTopic::query()
                ->lockForUpdate()
                ->findOrFail($answer->topic_id);
            $this->gate->authorize('update', $topic);

            if (! $this->topicTypeSchemas
                ->definition($topic->type->value)
                ?->allowsAcceptedAnswers) {
                throw ValidationException::withMessages([
                    'answer_id' => __('forum.validation.accepted_answers_unavailable'),
                ]);
            }

            if (
                $answer->author_id === $actor->id
                || $answer->author_key === $actor->actor_key
            ) {
                throw ValidationException::withMessages([
                    'answer_id' => __('forum_reputation.messages.self_accept_forbidden'),
                ]);
            }

            $allowsMultiple = (bool) data_get(
                $topic->structured_data,
                'allows_multiple_accepted_answers',
                false,
            );

            if (! $allowsMultiple) {
                $this->invalidateOtherAcceptances($topic, $answer, $actor);
            }

            $acceptance = ForumTopicAcceptance::query()->updateOrCreate(
                [
                    'forum_topic_id' => $topic->id,
                    'forum_answer_id' => $answer->id,
                    'acceptance_type' => 'author',
                ],
                [
                    'accepted_by_user_id' => $actor->id,
                    'is_active' => true,
                    'accepted_at' => now(),
                    'invalidated_at' => null,
                    'invalidation_reason_code' => null,
                    'metadata' => [],
                ],
            );
            $answer->forceFill([
                'is_accepted' => true,
                'is_highlighted' => true,
            ])->save();
            $topic->forceFill([
                'accepted_answer_id' => $allowsMultiple
                    ? ($topic->accepted_answer_id ?? $answer->id)
                    : $answer->id,
            ])->save();

            if ($topic->status->canonical() !== ForumTopicStatus::Solved) {
                $topic = $this->lifecycle->transition(
                    topic: $topic,
                    target: ForumTopicStatus::Solved,
                    actor: $actor,
                    reasonCode: 'answer-accepted',
                    expectedLockVersion: $topic->lock_version,
                    idempotencyKey: "topic-answer-accepted:{$acceptance->id}",
                );
            }
            $recipient = $this->answerAuthor($answer);

            if (
                $recipient instanceof User
                && ForumReputationDimension::query()
                    ->where('stable_key', 'answer-quality')
                    ->where('is_active', true)
                    ->exists()
            ) {
                $this->recordReputation->handle(new ReputationEventData(
                    recipient: $recipient,
                    dimension: 'answer-quality',
                    eventType: 'author-accepted-answer',
                    sourceEntityType: 'forum-answer',
                    sourceEntityId: (string) $answer->id,
                    amount: 5,
                    reasonCode: 'answer-accepted',
                    explanationTranslationKey: 'forum_reputation.events.answer_accepted',
                    idempotencyKey: 'answer-acceptance:'.$acceptance->id,
                    actor: $actor,
                    forumCategoryId: $topic->forum_category_id,
                ));
            }

            return $topic;
        }, 3);
    }

    private function invalidateOtherAcceptances(
        ForumTopic $topic,
        ForumAnswer $selected,
        User $actor,
    ): void {
        $acceptances = ForumTopicAcceptance::query()
            ->where('forum_topic_id', $topic->id)
            ->where('forum_answer_id', '!=', $selected->id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->get();

        foreach ($acceptances as $acceptance) {
            $acceptance->forceFill([
                'is_active' => false,
                'invalidated_at' => now(),
                'invalidation_reason_code' => 'another-answer-selected',
            ])->save();
            ForumAnswer::query()
                ->whereKey($acceptance->forum_answer_id)
                ->update(['is_accepted' => false]);
            $event = ForumReputationEvent::query()
                ->where('source_entity_type', 'forum-answer')
                ->where('source_entity_id', (string) $acceptance->forum_answer_id)
                ->where('event_type', 'author-accepted-answer')
                ->where('status', ReputationEventStatus::Active->value)
                ->first();

            if ($event instanceof ForumReputationEvent) {
                $this->reverseReputation->handle(
                    $event,
                    'answer-acceptance-replaced',
                    $actor,
                );
            }
        }
    }

    private function answerAuthor(ForumAnswer $answer): ?User
    {
        return $answer->author_id !== null
            ? User::query()->find($answer->author_id)
            : User::query()->where('actor_key', $answer->author_key)->first();
    }
}
