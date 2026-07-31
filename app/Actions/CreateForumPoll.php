<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumPollData;
use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollStatus;
use App\Models\ForumGroup;
use App\Models\ForumPoll;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateForumPoll
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        CreateForumPollData $data,
    ): ForumPoll {
        $this->gate->forUser($actor)->authorize('create', [ForumPoll::class, $group]);
        $options = $this->validatedOptions($group, $data);

        return DB::transaction(function () use ($actor, $group, $data, $options): ForumPoll {
            $existing = ForumPoll::query()
                ->where('creation_idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_group_id !== $group->id
                    || $existing->created_by_user_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'poll' => __('forum_polls.validation.idempotency_conflict'),
                    ]);
                }

                return $existing->load('options');
            }

            $poll = ForumPoll::query()->create([
                'forum_group_id' => $group->id,
                'created_by_user_id' => $actor->id,
                'stable_key' => 'poll-'.Str::lower((string) Str::ulid()),
                'creation_idempotency_key' => $data->idempotencyKey,
                'question' => trim($data->question),
                'description' => filled($data->description)
                    ? trim((string) $data->description)
                    : null,
                'type' => $data->type,
                'voter_visibility' => $data->voterVisibility,
                'result_visibility' => $data->resultVisibility,
                'is_vote_editable' => $data->isVoteEditable,
                'eligibility' => $data->eligibility,
                'location_scope' => $data->eligibility === ForumPollEligibility::LocationMembers
                    ? $group->location_scope
                    : null,
                'status' => ForumPollStatus::Active,
                'closes_at' => $data->closesAt,
            ]);

            $poll->options()->createMany(
                collect($options)
                    ->values()
                    ->map(static fn (string $label, int $position): array => [
                        'stable_key' => sprintf(
                            'option-%02d-%s',
                            $position + 1,
                            Str::slug($label) ?: 'choice',
                        ),
                        'label' => $label,
                        'position' => $position + 1,
                    ])
                    ->all(),
            );

            return $poll->load('options');
        }, 3);
    }

    /**
     * @return list<string>
     */
    private function validatedOptions(
        ForumGroup $group,
        CreateForumPollData $data,
    ): array {
        $errors = [];
        $options = collect($data->options)
            ->map(static fn (string $option): string => trim($option))
            ->filter(static fn (string $option): bool => $option !== '')
            ->values();
        $normalized = $options->map(static fn (string $option): string => Str::lower($option));

        if (mb_strlen(trim($data->question)) < 5
            || mb_strlen(trim($data->question)) > 240
        ) {
            $errors['pollQuestion'] = __('forum_polls.validation.question');
        }

        if ($data->description !== null && mb_strlen(trim($data->description)) > 3000) {
            $errors['pollDescription'] = __('forum_polls.validation.description');
        }

        if ($options->count() < 2 || $options->count() > 20) {
            $errors['pollOptionsText'] = __('forum_polls.validation.option_count');
        }

        if ($normalized->unique()->count() !== $normalized->count()
            || $options->contains(static fn (string $option): bool => mb_strlen($option) > 180)
        ) {
            $errors['pollOptionsText'] = __('forum_polls.validation.options_unique');
        }

        if ($data->closesAt !== null && ! $data->closesAt->isFuture()) {
            $errors['pollClosesAt'] = __('forum_polls.validation.closes_at');
        }

        if ($data->eligibility === ForumPollEligibility::LocationMembers
            && blank($group->location_scope)
        ) {
            $errors['pollEligibility'] = __('forum_polls.validation.location_scope');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $options->all();
    }
}
