<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\SocialGraphCache;
use App\Services\SocialIdempotencyGuard;
use App\Services\SocialRelationshipEventRecorder;
use App\Services\SocialRelationshipKey;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateSocialControl
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    public function handle(
        SocialActor $source,
        SocialActor $target,
        SocialRelationshipType $type,
        string $idempotencyKey,
        ?string $reasonCode = null,
    ): SocialRelationship {
        if (! in_array($type, [
            SocialRelationshipType::CloseCircle,
            SocialRelationshipType::Restrict,
            SocialRelationshipType::Mute,
            SocialRelationshipType::Block,
        ], true)) {
            throw ValidationException::withMessages([
                'relationship_type' => __('social_relationships.validation.control_type_unavailable'),
            ]);
        }

        $user = $this->actor->requireUser();
        $this->gate->authorize('represent', $source);

        if ($source->is($target)) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.cannot_connect_self'),
            ]);
        }

        return DB::transaction(function () use (
            $source,
            $target,
            $type,
            $idempotencyKey,
            $reasonCode,
            $user,
        ): SocialRelationship {
            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent?->social_relationship_id !== null) {
                $this->idempotency->assertEventMatches(
                    $existingEvent,
                    $source,
                    $target,
                    $type,
                );

                return SocialRelationship::query()->findOrFail($existingEvent->social_relationship_id);
            }

            $lockedActors = SocialActor::query()
                ->whereIn('id', [$source->id, $target->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lockedSource = $lockedActors->get($source->id);
            $lockedTarget = $lockedActors->get($target->id);

            if (! $lockedSource instanceof SocialActor || ! $lockedTarget instanceof SocialActor) {
                throw ValidationException::withMessages([
                    'target' => __('social_relationships.validation.actor_unavailable'),
                ]);
            }

            $this->gate->authorize('represent', $lockedSource);
            $activeKey = SocialRelationshipKey::forRelationship(
                $lockedSource->id,
                $lockedTarget->id,
                $type,
            );
            $relationship = SocialRelationship::query()
                ->where('active_key', $activeKey)
                ->lockForUpdate()
                ->first();

            if (! $relationship instanceof SocialRelationship) {
                $relationship = SocialRelationship::query()->create([
                    'relationship_key' => (string) Str::uuid(),
                    'source_actor_id' => $lockedSource->id,
                    'target_actor_id' => $lockedTarget->id,
                    'relationship_type' => $type,
                    'direction' => $type->direction(),
                    'status' => SocialRelationshipStatus::Active,
                    'active_key' => $activeKey,
                    'visibility' => 'private',
                    'created_by_user_id' => $user->id,
                    'reason_code' => $reasonCode,
                    'lock_version' => 1,
                    'started_at' => now(),
                ]);
            }

            if ($type === SocialRelationshipType::Block) {
                $this->closeBlockedContact(
                    $lockedSource,
                    $lockedTarget,
                    $relationship,
                    $user,
                );
            }

            $this->events->record(
                source: $lockedSource,
                target: $lockedTarget,
                representedActor: $lockedSource,
                actor: $user,
                eventType: "{$type->value}-created",
                type: $type,
                idempotencyKey: $idempotencyKey,
                toStatus: SocialRelationshipStatus::Active->value,
                reasonCode: $reasonCode,
                relationship: $relationship,
            );
            $this->cache->invalidate($lockedSource, $lockedTarget);

            return $relationship->refresh();
        }, 3);
    }

    private function closeBlockedContact(
        SocialActor $source,
        SocialActor $target,
        SocialRelationship $block,
        User $actor,
    ): void {
        $requests = SocialRelationshipRequest::query()
            ->select([
                'id',
                'source_actor_id',
                'target_actor_id',
                'relationship_type',
                'status',
                'active_key',
                'decided_by_user_id',
                'decided_at',
                'lock_version',
            ])
            ->open()
            ->where(function ($query) use ($source, $target): void {
                $query
                    ->where(function ($direct) use ($source, $target): void {
                        $direct
                            ->where('source_actor_id', $source->id)
                            ->where('target_actor_id', $target->id);
                    })
                    ->orWhere(function ($reverse) use ($source, $target): void {
                        $reverse
                            ->where('source_actor_id', $target->id)
                            ->where('target_actor_id', $source->id);
                    });
            })
            ->lockForUpdate()
            ->get();

        foreach ($requests as $request) {
            $fromStatus = $request->status->value;
            $request->forceFill([
                'status' => SocialRequestStatus::Blocked->value,
                'active_key' => null,
                'decided_by_user_id' => $actor->id,
                'decided_at' => now(),
                'lock_version' => $request->lock_version + 1,
            ])->save();
            $this->events->record(
                source: $source,
                target: $target,
                representedActor: $source,
                actor: $actor,
                eventType: 'request-blocked',
                type: $request->relationship_type,
                idempotencyKey: "block:{$block->relationship_key}:request:{$request->id}",
                fromStatus: $fromStatus,
                toStatus: SocialRequestStatus::Blocked->value,
                reasonCode: 'blocked',
                request: $request,
            );
        }

        $relationships = SocialRelationship::query()
            ->select([
                'id',
                'relationship_key',
                'source_actor_id',
                'target_actor_id',
                'relationship_type',
                'status',
                'active_key',
                'reason_code',
                'lock_version',
                'ended_at',
            ])
            ->active()
            ->where('relationship_type', '!=', SocialRelationshipType::Block->value)
            ->where(function ($query) use ($source, $target): void {
                $query
                    ->where(function ($direct) use ($source, $target): void {
                        $direct
                            ->where('source_actor_id', $source->id)
                            ->where('target_actor_id', $target->id);
                    })
                    ->orWhere(function ($reverse) use ($source, $target): void {
                        $reverse
                            ->where('source_actor_id', $target->id)
                            ->where('target_actor_id', $source->id);
                    });
            })
            ->lockForUpdate()
            ->get();

        foreach ($relationships as $relationship) {
            $fromStatus = $relationship->status->value;
            $relationship->forceFill([
                'status' => SocialRelationshipStatus::Ended->value,
                'active_key' => null,
                'reason_code' => 'blocked',
                'ended_at' => now(),
                'lock_version' => $relationship->lock_version + 1,
            ])->save();
            $this->events->record(
                source: $source,
                target: $target,
                representedActor: $source,
                actor: $actor,
                eventType: 'relationship-ended-by-block',
                type: $relationship->relationship_type,
                idempotencyKey: "block:{$block->relationship_key}:relationship:{$relationship->id}",
                fromStatus: $fromStatus,
                toStatus: SocialRelationshipStatus::Ended->value,
                reasonCode: 'blocked',
                relationship: $relationship,
            );
        }
    }
}
