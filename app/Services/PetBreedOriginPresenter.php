<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Models\PetProfile;
use App\Models\PetProfileBreedOrigin;
use LogicException;

final class PetBreedOriginPresenter
{
    /**
     * @return array{
     *     type: string,
     *     summary: string,
     *     notice: string,
     *     origins: list<array{
     *         key: string,
     *         name: string,
     *         confidence: string,
     *         source: string,
     *         share: string|null
     *     }>
     * }|null
     */
    public function for(PetProfile $profile): ?array
    {
        if (! $profile->relationLoaded('breedOrigins')) {
            throw new LogicException(__('pet_profiles.validation.breed_origins_not_loaded'));
        }

        $type = $profile->breed_origin_type;
        $origins = $profile->breedOrigins
            ->map(static fn (PetProfileBreedOrigin $origin): array => [
                'key' => $origin->origin_key,
                'name' => $origin->breed_name,
                'confidence' => $origin->confidence->label(),
                'source' => $origin->source->label(),
                'share' => $origin->approximate_share_percent === null
                    ? null
                    : __('pet_profiles.breed_origin.share_public', [
                        'share' => $origin->approximate_share_percent,
                    ]),
            ])
            ->values()
            ->all();

        if ($type === null && $origins === [] && $profile->breed === null) {
            return null;
        }

        if ($type === null && $origins === [] && $profile->breed !== null) {
            $type = PetBreedOriginType::Single;
            $origins[] = [
                'key' => 'legacy-'.$profile->id,
                'name' => $profile->breed,
                'confidence' => PetBreedConfidence::OwnerReported->label(),
                'source' => PetBreedSource::Unknown->label(),
                'share' => null,
            ];
        }

        $type ??= PetBreedOriginType::Unknown;
        $summary = $profile->breed ?? $type->label();

        return [
            'type' => $type->label(),
            'summary' => $summary,
            'notice' => __('pet_profiles.breed_origin.public_notice'),
            'origins' => $origins,
        ];
    }
}
