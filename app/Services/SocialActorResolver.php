<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfilePermission;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class SocialActorResolver
{
    public function __construct(private readonly PetProfileAccess $petAccess) {}

    public function forUser(User $user): SocialActor
    {
        return $this->resolve(
            SocialActorType::User,
            'user_id',
            $user->id,
        );
    }

    public function provisionPrivateForUser(User $user): SocialActor
    {
        return $this->resolve(
            SocialActorType::User,
            'user_id',
            $user->id,
            false,
            [
                'friend_request_policy' => SocialFriendRequestPolicy::Nobody,
                'follow_policy' => SocialFollowPolicy::Nobody,
                'friend_list_visibility' => SocialListVisibility::Hidden,
                'follower_list_visibility' => SocialListVisibility::Hidden,
                'is_recommendable' => false,
                'allow_message_requests' => false,
                'updated_by_user_id' => $user->id,
            ],
        );
    }

    public function forPet(PetProfile $profile): SocialActor
    {
        return $this->resolve(
            SocialActorType::Pet,
            'pet_profile_id',
            $profile->id,
        );
    }

    public function forExpert(ExpertProfile $profile): SocialActor
    {
        return $this->resolve(
            SocialActorType::Expert,
            'expert_profile_id',
            $profile->id,
        );
    }

    public function forGroup(ForumGroup $group): SocialActor
    {
        return $this->resolve(
            SocialActorType::Group,
            'forum_group_id',
            $group->id,
        );
    }

    /** @return Collection<int, SocialActor> */
    public function controlledBy(User $user): Collection
    {
        $personalActor = $this->forUser($user);

        $pets = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'name', 'status'])
            ->with(['managers' => fn ($query) => $query
                ->select([
                    'id',
                    'pet_profile_id',
                    'user_id',
                    'role',
                    'status',
                    'permission_overrides',
                    'starts_at',
                    'ends_at',
                    'revoked_at',
                ])
                ->where('user_id', $user->id)])
            ->where(function ($query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereHas('managers', fn ($managerQuery) => PetProfileManager::constrainActiveAt(
                        $managerQuery->where('user_id', $user->id),
                        now(),
                    ));
            })
            ->orderBy('id')
            ->limit(100)
            ->get();

        $petIds = $pets
            ->filter(fn (PetProfile $pet): bool => $this->petAccess
                ->allows($pet, $user, PetProfilePermission::ManageSocial))
            ->modelKeys();

        $expertIds = ExpertProfile::query()
            ->select(['id', 'owner_id'])
            ->where('owner_id', $user->id)
            ->orderBy('id')
            ->limit(50)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $groupIds = ForumGroup::query()
            ->select(['id', 'owner_user_id'])
            ->where('owner_user_id', $user->id)
            ->orderBy('id')
            ->limit(50)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $this->ensureActors(SocialActorType::Pet, 'pet_profile_id', $petIds);
        $this->ensureActors(SocialActorType::Expert, 'expert_profile_id', $expertIds);
        $this->ensureActors(SocialActorType::Group, 'forum_group_id', $groupIds);

        $actors = SocialActor::query()
            ->directoryFields()
            ->where(function (Builder $query) use (
                $expertIds,
                $groupIds,
                $personalActor,
                $petIds,
            ): void {
                $query
                    ->whereKey($personalActor->id)
                    ->when(
                        $petIds !== [],
                        fn (Builder $actorQuery): Builder => $actorQuery
                            ->orWhereIn('pet_profile_id', $petIds),
                    )
                    ->when(
                        $expertIds !== [],
                        fn (Builder $actorQuery): Builder => $actorQuery
                            ->orWhereIn('expert_profile_id', $expertIds),
                    )
                    ->when(
                        $groupIds !== [],
                        fn (Builder $actorQuery): Builder => $actorQuery
                            ->orWhereIn('forum_group_id', $groupIds),
                    );
            })
            ->orderBy('actor_type')
            ->orderBy('id')
            ->get();

        $now = now();
        SocialActorSetting::query()->insertOrIgnore(
            $actors->map(static fn (SocialActor $actor): array => [
                'social_actor_id' => $actor->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        );

        $actors->load([
            'user:id,name,actor_key',
            'petProfile:id,name,profile_key,user_id',
            'expertProfile:id,public_name,owner_id,owner_key,slug',
            'forumGroup:id,name,name_translation_key,owner_user_id,stable_key',
            'settings',
        ]);

        return $actors;
    }

    /** @param list<int> $foreignIds */
    private function ensureActors(
        SocialActorType $type,
        string $foreignKey,
        array $foreignIds,
    ): void {
        if ($foreignIds === []) {
            return;
        }

        $existingIds = SocialActor::query()
            ->select(['id', $foreignKey])
            ->whereIn($foreignKey, $foreignIds)
            ->pluck($foreignKey)
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
        $missingIds = array_values(array_diff($foreignIds, $existingIds));

        if ($missingIds === []) {
            return;
        }

        $now = now();
        SocialActor::query()->insertOrIgnore(array_map(
            static fn (int $foreignId): array => [
                'actor_key' => (string) Str::uuid(),
                'actor_type' => $type->value,
                'status' => SocialActorStatus::Active->value,
                $foreignKey => $foreignId,
                'is_discoverable' => true,
                'lock_version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $missingIds,
        ));
    }

    /**
     * @param  array<string, mixed>  $settingDefaults
     */
    private function resolve(
        SocialActorType $type,
        string $foreignKey,
        int $foreignId,
        bool $isDiscoverable = true,
        array $settingDefaults = [],
    ): SocialActor {
        $actor = SocialActor::query()->firstOrCreate(
            [$foreignKey => $foreignId],
            [
                'actor_key' => (string) Str::uuid(),
                'actor_type' => $type,
                'status' => SocialActorStatus::Active,
                'is_discoverable' => $isDiscoverable,
                'lock_version' => 1,
            ],
        );

        SocialActorSetting::query()->firstOrCreate(
            ['social_actor_id' => $actor->id],
            $settingDefaults,
        );

        return $actor;
    }
}
