<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumCommunityNoteType;
use App\Models\ForumCommunityNote;
use App\Models\ForumTopic;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumCommunityNote>
 */
final class ForumCommunityNoteFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'subject_type' => 'forum-topic',
            'subject_id' => null,
            'proposer_user_id' => User::factory(),
            'subject_author_user_id' => null,
            'note_type' => ForumCommunityNoteType::MissingContext,
            'status' => ForumCommunityNoteStatus::Proposed,
            'body' => fake()->paragraph(),
            'evidence' => [
                [
                    'url' => 'https://example.test/source',
                    'label' => 'Reference source',
                ],
            ],
            'is_safety_notice' => false,
            'current_version' => 1,
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumCommunityNote $note): void {
            if ($note->subject_id !== null) {
                return;
            }

            $topic = ForumTopic::factory()->create();
            $note->subject_id = $topic->id;
            $note->subject_author_user_id = $topic->author_id;
        });
    }

    public function published(): static
    {
        return $this->state([
            'status' => ForumCommunityNoteStatus::Published,
            'published_at' => now(),
            'revalidation_due_at' => now()->addMonths(6),
        ]);
    }

    public function safetyWarning(): static
    {
        return $this->state([
            'note_type' => ForumCommunityNoteType::SafetyWarning,
            'is_safety_notice' => true,
        ]);
    }
}
