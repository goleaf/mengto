<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use Illuminate\Validation\ValidationException;

final class SocialIdempotencyGuard
{
    public function assertRequestMatches(
        SocialRelationshipRequest $request,
        SocialActor $source,
        SocialActor $target,
        SocialRelationshipType $type,
    ): void {
        $this->assertMatches(
            $request->source_actor_id === $source->id
                && $request->target_actor_id === $target->id
                && $request->relationship_type === $type,
        );
    }

    public function assertEventMatches(
        SocialRelationshipEvent $event,
        SocialActor $source,
        SocialActor $target,
        SocialRelationshipType $type,
        ?SocialRelationshipRequest $request = null,
        ?SocialRelationship $relationship = null,
    ): void {
        $this->assertMatches(
            $event->source_actor_id === $source->id
                && $event->target_actor_id === $target->id
                && $event->relationship_type === $type
                && ($request === null
                    || $event->social_relationship_request_id === $request->id)
                && ($relationship === null
                    || $event->social_relationship_id === $relationship->id),
        );
    }

    private function assertMatches(bool $matches): void
    {
        if (! $matches) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('social_relationships.validation.idempotency_conflict'),
            ]);
        }
    }
}
