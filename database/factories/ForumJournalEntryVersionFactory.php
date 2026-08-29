<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalEntryKind;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalEntryVersion;

/**
 * @extends ApplicationFactory<ForumJournalEntryVersion>
 */
final class ForumJournalEntryVersionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_journal_entry_id' => ForumJournalEntry::factory(),
            'edited_by_user_id' => null,
            'version' => fn (array $attributes): int => (int) ForumJournalEntryVersion::query()
                ->where('forum_journal_entry_id', $attributes['forum_journal_entry_id'])
                ->max('version') + 1,
            'snapshot' => [
                'title' => fake()->sentence(4),
                'body' => fake()->paragraph(),
                'kind' => ForumJournalEntryKind::Entry->value,
                'occurred_at' => now()->subDay()->toIso8601String(),
                'timezone' => 'Europe/Vilnius',
                'measurements' => [],
            ],
            'reason_code' => 'content-update',
            'created_at' => now(),
        ];
    }
}
