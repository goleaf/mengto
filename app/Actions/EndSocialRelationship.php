<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialRelationshipStatus;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Services\ForumActor;
use App\Services\SocialGraphCache;
use App\Services\SocialIdempotencyGuard;
use App\Services\SocialRelationshipEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EndSocialRelationship
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    public function handle(
        SocialRelationship $relationship,
        string $idempotencyKey,
        string $reasonCode = 'ended-by-member',
    ): SocialRelationship {
        $user = $this->actor->requireUser();

        return DB::transaction(function () use (
            $relationship,
            $idempotencyKey,
            $reasonCode,
            $user,
        ): SocialRelationship {
            $locked = SocialRelationship::query()
                ->with(['sourceActor', 'targetActor'])
                ->lockForUpdate()
                ->findOrFail($relationship->id);
            $this->gate->authorize('end', $locked);

            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent instanceof SocialRelationshipEvent) {
                $this->idempotency->assertEventMatches(
                    $existingEvent,
                    $locked->sourceActor,
                    $locked->targetActor,
                    $locked->relationship_type,
                    relationship: $locked,
                );

                return $locked;
            }

            if ($locked->status === SocialRelationshipStatus::Ended) {
                return $locked;
            }

            if ($locked->status !== SocialRelationshipStatus::Active) {
                throw ValidationException::withMessages([
                    'relationship' => __('social_relationships.validation.relationship_unavailable'),
                ]);
            }

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => SocialRelationshipStatus::Ended,
                'active_key' => null,
                'reason_code' => $reasonCode,
                'ended_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $representedActor = $this->gate->forUser($user)->allows('represent', $locked->sourceActor)
                ? $locked->sourceActor
                : $locked->targetActor;
            $this->events->record(
                source: $locked->sourceActor,
                target: $locked->targetActor,
                representedActor: $representedActor,
                actor: $user,
                eventType: 'relationship-ended',
                type: $locked->relationship_type,
                idempotencyKey: $idempotencyKey,
                fromStatus: $fromStatus,
                toStatus: SocialRelationshipStatus::Ended->value,
                reasonCode: $reasonCode,
                relationship: $locked,
            );
            $this->cache->invalidate($locked->sourceActor, $locked->targetActor);

            return $locked->refresh();
        }, 3);
    }
}
