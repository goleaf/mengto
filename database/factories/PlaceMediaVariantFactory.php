<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceMediaVariant;
use App\Enums\PlaceMediaVariantStatus;
use App\Models\PlaceMedia;
use App\Models\PlaceMediaVariant as PlaceMediaVariantModel;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceMediaVariantModel> */
final class PlaceMediaVariantFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'place_media_id' => PlaceMedia::factory(),
            'variant' => PlaceMediaVariant::Fallback,
            'status' => PlaceMediaVariantStatus::Ready,
            'disk' => 'local',
            'path' => "place-media/factory/{$key}.webp",
            'mime_type' => 'image/webp',
            'width' => 1200,
            'height' => 900,
            'byte_size' => 1000,
            'checksum_sha256' => hash('sha256', $key),
            'failure_code' => null,
            'generated_at' => now(),
        ];
    }
}
