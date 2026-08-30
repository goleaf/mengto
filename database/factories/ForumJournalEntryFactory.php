<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalEntryKind;
use App\Models\ForumJournal;
use App\Models\ForumJournalEntry;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumJournalEntry>
 */
final class ForumJournalEntryFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_journal_id' => ForumJournal::factory(),
            'author_user_id' => null,
            'author_key' => 'journal-author-'.Str::lower(Str::random(10)),
            'author_name' => fake()->name(),
            'stable_key' => (string) Str::uuid(),
            'idempotency_key' => (string) Str::uuid(),
            'kind' => ForumJournalEntryKind::Entry,
            'occurred_at' => now()->subDays(fake()->numberBetween(0, 30)),
            'timezone' => 'Europe/Vilnius',
            'title' => fake()->sentence(5),
            'body' => fake()->paragraphs(2, true),
            'lock_version' => 1,
        ];
    }

    public function forJournal(ForumJournal $journal): static
    {
        return $this->state(fn (): array => [
            'forum_journal_id' => $journal->id,
        ]);
    }

    public function by(User $user): static
    {
        return $this->state(fn (): array => [
            'author_user_id' => $user->id,
            'author_key' => $user->actor_key,
            'author_name' => $user->name,
        ]);
    }

    public function milestone(): static
    {
        return $this->state(fn (): array => ['kind' => ForumJournalEntryKind::Milestone]);
    }

    public function setback(): static
    {
        return $this->state(fn (): array => ['kind' => ForumJournalEntryKind::Setback]);
    }
}
