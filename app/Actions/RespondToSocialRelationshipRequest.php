<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRequestStatus;
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

final class RespondToSocialRelationshipRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialBlockService $blocks,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    public function handle(
        SocialRelationshipRequest $request,
        SocialRequestStatus $decision,
        string $idempotencyKey,
        ?string $reasonCode = null,
    ): SocialRelationshipRequest {
        if (! in_array($decision, [
            SocialRequestStatus::Accepted,
            SocialRequestStatus::Declined,
            SocialRequestStatus::Hidden,
        ], true)) {
            throw ValidationException::withMessages([
                'decision' => __('social_relationships.validation.decision_unavailable'),
            ]);
        }

        $user = $this->actor->requireUser();

        /** @var array{request: SocialRelationshipRequest, expired: bool} $result */
        $result = DB::transaction(function () use (
            $request,
            $decision,
            $idempotencyKey,
            $reasonCode,
            $user,
        ): array {
            $locked = SocialRelationshipRequest::query()
                ->with(['sourceActor', 'targetActor'])
                ->lockForUpdate()
                ->findOrFail($request->id);
            $this->gate->authorize('respond', $locked);

            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', "{$idempotencyKey}:request")
                ->first();

            if ($existingEvent instanceof SocialRelationshipEvent) {
                $this->idempotency->assertEventMatches(
                    $existingEvent,
                    $locked->sourceActor,
                    $locked->targetActor,
                    $locked->relationship_type,
                    $locked,
                );

                return [
                    'request' => $locked,
                    'expired' => $locked->status === SocialRequestStatus::Expired,
                ];
            }

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages([
                    'request' => __('social_relationships.validation.request_unavailable'),
                ]);
            }

            if ($locked->expires_at !== null && $locked->expires_at->isPast()) {
                $locked->forceFill([
                    'status' => SocialRequestStatus::Expired,
                    'active_key' => null,
                    'decided_at' => now(),
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                $this->events->record(
                    source: $locked->sourceActor,
                    target: $locked->targetActor,
                    representedActor: $locked->targetActor,
                    actor: $user,
                    eventType: 'request-expired',
                    type: $locked->relationship_type,
                    idempotencyKey: "{$idempotencyKey}:request",
                    fromStatus: SocialRequestStatus::Pending->value,
                    toStatus: SocialRequestStatus::Expired->value,
                    request: $locked,
                );
                $this->cache->invalidate($locked->sourceActor, $locked->targetActor);

                return [
                    'request' => $locked->refresh(),
                    'expired' => true,
                ];
            }

            if ($decision === SocialRequestStatus::Accepted
                && $this->blocks->blockedBetween($locked->sourceActor, $locked->targetActor)) {
                throw ValidationException::withMessages([
                    'request' => __('social_relationships.validation.contact_unavailable'),
                ]);
            }

            $relationship = $decision === SocialRequestStatus::Accepted
                ? $this->activateRelationship($locked, $user->id)
                : null;
            $repeatAfter = $decision === SocialRequestStatus::Declined
                ? now()->addDays((int) config('social_relationships.repeat_cooldown_days', 30))
                : null;

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => $decision,
                'active_key' => null,
                'decided_by_user_id' => $user->id,
                'reason_code' => $reasonCode,
                'decided_at' => now(),
                'repeat_after' => $repeatAfter,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->events->record(
                source: $locked->sourceActor,
                target: $locked->targetActor,
                representedActor: $locked->targetActor,
                actor: $user,
                eventType: "request-{$decision->value}",
                type: $locked->relationship_type,
                idempotencyKey: "{$idempotencyKey}:request",
                fromStatus: $fromStatus,
                toStatus: $decision->value,
                reasonCode: $reasonCode,
                relationship: $relationship,
                request: $locked,
            );

            if ($relationship instanceof SocialRelationship) {
                $this->events->record(
                    source: $locked->sourceActor,
                    target: $locked->targetActor,
                    representedActor: $locked->targetActor,
                    actor: $user,
                    eventType: 'relationship-created',
                    type: $locked->relationship_type,
                    idempotencyKey: "{$idempotencyKey}:relationship",
                    toStatus: SocialRelationshipStatus::Active->value,
                    relationship: $relationship,
                    request: $locked,
                );
            }

            $this->cache->invalidate($locked->sourceActor, $locked->targetActor);

            return [
                'request' => $locked->refresh(),
                'expired' => false,
            ];
        }, 3);

        if ($result['expired']) {
            throw ValidationException::withMessages([
                'request' => __('social_relationships.validation.request_expired'),
            ]);
        }

        return $result['request'];
    }

    private function activateRelationship(
        SocialRelationshipRequest $request,
        int $acceptingUserId,
    ): SocialRelationship {
        $activeKey = SocialRelationshipKey::forRelationship(
            $request->source_actor_id,
            $request->target_actor_id,
            $request->relationship_type,
        );
        $existing = SocialRelationship::query()
            ->where('active_key', $activeKey)
            ->lockForUpdate()
            ->first();

        if ($existing instanceof SocialRelationship) {
            return $existing;
        }

        return SocialRelationship::query()->create([
            'relationship_key' => (string) Str::uuid(),
            'source_actor_id' => $request->source_actor_id,
            'target_actor_id' => $request->target_actor_id,
            'request_id' => $request->id,
            'relationship_type' => $request->relationship_type,
            'direction' => $request->direction,
            'status' => SocialRelationshipStatus::Active,
            'active_key' => $activeKey,
            'visibility' => 'private',
            'created_by_user_id' => $request->created_by_user_id,
            'accepted_by_user_id' => $acceptingUserId,
            'context_type' => $request->context_type,
            'context_key' => $request->context_key,
            'lock_version' => 1,
            'started_at' => now(),
        ]);
    }
}
