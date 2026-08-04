<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetIdentifyingMarkType;
use App\Enums\PetIdentifyingMarkVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileIdentifyingMark;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PetProfileIdentifyingMark> */
final class PetProfileIdentifyingMarkFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'mark_key' => Str::lower((string) Str::ulid()),
            'pet_profile_id' => PetProfile::factory(),
            'type' => PetIdentifyingMarkType::Scar,
            'description' => fake()->sentence(),
            'visibility' => PetIdentifyingMarkVisibility::Verification,
            'position' => 0,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
            'retired_at' => null,
        ];
    }
}
