<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\SocialActorResolver;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroup>
 */
final class ForumGroupFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'owner_user_id' => User::factory(),
            'stable_key' => "group-{$key}",
            'creation_idempotency_key' => "factory:group:{$key}",
            'is_system_managed' => false,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'rules' => [fake()->sentence(), fake()->sentence()],
            'rules_version' => 1,
            'visibility' => ForumGroupVisibility::Public,
            'status' => ForumGroupStatus::Active,
            'default_locale' => 'en',
            'location_scope' => 'lt-vilnius',
            'membership_questions' => ['What would you like to contribute?'],
            'allowed_actor_types' => ['user', 'pet', 'expert'],
            'active_member_count' => 1,
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ForumGroup $group): void {
            $owner = User::query()->findOrFail($group->owner_user_id);
            $ownerActor = app(SocialActorResolver::class)->forUser($owner);
            ForumGroupMembership::query()->firstOrCreate(
                [
                    'forum_group_id' => $group->id,
                    'social_actor_id' => $ownerActor->id,
                ],
                [
                    'user_id' => $group->owner_user_id,
                    'role' => ForumGroupRole::Owner,
                    'state' => ForumGroupMembershipState::Active,
                    'notification_level' => 'all',
                    'accepted_rules_version' => $group->rules_version,
                    'accepted_rules_at' => now(),
                    'joined_at' => now(),
                    'lock_version' => 0,
                ],
            );
        });
    }

    public function requestToJoin(): static
    {
        return $this->state(fn (): array => [
            'visibility' => ForumGroupVisibility::RequestToJoin,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => [
            'visibility' => ForumGroupVisibility::Private,
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn (): array => [
            'visibility' => ForumGroupVisibility::Unlisted,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumGroupStatus::Closed,
            'closed_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->closed()->state(fn (): array => [
            'status' => ForumGroupStatus::Archived,
            'archived_at' => now(),
        ]);
    }
}
