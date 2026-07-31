<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumTopicType;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopicType>
 */
final class ForumTopicTypeFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name_translation_key' => 'forum_topic_types.'.$key.'.name',
            'description_translation_key' => 'forum_topic_types.'.$key.'.description',
            'schema_version' => 1,
            'field_schema' => [],
            'configuration' => [],
            'moderation_level' => 'standard',
            'allows_accepted_answers' => false,
            'allows_confirmation' => false,
            'expires' => false,
            'is_system_managed' => false,
            'is_active' => true,
        ];
    }
}
