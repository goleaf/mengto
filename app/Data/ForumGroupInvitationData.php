<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumGroupRole;
use Carbon\CarbonImmutable;

final readonly class ForumGroupInvitationData
{
    public function __construct(
        public ForumGroupRole $role,
        public ?string $message,
        public CarbonImmutable $expiresAt,
        public string $idempotencyKey,
    ) {}
}
