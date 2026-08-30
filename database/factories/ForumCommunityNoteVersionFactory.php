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
            'evidence' => [['type' => 'source-link', 'url' => 'https://example.test/evidence/community-note']],
            'change_reason' => 'factory',
            'source_event' => 'proposed',
            'metadata' => ['source' => 'factory', 'version' => 1],
            'created_at' => now(),
        ];
    }
}
