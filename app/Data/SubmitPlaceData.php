<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceType;
use Carbon\CarbonImmutable;

final readonly class SubmitPlaceData
{
    /**
     * @param  array<string, mixed>  $facts
     * @param  array<string, scalar|null>  $auditContext
     */
    public function __construct(
        public string $name,
        public PlaceType $type,
        public string $catalogCategory,
        public PlaceSubmissionSource $source,
        public ?string $sourceReference,
        public string $relationshipToPlace,
        public PlaceLocationPrecision $locationPrecision,
        public string $locale,
        public string $publicRegion,
        public ?string $publicAddress,
        public ?string $publicLatitude,
        public ?string $publicLongitude,
        public ?string $exactAddress,
        public ?string $exactLatitude,
        public ?string $exactLongitude,
        public ?string $publicPhone,
        public ?string $publicEmail,
        public ?string $publicWebsite,
        public ?string $summary,
        public array $facts,
        public ?int $canonicalOrganizationId,
        public ?CarbonImmutable $observedAt,
        public string $consentVersion,
        public bool $consentGranted,
        public string $idempotencyKey,
        public array $auditContext = [],
    ) {}
}
