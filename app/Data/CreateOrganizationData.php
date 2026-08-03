<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\OrganizationType;

final readonly class CreateOrganizationData
{
    public function __construct(
        public string $name,
        public OrganizationType $type,
        public string $defaultLocale,
        public string $idempotencyKey,
        public ?string $summary = null,
        public ?string $publicRegion = null,
    ) {}
}
