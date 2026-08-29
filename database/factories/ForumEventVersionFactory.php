<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumEvent;
use App\Models\ForumEventVersion;
use App\Models\User;

/** @extends ApplicationFactory<ForumEventVersion> */
final class ForumEventVersionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $snapshot = ['title' => fake()->sentence(4), 'status' => 'scheduled'];

        return [
            'forum_event_id' => ForumEvent::factory(),
            'version_number' => fn (array $attributes): int => (int) ForumEventVersion::query()
                ->where('forum_event_id', $attributes['forum_event_id'])
                ->max('version_number') + 1,
            'created_by_user_id' => User::factory(),
            'kind' => 'draft',
            'reason_code' => 'factory-version',
            'snapshot' => $snapshot,
            'snapshot_checksum' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR)),
            'material_fields' => [],
            'published_at' => null,
            'created_at' => now(),
        ];
    }
}
