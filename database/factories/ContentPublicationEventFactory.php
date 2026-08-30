<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentPublicationEventType;
use App\Enums\ContentPublicationStatus;
use App\Models\ContentPublication;
use App\Models\ContentPublicationEvent;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ContentPublicationEvent> */
final class ContentPublicationEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_publication_id' => ContentPublication::factory(),
            'actor_user_id' => null,
            'represented_actor_id' => null,
            'actor_key_snapshot' => null,
            'representation_role' => 'self',
            'event_type' => ContentPublicationEventType::Created,
            'from_status' => null,
            'to_status' => ContentPublicationStatus::Draft,
            'idempotency_key' => (string) Str::ulid(),
            'metadata' => null,
            'occurred_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ContentPublicationEvent $event): void {
            $publication = ContentPublication::query()
                ->with(['realAuthor', 'publishingActor'])
                ->findOrFail($event->content_publication_id);

            $event->actor_user_id = $publication->real_author_user_id;
            $event->represented_actor_id = $publication->publishing_actor_id;
            $event->actor_key_snapshot = $publication->realAuthor->actor_key;
        });
    }
}
