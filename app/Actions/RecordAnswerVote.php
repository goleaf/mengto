<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReputationEventData;
use App\Enums\ForumVoteValue;
use App\Models\ForumAnswer;
use App\Models\ForumReputationDimension;
use App\Models\ForumTopic;
use App\Models\ForumVote;
use App\Models\User;
use App\Services\ForumActor;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RecordAnswerVote
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private RecordReputationEvent $recordReputation,
        private ReverseReputationEvent $reverseReputation,
    ) {}

    public function handle(int $answerId, string $value, ?string $reason): ForumTopic
    {
        $voteValue = ForumVoteValue::tryFrom($value);

        if (! $voteValue instanceof ForumVoteValue) {
            throw ValidationException::withMessages([
                'value' => __('forum.validation.answer_rating'),
            ]);
        }

        return DB::transaction(function () use ($answerId, $reason, $voteValue): ForumTopic {
            $actor = $this->actor->requireUser();
            $answer = ForumAnswer::query()
                ->with('topic')
                ->lockForUpdate()
                ->findOrFail($answerId);
            $topic = $answer->topic;
            $this->gate->authorize('view', $topic);

            if (
                $answer->author_id === $actor->id
                || $answer->author_key === $actor->actor_key
            ) {
                throw ValidationException::withMessages([
                    'value' => __('forum_reputation.messages.self_vote_forbidden'),
                ]);
            }

            $vote = ForumVote::query()
                ->where('answer_id', $answer->id)
                ->where('user_key', $actor->actor_key)
                ->lockForUpdate()
                ->first();

            if (! $vote instanceof ForumVote) {
                $vote = new ForumVote([
                    'answer_id' => $answer->id,
                    'user_id' => $actor->id,
                    'user_key' => $actor->actor_key,
                    'effect_revision' => 0,
                ]);
            }

            $previousValue = $vote->exists ? $vote->value : null;

            if ($previousValue === $voteValue) {
                $vote->forceFill(['reason' => $reason])->save();

                return $topic;
            }

            if (
                $previousValue === ForumVoteValue::Helpful
                && $vote->reputation_event_id !== null
            ) {
                $event = $vote->reputationEvent()->first();

                if ($event !== null) {
                    $this->reverseReputation->handle($event, 'vote-changed', $actor);
                }
            }

            $revision = $vote->effect_revision + 1;
            $event = null;
            $recipient = $this->answerAuthor($answer);

            if (
                $voteValue === ForumVoteValue::Helpful
                && $recipient instanceof User
                && ForumReputationDimension::query()
                    ->where('stable_key', 'helpfulness')
                    ->where('is_active', true)
                    ->exists()
            ) {
                $event = $this->recordReputation->handle(new ReputationEventData(
                    recipient: $recipient,
                    dimension: 'helpfulness',
                    eventType: 'helpful-answer-vote',
                    sourceEntityType: 'forum-answer',
                    sourceEntityId: (string) $answer->id,
                    amount: 1,
                    reasonCode: 'helpful-vote',
                    explanationTranslationKey: 'forum_reputation.events.helpful_vote',
                    idempotencyKey: "answer-vote:{$answer->id}:{$actor->id}:{$revision}",
                    actor: $actor,
                    forumCategoryId: $topic->forum_category_id,
                ));
            }

            $vote->forceFill([
                'user_id' => $actor->id,
                'value' => $voteValue,
                'reason' => $reason,
                'effect_revision' => $revision,
                'reputation_event_id' => $event?->id,
            ])->save();
            $answer->forceFill([
                'helpful_count' => ForumVote::query()
                    ->where('answer_id', $answer->id)
                    ->where('value', ForumVoteValue::Helpful->value)
                    ->count(),
            ])->save();

            return $topic;
        }, 3);
    }

    private function answerAuthor(ForumAnswer $answer): ?User
    {
        if ($answer->author_id !== null) {
            return User::query()->find($answer->author_id);
        }

        return User::query()->where('actor_key', $answer->author_key)->first();
    }
}
