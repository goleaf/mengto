<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumJournal>
 */
final class ForumJournalFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $ownerKey = 'journal-owner-'.Str::lower(Str::random(10));
        $title = fake()->sentence(5);

        return [
            'forum_topic_id' => ForumTopic::factory()->state([
                'author_key' => $ownerKey,
                'type' => ForumTopicType::Journal,
                'title' => $title,
                'status' => ForumTopicStatus::Published,
                'visibility' => ForumVisibility::Public,
                'structured_data' => [
                    'journal_type' => ForumJournalType::General->value,
                    'started_on' => now()->toDateString(),
                ],
                'structured_data_version' => 1,
                'lock_version' => 1,
            ]),
            'owner_user_id' => null,
            'owner_key' => $ownerKey,
            'stable_key' => (string) Str::uuid(),
            'creation_idempotency_key' => (string) Str::uuid(),
            'type' => ForumJournalType::General,
            'status' => ForumJournalStatus::Active,
            'started_on' => now()->toDateString(),
            'timezone' => 'Europe/Vilnius',
            'lock_version' => 1,
            'archived_by_user_id' => null,
            'archived_at' => null,
            'archive_reason_code' => null,
            'metadata' => [],
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'forum_topic_id' => ForumTopic::factory()->state([
                'author_id' => $user->id,
                'author_key' => $user->actor_key,
                'author_name' => $user->name,
                'type' => ForumTopicType::Journal,
                'status' => ForumTopicStatus::Published,
                'visibility' => ForumVisibility::Public,
                'structured_data' => [
                    'journal_type' => ForumJournalType::General->value,
                    'started_on' => now()->toDateString(),
                ],
                'structured_data_version' => 1,
                'lock_version' => 1,
            ]),
            'owner_user_id' => $user->id,
            'owner_key' => $user->actor_key,
        ]);
    }

    public function withType(ForumJournalType $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
        ]);
    }

    public function archived(?User $actor = null): static
    {
        return $this->state(fn (): array => [
            'status' => ForumJournalStatus::Archived,
            'archived_by_user_id' => $actor?->id,
            'archived_at' => now(),
            'archive_reason_code' => 'owner-request',
            'lock_version' => 2,
        ]);
    }
}
