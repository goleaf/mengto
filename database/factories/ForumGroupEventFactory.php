<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupEventType;
use App\Models\ForumGroup;
use App\Models\ForumGroupEvent;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupEvent>
 */
final class ForumGroupEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_group_id' => ForumGroup::factory(),
            'actor_user_id' => null,
            'subject_user_id' => null,
            'event_type' => ForumGroupEventType::Created,
            'reason_code' => 'factory-event',
            'summary_translation_key' => 'forum_groups.events.created',
            'metadata' => [],
            'idempotency_key' => 'factory:group-event:'.Str::uuid()->toString(),
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumGroupEvent $event): void {
            if ($event->forum_group_id !== null) {
                $event->actor_user_id = ForumGroup::query()
                    ->whereKey($event->forum_group_id)
                    ->value('owner_user_id');
            }
        });
    }
}
