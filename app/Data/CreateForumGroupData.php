<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ForumGroupVisibility;

final readonly class CreateForumGroupData
{
    /**
     * @param  list<string>  $rules
     * @param  list<string>  $membershipQuestions
     * @param  list<int>  $taxonIds
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $rules,
        public ForumGroupVisibility $visibility,
        public string $defaultLocale,
        public ?string $locationScope,
        public array $membershipQuestions,
        public array $taxonIds,
        public string $idempotencyKey,
    ) {}
}
