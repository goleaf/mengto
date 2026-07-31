<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumGroupMembershipRequestData
{
    /**
     * @param  array<array-key, string>  $answers
     */
    public function __construct(
        public array $answers,
        public string $idempotencyKey,
        public ?string $socialActorKey = null,
    ) {}
}
