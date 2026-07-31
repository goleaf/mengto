<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContentInteractionSetting;
use App\Models\ContentPublication;

/** @extends ApplicationFactory<ContentInteractionSetting> */
final class ContentInteractionSettingFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_publication_id' => ContentPublication::factory(),
            'allow_comments' => true,
            'allow_reactions' => true,
            'allow_reposts' => false,
            'allow_external_sharing' => false,
            'allow_media_downloads' => false,
            'allow_mentions' => true,
            'is_searchable' => false,
            'allow_external_indexing' => false,
            'show_reaction_counts' => true,
        ];
    }
}
