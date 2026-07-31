<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentAudienceActorEffect;
use App\Models\ContentAudienceActor;
use App\Models\ContentAudienceRule;
use App\Models\SocialActor;

/** @extends ApplicationFactory<ContentAudienceActor> */
final class ContentAudienceActorFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_audience_rule_id' => ContentAudienceRule::factory(),
            'social_actor_id' => SocialActor::factory()->forUser(),
            'effect' => ContentAudienceActorEffect::Include,
        ];
    }
}
