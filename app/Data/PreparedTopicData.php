<?php

declare(strict_types=1);

namespace App\Data;

final readonly class PreparedTopicData
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $newMediaPaths
     */
    public function __construct(
        public array $attributes,
        public array $newMediaPaths,
    ) {}
}
