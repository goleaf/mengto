<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumTopicMove;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopicMove>
 */
final class ForumTopicMoveFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_topic_id' => ForumTopic::factory()->state([
                'forum_category_id' => ForumCategory::factory(),
            ]),
            'from_forum_category_id' => ForumCategory::factory(),
            'to_forum_category_id' => static fn (array $attributes): mixed => ForumTopic::query()
                ->whereKey($attributes['forum_topic_id'])
                ->value('forum_category_id'),
            'actor_user_id' => User::factory()->administrator(),
            'reason_code' => fake()->randomElement([
                'category-correction',
                'moderator-reclassification',
                'topic-scope-update',
            ]),
            'old_url' => 'https://forum.example.test/topics/'.Str::slug(fake()->unique()->words(4, true)),
            'metadata' => [
                'source' => 'moderation-review',
                'preserve_redirect' => true,
            ],
        ];
    }

    public function initialPlacement(): static
    {
        return $this->state(fn (): array => [
            'from_forum_category_id' => null,
            'old_url' => null,
            'reason_code' => 'initial-placement',
        ]);
    }
}
