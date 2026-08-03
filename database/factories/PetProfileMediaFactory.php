<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PetProfileMediaStatus;
use App\Models\ContentMediaAsset;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PetProfileMedia> */
final class PetProfileMediaFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'media_key' => (string) Str::ulid(),
            'pet_profile_id' => PetProfile::factory(),
            'content_media_asset_id' => ContentMediaAsset::factory(),
            'attached_by_user_id' => User::factory(),
            'role' => 'primary',
            'status' => PetProfileMediaStatus::Active,
            'current_key' => static fn (array $attributes): string => "primary:{$attributes['pet_profile_id']}",
            'upload_key' => hash('sha256', fake()->uuid()),
            'recoverable_until' => null,
            'replaced_at' => null,
            'removed_at' => null,
            'restored_at' => null,
        ];
    }
}
