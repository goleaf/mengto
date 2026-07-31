<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Models\User;

final class SocialRelationshipEventRecorder
{
    public function __construct(private readonly SocialIdempotencyGuard $idempotency) {}

    /**
     * @param  array<string, mixed>|null  $publicMetadata
     * @param  array<string, mixed>|null  $privateMetadata
     */
    public function record(
        SocialActor $source,
        SocialActor $target,
        SocialActor $representedActor,
        User $actor,
        string $eventType,
        SocialRelationshipType $type,
        string $idempotencyKey,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $reasonCode = null,
        ?SocialRelationship $relationship = null,
        ?SocialRelationshipRequest $request = null,
        ?array $publicMetadata = null,
        ?array $privateMetadata = null,
    ): SocialRelationshipEvent {
        $event = SocialRelationshipEvent::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'social_relationship_id' => $relationship?->id,
                'social_relationship_request_id' => $request?->id,
                'source_actor_id' => $source->id,
                'target_actor_id' => $target->id,
                'represented_actor_id' => $representedActor->id,
                'actor_user_id' => $actor->id,
                'actor_key_snapshot' => $actor->actor_key,
                'event_type' => $eventType,
                'relationship_type' => $type,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason_code' => $reasonCode,
                'public_metadata' => $publicMetadata,
                'private_metadata' => $privateMetadata,
                'occurred_at' => now(),
            ],
        );

        $this->idempotency->assertEventMatches(
            $event,
            $source,
            $target,
            $type,
            $request,
            $relationship,
        );

        return $event;
    }
}
