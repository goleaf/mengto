<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CastForumPollVoteData
{
    /**
     * @param  list<int>  $choices
     */
    public function __construct(
        public array $choices,
        public string $idempotencyKey,
        public ?int $expectedVoteVersion = null,
    ) {}
}
