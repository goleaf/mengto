<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentDomainType;
use App\Models\ContentDomainLink;
use App\Models\ContentPublication;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ContentDomainLink> */
final class ContentDomainLinkFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_publication_id' => ContentPublication::factory(),
            'domain_type' => ContentDomainType::Pet,
            'domain_key' => (string) Str::ulid(),
            'relationship' => 'context',
            'is_primary' => true,
        ];
    }
}
