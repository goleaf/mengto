<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumGroup;
use App\Models\ForumGroupAnnouncement;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupAnnouncement>
 */
final class ForumGroupAnnouncementFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'forum_group_id' => ForumGroup::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => "group-announcement-{$key}",
            'publication_idempotency_key' => "factory:group-announcement:{$key}",
            'title' => fake()->sentence(5),
            'body' => fake()->paragraphs(2, true),
            'published_at' => now(),
            'expires_at' => null,
            'lock_version' => 0,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'published_at' => now()->addWeek(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'published_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'archived_at' => now(),
        ]);
    }
}
