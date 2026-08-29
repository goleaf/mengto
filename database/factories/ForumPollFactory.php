<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollStatus;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use App\Models\ForumGroup;
use App\Models\ForumPoll;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumPoll>
 */
final class ForumPollFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'forum_group_id' => ForumGroup::factory(),
            'created_by_user_id' => null,
            'stable_key' => "poll-{$key}",
            'creation_idempotency_key' => "factory:poll:{$key}",
            'question' => fake()->sentence(8),
            'description' => fake()->sentence(),
            'type' => ForumPollType::SingleChoice,
            'voter_visibility' => ForumPollVoterVisibility::Anonymous,
            'result_visibility' => ForumPollResultVisibility::AfterVote,
            'is_vote_editable' => true,
            'eligibility' => ForumPollEligibility::GroupMembers,
            'location_scope' => null,
            'status' => ForumPollStatus::Active,
            'closes_at' => now()->addWeek(),
            'total_vote_count' => 0,
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(static function (ForumPoll $poll): void {
                if ($poll->forum_group_id !== null) {
                    $poll->created_by_user_id = ForumGroup::query()
                        ->whereKey($poll->forum_group_id)
                        ->value('owner_user_id');
                }
            })
            ->afterCreating(static function (ForumPoll $poll): void {
                if ($poll->options()->doesntExist()) {
                    $poll->options()->createMany([
                        ['stable_key' => 'option-01', 'label' => 'First choice', 'position' => 1],
                        ['stable_key' => 'option-02', 'label' => 'Second choice', 'position' => 2],
                        ['stable_key' => 'option-03', 'label' => 'Third choice', 'position' => 3],
                    ]);
                }
            });
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (): array => ['type' => ForumPollType::MultipleChoice]);
    }

    public function rankedChoice(): static
    {
        return $this->state(fn (): array => ['type' => ForumPollType::RankedChoice]);
    }

    public function visibleVoters(): static
    {
        return $this->state(fn (): array => [
            'voter_visibility' => ForumPollVoterVisibility::Visible,
        ]);
    }

    public function publicResults(): static
    {
        return $this->state(fn (): array => [
            'result_visibility' => ForumPollResultVisibility::Public,
        ]);
    }

    public function resultsAfterClose(): static
    {
        return $this->state(fn (): array => [
            'result_visibility' => ForumPollResultVisibility::AfterClose,
        ]);
    }

    public function nonEditable(): static
    {
        return $this->state(fn (): array => ['is_vote_editable' => false]);
    }

    public function trustedMembers(): static
    {
        return $this->state(fn (): array => [
            'eligibility' => ForumPollEligibility::TrustedMembers,
        ]);
    }

    public function locationMembers(): static
    {
        return $this->state(fn (array $attributes): array => [
            'eligibility' => ForumPollEligibility::LocationMembers,
            'location_scope' => $attributes['location_scope'] ?? 'lt-vilnius',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => ['closes_at' => now()->subMinute()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => ForumPollStatus::Cancelled]);
    }
}
