<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentMediaStatus;
use App\Enums\ContentMediaType;
use App\Models\ContentMediaAsset;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ContentMediaAsset> */
final class ContentMediaAssetFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = (string) Str::ulid();

        return [
            'media_key' => $key,
            'owner_user_id' => User::factory(),
            'created_by_user_id' => fn (array $attributes): mixed => $attributes['owner_user_id'],
            'media_type' => ContentMediaType::Image,
            'status' => ContentMediaStatus::Ready,
            'disk' => 'private',
            'path' => "content/{$key}.jpg",
            'original_name' => 'pet-photo.jpg',
            'mime_type' => 'image/jpeg',
            'byte_size' => 150_000,
            'checksum_sha256' => hash('sha256', $key),
            'alt_text' => fake()->sentence(),
            'licence' => null,
            'safe_metadata' => ['gps_removed' => true],
            'retained_until' => null,
        ];
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn (): array => [
            'owner_user_id' => $user->id,
            'created_by_user_id' => $user->id,
        ]);
    }
}
