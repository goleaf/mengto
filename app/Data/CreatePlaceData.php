<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;

final readonly class CreatePlaceData
{
    /** @param list<string> $speciesRules */
    public function __construct(
        public string $name,
        public PlaceType $type,
        public PlaceVisibility $visibility,
        public string $publicRegion,
        public ?string $publicAddress,
        public ?string $exactAddress,
        public ?string $publicLatitude,
        public ?string $publicLongitude,
        public ?string $exactLatitude,
        public ?string $exactLongitude,
        public string $locale,
        public string $idempotencyKey,
        public ?string $summary = null,
        public ?string $privateInstructions = null,
        public ?string $transportInformation = null,
        public ?string $parkingInformation = null,
        public ?string $petRules = null,
        public array $speciesRules = [],
        public bool $isIndoor = false,
        public PlaceVerificationStatus $verificationStatus = PlaceVerificationStatus::NotAssessed,
        public PlaceAccessibilityStatus $accessibilityStatus = PlaceAccessibilityStatus::NotAssessed,
        public ?int $organizationId = null,
        public ?string $catalogCategory = null,
        public ?string $publicPhone = null,
        public ?string $publicWebsite = null,
        public ?string $publicEmail = null,
    ) {}
}
