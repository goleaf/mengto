<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalMediaStatus;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMedia;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumJournalMedia>
 */
final class ForumJournalMediaFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $stableKey = (string) Str::uuid();

        return [
            'forum_journal_entry_id' => ForumJournalEntry::factory(),
            'uploaded_by_user_id' => null,
            'stable_key' => $stableKey,
            'upload_idempotency_key' => (string) Str::uuid(),
            'disk' => 'local',
            'path' => "forum-journals/testing/{$stableKey}.jpg",
            'original_name' => 'journal-photo.jpg',
            'mime_type' => 'image/jpeg',
            'byte_size' => 1024,
            'checksum' => hash('sha256', $stableKey),
            'alt_text' => 'Journal progress photograph',
            'caption' => 'Representative journal progress image.',
            'status' => ForumJournalMediaStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => ForumJournalMediaStatus::Archived]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumJournalMedia $media): void {
            if ($media->uploaded_by_user_id !== null) {
                return;
            }

            $media->uploaded_by_user_id = ForumJournalEntry::query()
                ->whereKey($media->forum_journal_entry_id)
                ->value('author_user_id');
        });
    }
}
