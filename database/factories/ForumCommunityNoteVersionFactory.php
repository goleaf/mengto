<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumCommunityNoteStatus;
use App\Models\ForumCommunityNote;
use App\Models\ForumCommunityNoteVersion;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumCommunityNoteVersion>
 */
final class ForumCommunityNoteVersionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_community_note_id' => ForumCommunityNote::factory(),
            'version_number' => fn (array $attributes): int => (int) ForumCommunityNoteVersion::query()
                ->where('forum_community_note_id', $attributes['forum_community_note_id'])
                ->max('version_number') + 1,
            'editor_user_id' => User::factory(),
            'status' => ForumCommunityNoteStatus::Proposed,
            'body' => fake()->paragraph(),
            'evidence' => [],
            'change_reason' => 'factory',
            'source_event' => 'proposed',
            'metadata' => [],
            'created_at' => now(),
        ];
    }
}
