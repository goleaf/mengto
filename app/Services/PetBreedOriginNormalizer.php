<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Models\DomesticClassification;
use App\Models\PetProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PetBreedOriginNormalizer
{
    public const MAX_ORIGINS = 4;

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     taxon_id: int|null,
     *     type: PetBreedOriginType,
     *     legacy_snapshot: string|null,
     *     domestic_classification_id: int|null,
     *     origins: list<array{
     *         origin_key: string|null,
     *         domestic_classification_id: int|null,
     *         breed_name: string,
     *         confidence: PetBreedConfidence,
     *         source: PetBreedSource,
     *         approximate_share_percent: int|null
     *     }>
     * }
     */
    public function normalize(array $data, PetProfile $profile): array
    {
        $type = PetBreedOriginType::tryFrom((string) ($data['breed_origin_type'] ?? ''));

        if (! $type instanceof PetBreedOriginType) {
            $this->invalid('breed_origin_type', 'breed_origin_type');
        }

        $rawOrigins = $data['breed_origins'] ?? [];

        if (! is_array($rawOrigins) || count($rawOrigins) > self::MAX_ORIGINS) {
            $this->invalid('breed_origins', 'breed_origins');
        }

        $rawOrigins = array_values($rawOrigins);
        $classificationIds = $this->classificationIds($rawOrigins);
        $classifications = $this->classifications($classificationIds);
        $taxonId = $this->nullableInteger($data['taxon_id'] ?? $profile->taxon_id, 'taxon_id');
        $origins = [];
        $seen = [];
        $shareTotal = 0;

        foreach ($rawOrigins as $rawOrigin) {
            if (! is_array($rawOrigin)) {
                $this->invalid('breed_origins', 'breed_origins');
            }

            $classificationId = $this->nullableInteger(
                $rawOrigin['domestic_classification_id'] ?? null,
                'breed_origins',
            );
            $breedName = trim(is_string($rawOrigin['name'] ?? null)
                ? $rawOrigin['name']
                : '');

            if ($classificationId === null && $breedName === '') {
                continue;
            }

            $classification = $classificationId === null
                ? null
                : $classifications->get($classificationId);

            if ($classificationId !== null && ! $classification instanceof DomesticClassification) {
                $this->invalid('breed_origins', 'breed_classification');
            }

            if ($classification instanceof DomesticClassification) {
                if ($taxonId === null) {
                    $taxonId = $classification->taxon_id;
                }

                if ($taxonId !== $classification->taxon_id) {
                    $this->invalid('breed_origins', 'breed_taxon');
                }

                if ($breedName === '') {
                    $breedName = $classification->canonical_name;
                }
            }

            if ($breedName === '' || Str::length($breedName) > 220) {
                $this->invalid('breed_origins', 'breed_name');
            }

            $confidence = PetBreedConfidence::tryFrom((string) ($rawOrigin['confidence'] ?? ''));
            $source = PetBreedSource::tryFrom((string) ($rawOrigin['source'] ?? ''));

            if (! $confidence instanceof PetBreedConfidence) {
                $this->invalid('breed_origins', 'breed_confidence');
            }

            if (! $source instanceof PetBreedSource) {
                $this->invalid('breed_origins', 'breed_source');
            }

            $share = $this->nullableInteger(
                $rawOrigin['approximate_share_percent'] ?? null,
                'breed_origins',
            );

            if ($share !== null && ($share < 1 || $share > 100)) {
                $this->invalid('breed_origins', 'breed_share');
            }

            if ($type !== PetBreedOriginType::Mixed && $share !== null) {
                $this->invalid('breed_origins', 'breed_share_mixed_only');
            }

            $duplicateKey = $classificationId === null
                ? 'name:'.Str::lower($breedName)
                : 'classification:'.$classificationId;

            if (isset($seen[$duplicateKey])) {
                $this->invalid('breed_origins', 'breed_duplicate');
            }

            $seen[$duplicateKey] = true;
            $shareTotal += $share ?? 0;
            $origins[] = [
                'origin_key' => $this->originKey($rawOrigin['origin_key'] ?? null),
                'domestic_classification_id' => $classificationId,
                'breed_name' => $breedName,
                'confidence' => $confidence,
                'source' => $source,
                'approximate_share_percent' => $share,
            ];
        }

        $this->guardCount($type, count($origins));

        if ($shareTotal > 100) {
            $this->invalid('breed_origins', 'breed_share_total');
        }

        $legacySnapshot = $this->legacySnapshot($type, $origins);
        $domesticClassificationId = $type === PetBreedOriginType::Single
            ? $origins[0]['domestic_classification_id']
            : null;

        return [
            'taxon_id' => $taxonId,
            'type' => $type,
            'legacy_snapshot' => $legacySnapshot,
            'domestic_classification_id' => $domesticClassificationId,
            'origins' => $origins,
        ];
    }

    /**
     * @param  list<mixed>  $rawOrigins
     * @return list<int>
     */
    private function classificationIds(array $rawOrigins): array
    {
        $ids = [];

        foreach ($rawOrigins as $rawOrigin) {
            if (! is_array($rawOrigin)) {
                $this->invalid('breed_origins', 'breed_origins');
            }

            $id = $this->nullableInteger(
                $rawOrigin['domestic_classification_id'] ?? null,
                'breed_origins',
            );

            if ($id !== null) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, DomesticClassification>
     */
    private function classifications(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DomesticClassification::query()
            ->select([
                'id',
                'taxon_id',
                'classification_type',
                'canonical_name',
                'is_active',
            ])
            ->whereKey($ids)
            ->where('classification_type', 'breed')
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
    }

    /** @param list<array{breed_name: string, ...}> $origins */
    private function legacySnapshot(PetBreedOriginType $type, array $origins): ?string
    {
        if (! $type->acceptsEntries() || $origins === []) {
            return null;
        }

        $separator = $type === PetBreedOriginType::Mixed ? ' + ' : ' / ';
        $names = array_map(
            static fn (array $origin): string => $origin['breed_name'],
            $origins,
        );

        return Str::limit(implode($separator, $names), 120, '');
    }

    private function guardCount(PetBreedOriginType $type, int $count): void
    {
        $valid = match ($type) {
            PetBreedOriginType::Single => $count === 1,
            PetBreedOriginType::Mixed => $count <= self::MAX_ORIGINS,
            PetBreedOriginType::PossibleMultiple => $count >= 2 && $count <= self::MAX_ORIGINS,
            PetBreedOriginType::NoBreed,
            PetBreedOriginType::Unknown => $count === 0,
        };

        if (! $valid) {
            $this->invalid('breed_origins', 'breed_origin_count');
        }
    }

    private function nullableInteger(mixed $value, string $field): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        $this->invalid($field, 'breed_integer');
    }

    private function originKey(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = Str::lower(trim($value));

        return preg_match('/^[0-9a-hjkmnp-tv-z]{26}$/', $normalized) === 1
            ? $normalized
            : null;
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => __("pet_profiles.validation.{$message}"),
        ]);
    }
}
