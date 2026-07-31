<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentPublicationEventType;
use App\Enums\ContentPublicationStatus;
use App\Models\ContentPublication;
use App\Models\ContentPublicationEvent;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ContentPublicationEvent> */
final class ContentPublicationEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'content_publication_id' => ContentPublication::factory(),
            'actor_user_id' => User::factory(),
            'represented_actor_id' => SocialActor::factory()->forUser(),
            'actor_key_snapshot' => (string) Str::uuid(),
            'representation_role' => 'self',
            'event_type' => ContentPublicationEventType::Created,
            'from_status' => null,
            'to_status' => ContentPublicationStatus::Draft,
            'idempotency_key' => (string) Str::ulid(),
            'metadata' => null,
            'occurred_at' => now(),
        ];
    }
}
