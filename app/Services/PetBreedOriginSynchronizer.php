<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedSource;
use App\Models\PetProfile;
use App\Models\PetProfileBreedOrigin;
use Illuminate\Support\Str;
use LogicException;

final class PetBreedOriginSynchronizer
{
    /**
     * @param list<array{
     *     origin_key: string|null,
     *     domestic_classification_id: int|null,
     *     breed_name: string,
     *     confidence: PetBreedConfidence,
     *     source: PetBreedSource,
     *     approximate_share_percent: int|null
     * }> $origins
     */
    public function differs(PetProfile $profile, array $origins): bool
    {
        if (! $profile->relationLoaded('breedOrigins')) {
            throw new LogicException(__('pet_profiles.validation.breed_origins_not_loaded'));
        }

        $current = $profile->breedOrigins
            ->values()
            ->map(static fn (PetProfileBreedOrigin $origin): array => [
                'origin_key' => $origin->origin_key,
                'domestic_classification_id' => $origin->domestic_classification_id,
                'breed_name' => $origin->breed_name,
                'confidence' => $origin->confidence->value,
                'source' => $origin->source->value,
                'approximate_share_percent' => $origin->approximate_share_percent,
            ])
            ->all();
        $expected = array_map(static fn (array $origin): array => [
            'origin_key' => $origin['origin_key'],
            'domestic_classification_id' => $origin['domestic_classification_id'],
            'breed_name' => $origin['breed_name'],
            'confidence' => $origin['confidence']->value,
            'source' => $origin['source']->value,
            'approximate_share_percent' => $origin['approximate_share_percent'],
        ], $origins);

        return $current !== $expected;
    }

    /**
     * @param list<array{
     *     origin_key: string|null,
     *     domestic_classification_id: int|null,
     *     breed_name: string,
     *     confidence: PetBreedConfidence,
     *     source: PetBreedSource,
     *     approximate_share_percent: int|null
     * }> $origins
     */
    public function sync(PetProfile $profile, array $origins): void
    {
        if (! $profile->relationLoaded('breedOrigins')) {
            throw new LogicException(__('pet_profiles.validation.breed_origins_not_loaded'));
        }

        $currentKeys = $profile->breedOrigins
            ->pluck('origin_key')
            ->filter(static fn (mixed $key): bool => is_string($key))
            ->flip();
        $usedKeys = [];
        $now = now();
        $rows = [];

        foreach ($origins as $position => $origin) {
            $requestedKey = $origin['origin_key'];
            $originKey = is_string($requestedKey)
                && $currentKeys->has($requestedKey)
                && ! isset($usedKeys[$requestedKey])
                    ? $requestedKey
                    : Str::lower((string) Str::ulid());
            $usedKeys[$originKey] = true;
            $rows[] = [
                'origin_key' => $originKey,
                'pet_profile_id' => $profile->id,
                'domestic_classification_id' => $origin['domestic_classification_id'],
                'breed_name' => $origin['breed_name'],
                'confidence' => $origin['confidence']->value,
                'source' => $origin['source']->value,
                'approximate_share_percent' => $origin['approximate_share_percent'],
                'position' => $position,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $stale = PetProfileBreedOrigin::query()
            ->where('pet_profile_id', $profile->id);

        if ($usedKeys !== []) {
            $stale->whereNotIn('origin_key', array_keys($usedKeys));
        }

        $stale->delete();

        if ($rows !== []) {
            PetProfileBreedOrigin::query()->upsert(
                $rows,
                ['origin_key'],
                [
                    'domestic_classification_id',
                    'breed_name',
                    'confidence',
                    'source',
                    'approximate_share_percent',
                    'position',
                    'updated_at',
                ],
            );
        }

        $profile->unsetRelation('breedOrigins');
    }
}
