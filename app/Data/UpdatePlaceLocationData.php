<?php

declare(strict_types=1);

namespace App\Data;

final readonly class UpdatePlaceLocationData
{
    public function __construct(
        public string $publicRegion,
        public ?string $publicAddress,
        public ?string $publicLatitude,
        public ?string $publicLongitude,
        public ?string $exactAddress,
        public ?string $exactLatitude,
        public ?string $exactLongitude,
        public ?string $privateInstructions,
        public string $reasonCode,
    ) {}
}
