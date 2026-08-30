<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlacePublicLocationPrecision;

final class PlacePublicLocation
{
    /** @return array{latitude: string|null, longitude: string|null, precision: PlacePublicLocationPrecision} */
    public function normalize(
        ?string $latitude,
        ?string $longitude,
        ?PlacePublicLocationPrecision $precision,
    ): array {
        $precision ??= $latitude !== null && $longitude !== null
            ? PlacePublicLocationPrecision::ApproximatePoint
            : PlacePublicLocationPrecision::Region;

        if ($precision === PlacePublicLocationPrecision::Region) {
            return ['latitude' => null, 'longitude' => null, 'precision' => $precision];
        }

        return [
            'latitude' => $latitude === null ? null : number_format(round((float) $latitude, 3), 3, '.', ''),
            'longitude' => $longitude === null ? null : number_format(round((float) $longitude, 3), 3, '.', ''),
            'precision' => $precision,
        ];
    }
}
