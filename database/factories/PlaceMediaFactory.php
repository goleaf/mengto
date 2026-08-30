<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceMediaStatus;
use App\Models\ContentMediaAsset;
use App\Models\Place;
use App\Models\PlaceMedia;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceMedia> */
final class PlaceMediaFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'media_key' => (string) Str::ulid(),
            'place_id' => Place::factory(),
            'content_media_asset_id' => ContentMediaAsset::factory(),
            'attached_by_user_id' => fn (array $attributes): mixed => Place::query()->findOrFail($attributes['place_id'])->owner_user_id,
            'moderated_by_user_id' => null,
            'status' => PlaceMediaStatus::PendingReview,
            'position' => 1,
            'is_featured' => false,
            'featured_key' => null,
            'caption' => null,
            'attribution' => fake()->name(),
            'licence' => 'all-rights-reserved',
            'upload_key' => hash('sha256', (string) Str::uuid()),
            'moderation_reason_code' => null,
            'moderated_at' => null,
            'archived_at' => null,
            'removed_at' => null,
            'recoverable_until' => null,
            'retained_until' => null,
            'legal_hold_at' => null,
            'lock_version' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceMediaStatus::Active,
            'moderated_at' => now(),
        ]);
    }
}
