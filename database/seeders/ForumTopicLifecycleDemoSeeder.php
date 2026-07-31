<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SetForumTopicLegalHold;
use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumTopicLifecycleDemoSeeder extends Seeder
{
    public function run(
        ForumTopicLifecycle $lifecycle,
        SetForumTopicLegalHold $legalHold,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException(
                'Forum topic lifecycle demo data may only be created in an explicitly allowed environment.',
            );
        }

        $author = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $administrator = User::query()
            ->where('actor_key', 'demo-administrator')
            ->firstOrFail();
        $categoryId = ForumCategory::query()
            ->where('stable_key', 'forum.health')
            ->value('id');
        $topics = [
            [
                'slug' => 'demo-outdated-seasonal-parasite-advice',
                'status' => ForumTopicStatus::Outdated,
                'title' => 'Demo: seasonal parasite advice awaiting an update',
                'body' => 'This deterministic demo topic shows an outdated discussion, a stale-content warning, and the author update workflow.',
                'last_activity_at' => now()->subYear(),
                'last_author_update_at' => now()->subYear(),
                'outdated_at' => now()->subMonth(),
            ],
            [
                'slug' => 'demo-archived-care-discussion',
                'status' => ForumTopicStatus::Archived,
                'title' => 'Demo: archived care discussion',
                'body' => 'This deterministic demo topic shows preserved archived content and the restoration workflow.',
                'archived_at' => now()->subWeek(),
            ],
            [
                'slug' => 'demo-restored-owner-discussion',
                'status' => ForumTopicStatus::Restored,
                'title' => 'Demo: restored owner discussion',
                'body' => 'This deterministic demo topic shows a topic restored without losing its history.',
                'restored_at' => now()->subDay(),
            ],
            [
                'slug' => 'demo-topic-under-legal-hold',
                'status' => ForumTopicStatus::Open,
                'title' => 'Demo: topic retained under legal hold',
                'body' => 'This deterministic demo topic lets administrators verify retention safeguards without exposing the private legal reason.',
            ],
        ];

        foreach ($topics as $attributes) {
            $topic = ForumTopic::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                ForumTopic::factory()->raw([
                    ...$attributes,
                    'author_id' => $author->id,
                    'author_key' => $author->actor_key,
                    'author_name' => $author->name,
                    'author_initials' => 'MC',
                    'author_role' => 'Demo animal owner',
                    'category' => 'health',
                    'forum_category_id' => $categoryId,
                    'published_at' => now()->subYear(),
                    'state_entered_at' => now()->subMonth(),
                    'retention_until' => now()->addYears(7),
                    'lock_version' => 1,
                ]),
            );
            $lifecycle->record(
                topic: $topic,
                type: ForumTopicLifecycleEventType::StateChanged,
                actor: $administrator,
                reasonCode: 'demo-lifecycle-state',
                toStatus: $topic->status->canonical(),
                idempotencyKey: "demo-topic-lifecycle:{$topic->id}",
            );
        }

        $heldTopic = ForumTopic::query()
            ->where('slug', 'demo-topic-under-legal-hold')
            ->firstOrFail();
        $legalHold->apply(
            actor: $administrator,
            topic: $heldTopic,
            reasonCode: 'demo-retention-review',
            privateReason: 'Demonstration-only private retention reason for local lifecycle testing.',
            reviewAt: now()->addMonth()->toDateTimeString(),
        );
    }
}
