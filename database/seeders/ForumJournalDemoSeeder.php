<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CreateForumJournalComment;
use App\Actions\CreateForumJournalEntry;
use App\Actions\GrantForumJournalCollaborator;
use App\Data\CreateForumJournalEntryData;
use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalEntryKind;
use App\Enums\ForumJournalType;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumJournalDemoSeeder extends Seeder
{
    public function run(
        CreateForumJournalEntry $createEntry,
        CreateForumJournalComment $createComment,
        GrantForumJournalCollaborator $grantCollaborator,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Forum journal demo data is restricted to explicitly allowed environments.');
        }

        $owner = User::query()
            ->where('actor_key', 'mia-carter')
            ->firstOrFail();
        $collaborator = User::query()
            ->where('actor_key', 'demo-lithuanian')
            ->firstOrFail();
        $topic = ForumTopic::query()
            ->where('slug', 'helping-a-cat-feel-safe-in-a-carrier')
            ->firstOrFail();
        $journal = ForumJournal::query()
            ->where('forum_topic_id', $topic->id)
            ->firstOrFail();

        if ($journal->type !== ForumJournalType::Behavior) {
            $journal->forceFill([
                'type' => ForumJournalType::Behavior,
                'metadata' => [
                    ...($journal->metadata ?? []),
                    'demo_type_curated' => true,
                    'requires_type_review' => false,
                ],
            ])->save();
        }

        $firstEntry = $createEntry->handle($owner, $journal, new CreateForumJournalEntryData(
            kind: ForumJournalEntryKind::Entry,
            title: 'Carrier became ordinary furniture',
            body: 'The carrier stayed open in the room. Nori explored it without being approached or shut inside.',
            occurredAt: CarbonImmutable::now()->subWeeks(6),
            timezone: 'Europe/Vilnius',
            measurements: [
                ['key' => 'duration_minutes', 'value' => 5],
                ['key' => 'intensity_score', 'value' => 3],
            ],
            idempotencyKey: 'demo-forum-journal-carrier-entry-week-one-v1',
        ));
        $createEntry->handle($owner, $journal, new CreateForumJournalEntryData(
            kind: ForumJournalEntryKind::Milestone,
            title: 'Relaxed entry with the door moving',
            body: 'Nori entered voluntarily and stayed relaxed while the door moved briefly without closing.',
            occurredAt: CarbonImmutable::now()->subWeek(),
            timezone: 'Europe/Vilnius',
            measurements: [
                ['key' => 'duration_minutes', 'value' => 2],
                ['key' => 'intensity_score', 'value' => 1],
            ],
            idempotencyKey: 'demo-forum-journal-carrier-entry-milestone-v1',
        ));
        $createComment->handle(
            actor: $collaborator,
            journal: $journal,
            entry: $firstEntry,
            body: 'The gradual pace and clear observations make this progress easy to understand.',
            idempotencyKey: 'demo-forum-journal-carrier-comment-v1',
        );
        $grantCollaborator->handle(
            actor: $owner,
            journal: $journal,
            email: $collaborator->email,
            role: ForumJournalCollaboratorRole::Viewer,
        );
    }
}
