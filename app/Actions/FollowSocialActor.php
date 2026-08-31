<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialFollowPolicy;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Services\ForumActor;
use App\Services\SocialBlockService;
use App\Services\SocialGraphCache;
use App\Services\SocialIdempotencyGuard;
use App\Services\SocialRelationshipEventRecorder;
use App\Services\SocialRelationshipKey;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class FollowSocialActor
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SendSocialRelationshipRequest $sendRequest,
        private readonly SocialBlockService $blocks,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    public function handle(
        SocialActor $source,
        SocialActor $target,
        string $idempotencyKey,
    ): SocialRelationship|SocialRelationshipRequest {
        $user = $this->actor->requireUser();
        $this->gate->authorize('represent', $source);
        $this->gate->authorize('view', $target);

        if ($source->is($target)) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.cannot_connect_self'),
            ]);
        }

        return DB::transaction(function () use ($source, $target, $idempotencyKey, $user): SocialRelationship|SocialRelationshipRequest {
            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent?->social_relationship_id !== null) {
                $this->idempotency->assertEventMatches(
                    $existingEvent,
                    $source,
                    $target,
                    SocialRelationshipType::Follow,
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
            $this->gate->authorize('view', $lockedTarget);

            if ($this->blocks->blockedBetween($lockedSource, $lockedTarget)
                || $this->blocks->blockedForContact($user, $lockedTarget)) {
                throw ValidationException::withMessages([
                    'target' => __('social_relationships.validation.contact_unavailable'),
                ]);
            }

            $settings = SocialActorSetting::query()
                ->where('social_actor_id', $lockedTarget->id)
                ->lockForUpdate()
                ->first();

            if (! $settings instanceof SocialActorSetting
                || $settings->follow_policy === SocialFollowPolicy::Nobody) {
                throw ValidationException::withMessages([
                    'target' => __('social_relationships.validation.requests_disabled'),
                ]);
            }

            if ($settings->follow_policy === SocialFollowPolicy::Approval) {
                return $this->sendRequest->handle(
                    source: $lockedSource,
                    target: $lockedTarget,
                    type: SocialRelationshipType::Follow,
                    idempotencyKey: $idempotencyKey,
                );
            }

            $activeKey = SocialRelationshipKey::forRelationship(
                $lockedSource->id,
                $lockedTarget->id,
                SocialRelationshipType::Follow,
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
                    'relationship_type' => SocialRelationshipType::Follow,
                    'direction' => SocialRelationshipType::Follow->direction(),
                    'status' => SocialRelationshipStatus::Active,
                    'active_key' => $activeKey,
                    'visibility' => 'private',
                    'created_by_user_id' => $user->id,
                    'lock_version' => 1,
                    'started_at' => now(),
                ]);
            }

            $this->events->record(
                source: $lockedSource,
                target: $lockedTarget,
                representedActor: $lockedSource,
                actor: $user,
                eventType: 'relationship-created',
                type: SocialRelationshipType::Follow,
                idempotencyKey: $idempotencyKey,
                toStatus: SocialRelationshipStatus::Active->value,
                relationship: $relationship,
            );
            $this->cache->invalidate($lockedSource, $lockedTarget);

            return $relationship->refresh();
        }, 3);
    }
}
