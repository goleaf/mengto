<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumJournalCollaborator>
 */
final class ForumJournalCollaboratorFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_journal_id' => ForumJournal::factory(),
            'user_id' => User::factory(),
            'granted_by_user_id' => null,
            'role' => ForumJournalCollaboratorRole::Viewer,
            'state' => ForumJournalCollaboratorState::Active,
            'granted_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function editor(): static
    {
        return $this->state(fn (): array => ['role' => ForumJournalCollaboratorRole::Editor]);
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'state' => ForumJournalCollaboratorState::Revoked,
            'revoked_at' => now(),
        ]);
    }
}
