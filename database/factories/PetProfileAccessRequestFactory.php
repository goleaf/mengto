<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfileAccessRequestType;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PetProfileAccessRequest> */
final class PetProfileAccessRequestFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $requestKey = 'pet-access-'.Str::lower((string) Str::ulid());

        return [
            'request_key' => $requestKey,
            'pet_profile_id' => PetProfile::factory()->discoverable(),
            'requester_user_id' => User::factory(),
            'requester_actor_key_snapshot' => fake()->unique()->slug(2),
            'request_type' => PetProfileAccessRequestType::CoOwnership,
            'requested_role' => PetManagerRole::CoOwner,
            'status' => PetProfileAccessRequestStatus::Pending,
            'evidence_summary' => fake()->sentence(12),
            'active_key' => hash('sha256', $requestKey.'|active'),
            'submission_key' => hash('sha256', $requestKey.'|submission'),
            'lock_version' => 1,
        ];
    }
}
