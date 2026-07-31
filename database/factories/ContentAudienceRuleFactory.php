<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentAudienceType;
use App\Models\ContentAudienceRule;
use App\Models\ContentPublication;

/** @extends ApplicationFactory<ContentAudienceRule> */
final class ContentAudienceRuleFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_publication_id' => ContentPublication::factory(),
            'audience_type' => ContentAudienceType::AuthorOnly,
            'context_actor_id' => null,
            'context_type' => null,
            'context_key' => null,
            'expires_at' => null,
            'lock_version' => 1,
        ];
    }
}
