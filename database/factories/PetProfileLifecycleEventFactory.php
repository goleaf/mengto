<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PetProfileLifecycleEvent> */
final class PetProfileLifecycleEventFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'pet_profile_id' => PetProfile::factory(),
            'actor_user_id' => User::factory(),
            'actor_key_snapshot' => fake()->unique()->slug(2),
            'actor_role_snapshot' => 'primary-owner',
            'event_type' => 'profile-created',
            'from_status' => null,
            'to_status' => 'draft',
            'reason_code' => 'profile-created',
            'reason_translation_key' => 'pet_profiles.reasons.profile-created',
            'lock_version' => 1,
            'idempotency_key' => (string) Str::uuid(),
            'public_metadata' => [],
            'private_metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
