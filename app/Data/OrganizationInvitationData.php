<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\OrganizationRole;
use Carbon\CarbonImmutable;

final readonly class OrganizationInvitationData
{
    public function __construct(
        public OrganizationRole $role,
        public CarbonImmutable $expiresAt,
        public string $idempotencyKey,
    ) {}
}
