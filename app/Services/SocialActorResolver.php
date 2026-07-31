<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\User;
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
        $actorIds = [$this->forUser($user)->id];

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
                ])
                ->where('user_id', $user->id)])
            ->where(function ($query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereHas('managers', fn ($managerQuery) => $managerQuery
                        ->where('user_id', $user->id)
                        ->where('status', PetManagerStatus::Active->value));
            })
            ->orderBy('id')
            ->limit(100)
            ->get();

        foreach ($pets as $pet) {
            if ($this->petAccess->allows($pet, $user, PetProfilePermission::ManageSocial)) {
                $actorIds[] = $this->forPet($pet)->id;
            }
        }

        ExpertProfile::query()
            ->select(['id', 'owner_id'])
            ->where('owner_id', $user->id)
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(function (ExpertProfile $profile) use (&$actorIds): void {
                $actorIds[] = $this->forExpert($profile)->id;
            });

        ForumGroup::query()
            ->select(['id', 'owner_user_id'])
            ->where('owner_user_id', $user->id)
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(function (ForumGroup $group) use (&$actorIds): void {
                $actorIds[] = $this->forGroup($group)->id;
            });

        return SocialActor::query()
            ->directoryFields()
            ->with([
                'user:id,name,actor_key',
                'petProfile:id,name,profile_key,user_id',
                'expertProfile:id,public_name,owner_id,owner_key,slug',
                'forumGroup:id,name,name_translation_key,owner_user_id,stable_key',
                'settings',
            ])
            ->whereIn('id', array_values(array_unique($actorIds)))
            ->orderBy('actor_type')
            ->orderBy('id')
            ->get();
    }

    private function resolve(
        SocialActorType $type,
        string $foreignKey,
        int $foreignId,
    ): SocialActor {
        $actor = SocialActor::query()->firstOrCreate(
            [$foreignKey => $foreignId],
            [
                'actor_key' => (string) Str::uuid(),
                'actor_type' => $type,
                'status' => SocialActorStatus::Active,
                'is_discoverable' => true,
                'lock_version' => 1,
            ],
        );

        SocialActorSetting::query()->firstOrCreate([
            'social_actor_id' => $actor->id,
        ]);

        return $actor;
    }
}
