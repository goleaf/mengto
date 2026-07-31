<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SocialActor> */
final class SocialActorFactory extends ApplicationFactory
{
    protected $model = SocialActor::class;

    public function definition(): array
    {
        return [
            'actor_key' => (string) Str::uuid(),
            'actor_type' => SocialActorType::User,
            'status' => SocialActorStatus::Active,
            'user_id' => User::factory(),
            'pet_profile_id' => null,
            'expert_profile_id' => null,
            'forum_group_id' => null,
            'is_discoverable' => true,
            'lock_version' => 1,
            'detached_at' => null,
        ];
    }

    public function forUser(User|UserFactory|null $user = null): self
    {
        return $this->state(fn (): array => [
            'actor_type' => SocialActorType::User,
            'user_id' => $user ?? User::factory(),
            'pet_profile_id' => null,
            'expert_profile_id' => null,
            'forum_group_id' => null,
        ]);
    }

    public function forPet(PetProfile|PetProfileFactory|null $profile = null): self
    {
        return $this->state(fn (): array => [
            'actor_type' => SocialActorType::Pet,
            'user_id' => null,
            'pet_profile_id' => $profile ?? PetProfile::factory(),
            'expert_profile_id' => null,
            'forum_group_id' => null,
        ]);
    }

    public function forExpert(ExpertProfile|ExpertProfileFactory|null $profile = null): self
    {
        return $this->state(fn (): array => [
            'actor_type' => SocialActorType::Expert,
            'user_id' => null,
            'pet_profile_id' => null,
            'expert_profile_id' => $profile ?? ExpertProfile::factory(),
            'forum_group_id' => null,
        ]);
    }

    public function forGroup(ForumGroup|ForumGroupFactory|null $group = null): self
    {
        return $this->state(fn (): array => [
            'actor_type' => SocialActorType::Group,
            'user_id' => null,
            'pet_profile_id' => null,
            'expert_profile_id' => null,
            'forum_group_id' => $group ?? ForumGroup::factory(),
        ]);
    }
}
