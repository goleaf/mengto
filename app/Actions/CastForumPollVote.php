<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CastForumPollVoteData;
use App\Enums\ForumPollType;
use App\Models\ForumPoll;
use App\Models\ForumPollOption;
use App\Models\ForumPollVote;
use App\Models\User;
use App\Services\ForumPollEligibility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CastForumPollVote
{
    public function __construct(
        private readonly Gate $gate,
        private readonly ForumPollEligibility $eligibility,
    ) {}

    public function handle(
        User $actor,
        ForumPoll $poll,
        CastForumPollVoteData $data,
    ): ForumPollVote {
        $this->gate->forUser($actor)->authorize('view', $poll);

        if ($poll->isClosed()) {
            throw ValidationException::withMessages([
                'poll' => __('forum_polls.validation.poll_closed'),
            ]);
        }

        $this->gate->forUser($actor)->authorize('vote', $poll);

        return DB::transaction(function () use ($actor, $poll, $data): ForumPollVote {
            $lockedPoll = ForumPoll::query()
                ->with(['options:id,forum_poll_id,stable_key,label,position,selection_count,first_choice_count,created_at'])
                ->lockForUpdate()
                ->findOrFail($poll->id);
            $this->gate->forUser($actor)->authorize('vote', $lockedPoll);

            if ($lockedPoll->isClosed()) {
                throw ValidationException::withMessages([
                    'poll' => __('forum_polls.validation.poll_closed'),
                ]);
            }

            $this->assertEligible($actor, $lockedPoll);

            $idempotentVote = ForumPollVote::query()
                ->where('idempotency_key', $data->idempotencyKey)
                ->first();

            if ($idempotentVote !== null) {
                if ($idempotentVote->forum_poll_id !== $lockedPoll->id
                    || $idempotentVote->user_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'poll' => __('forum_polls.validation.idempotency_conflict'),
                    ]);
                }

                return $idempotentVote;
            }

            $existingVote = ForumPollVote::query()
                ->where('forum_poll_id', $lockedPoll->id)
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if ($existingVote !== null && ! $lockedPoll->is_vote_editable) {
                throw ValidationException::withMessages([
                    'poll' => __('forum_polls.validation.vote_final'),
                ]);
            }

            if ($existingVote !== null
                && $data->expectedVoteVersion !== null
                && $existingVote->lock_version !== $data->expectedVoteVersion
            ) {
                throw ValidationException::withMessages([
                    'poll' => __('forum_polls.validation.vote_changed'),
                ]);
            }

            $choices = $this->validatedChoices($lockedPoll, $data->choices);
            $oldChoices = $existingVote === null ? [] : $existingVote->choices;
            $this->synchronizeCounters(
                $lockedPoll->options,
                $oldChoices,
                $choices,
            );

            if ($existingVote === null) {
                $vote = ForumPollVote::query()->create([
                    'forum_poll_id' => $lockedPoll->id,
                    'user_id' => $actor->id,
                    'choices' => $choices,
                    'idempotency_key' => $data->idempotencyKey,
                ]);
                $lockedPoll->increment('total_vote_count');
            } else {
                $existingVote->forceFill([
                    'choices' => $choices,
                    'idempotency_key' => $data->idempotencyKey,
                    'lock_version' => $existingVote->lock_version + 1,
                ])->save();
                $vote = $existingVote;
            }

            $lockedPoll->increment('lock_version');

            return $vote->refresh();
        }, 3);
    }

    private function assertEligible(User $user, ForumPoll $poll): void
    {
        if (! $this->eligibility->allows($user, $poll)) {
            throw ValidationException::withMessages([
                'poll' => __($this->eligibility->denialTranslationKey($poll)),
            ]);
        }
    }

    /**
     * @param  list<int>  $choices
     * @return list<int>
     */
    private function validatedChoices(ForumPoll $poll, array $choices): array
    {
        $normalized = collect($choices)
            ->map(static fn (int $choice): int => $choice)
            ->unique()
            ->values();
        $validIds = $poll->options->pluck('id');
        $count = $normalized->count();
        $hasUnknownChoice = $normalized->contains(
            static fn (int $choice): bool => ! $validIds->containsStrict($choice),
        );
        $cardinalityIsValid = match ($poll->type) {
            ForumPollType::SingleChoice => $count === 1,
            ForumPollType::MultipleChoice => $count >= 1 && $count <= min(10, $validIds->count()),
            ForumPollType::RankedChoice => $count >= 2 && $count <= $validIds->count(),
        };

        if ($hasUnknownChoice || ! $cardinalityIsValid) {
            throw ValidationException::withMessages([
                'pollChoices' => __('forum_polls.validation.choices'),
            ]);
        }

        return $normalized->all();
    }

    /**
     * @param  Collection<int, ForumPollOption>  $options
     * @param  list<int>  $oldChoices
     * @param  list<int>  $newChoices
     */
    private function synchronizeCounters(
        Collection $options,
        array $oldChoices,
        array $newChoices,
    ): void {
        $oldFirst = $oldChoices[0] ?? null;
        $newFirst = $newChoices[0] ?? null;
        $now = now();
        $rows = $options->map(static function (ForumPollOption $option) use (
            $newChoices,
            $newFirst,
            $now,
            $oldChoices,
            $oldFirst,
        ): array {
            $selectionDelta = (int) in_array($option->id, $newChoices, true)
                - (int) in_array($option->id, $oldChoices, true);
            $firstChoiceDelta = (int) ($option->id === $newFirst)
                - (int) ($option->id === $oldFirst);

            return [
                'id' => $option->id,
                'forum_poll_id' => $option->forum_poll_id,
                'stable_key' => $option->stable_key,
                'label' => $option->label,
                'position' => $option->position,
                'selection_count' => max(0, $option->selection_count + $selectionDelta),
                'first_choice_count' => max(0, $option->first_choice_count + $firstChoiceDelta),
                'created_at' => $option->created_at,
                'updated_at' => $now,
            ];
        })->all();

        ForumPollOption::query()->upsert(
            $rows,
            ['id'],
            ['selection_count', 'first_choice_count', 'updated_at'],
        );
    }
}
