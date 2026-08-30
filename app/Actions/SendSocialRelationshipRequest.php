<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialActorType;
use App\Enums\SocialFollowPolicy;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\SocialBlockService;
use App\Services\SocialGraphCache;
use App\Services\SocialIdempotencyGuard;
use App\Services\SocialRelationshipEventRecorder;
use App\Services\SocialRelationshipKey;
use App\Services\SocialRequestAbuseGuard;
use App\Services\SocialRequestEligibility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SendSocialRelationshipRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialBlockService $blocks,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialRequestEligibility $eligibility,
        private readonly SocialRequestAbuseGuard $abuse,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    /** @param array<string, mixed>|null $metadata */
    public function handle(
        SocialActor $source,
        SocialActor $target,
        SocialRelationshipType $type,
        string $idempotencyKey,
        ?string $message = null,
        ?string $contextType = null,
        ?string $contextKey = null,
        ?array $metadata = null,
    ): SocialRelationshipRequest {
        $user = $this->actor->requireUser();
        $this->gate->authorize('represent', $source);
        $this->gate->authorize('view', $target);
        $this->validateEndpoints($source, $target, $type);

        return DB::transaction(function () use (
            $source,
            $target,
            $type,
            $idempotencyKey,
            $message,
            $contextType,
            $contextKey,
            $metadata,
            $user,
        ): SocialRelationshipRequest {
            $existing = SocialRelationshipRequest::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof SocialRelationshipRequest) {
                $this->idempotency->assertRequestMatches(
                    $existing,
                    $source,
                    $target,
                    $type,
                );

                return $existing;
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

            $assessment = $this->abuse->assess($user, $lockedTarget, $type, $message);

            $settings = SocialActorSetting::query()->firstOrCreate([
                'social_actor_id' => $lockedTarget->id,
            ]);
            $this->validateRecipientPolicy($settings, $type, $user, $lockedTarget);

            $activeKey = SocialRelationshipKey::forRequest(
                $lockedSource->id,
                $lockedTarget->id,
                $type,
            );
            $open = SocialRelationshipRequest::query()
                ->where('active_key', $activeKey)
                ->lockForUpdate()
                ->first();

            if ($open instanceof SocialRelationshipRequest) {
                return $open;
            }

            $request = SocialRelationshipRequest::query()->create([
                'request_key' => (string) Str::uuid(),
                'source_actor_id' => $lockedSource->id,
                'target_actor_id' => $lockedTarget->id,
                'relationship_type' => $type,
                'direction' => $type->direction(),
                'status' => SocialRequestStatus::Pending,
                'active_key' => $activeKey,
                'idempotency_key' => $idempotencyKey,
                'created_by_user_id' => $user->id,
                'context_type' => $contextType,
                'context_key' => $contextKey,
                'message' => $assessment['message'],
                'message_fingerprint' => $assessment['message_fingerprint'],
                'risk_level' => $assessment['risk_level'],
                'risk_signals' => $assessment['risk_signals'],
                'lock_version' => 1,
                'metadata' => $metadata,
                'sent_at' => now(),
                'delivered_at' => now(),
                'expires_at' => now()->addDays((int) config('social_relationships.request_ttl_days', 30)),
            ]);

            $this->events->record(
                source: $lockedSource,
                target: $lockedTarget,
                representedActor: $lockedSource,
                actor: $user,
                eventType: 'request-sent',
                type: $type,
                idempotencyKey: "{$idempotencyKey}:event",
                toStatus: SocialRequestStatus::Pending->value,
                request: $request,
                publicMetadata: ['context_type' => $contextType],
            );
            $this->cache->invalidate($lockedSource, $lockedTarget);

            return $request->refresh();
        }, 3);
    }

    private function validateEndpoints(
        SocialActor $source,
        SocialActor $target,
        SocialRelationshipType $type,
    ): void {
        if ($source->is($target)) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.cannot_connect_self'),
            ]);
        }

        if (! $type->requiresAcceptance() && $type !== SocialRelationshipType::Follow) {
            throw ValidationException::withMessages([
                'relationship_type' => __('social_relationships.validation.request_type_unavailable'),
            ]);
        }

        if ($type === SocialRelationshipType::OwnerFriendship
            && ($source->actor_type !== SocialActorType::User || $target->actor_type !== SocialActorType::User)) {
            throw ValidationException::withMessages([
                'relationship_type' => __('social_relationships.validation.owner_friendship_requires_users'),
            ]);
        }

        if ($type === SocialRelationshipType::PetFriendship
            && ($source->actor_type !== SocialActorType::Pet || $target->actor_type !== SocialActorType::Pet)) {
            throw ValidationException::withMessages([
                'relationship_type' => __('social_relationships.validation.pet_friendship_requires_pets'),
            ]);
        }
    }

    private function validateRecipientPolicy(
        SocialActorSetting $settings,
        SocialRelationshipType $type,
        User $sourceUser,
        SocialActor $targetActor,
    ): void {
        if ($type === SocialRelationshipType::Follow) {
            if ($settings->follow_policy === SocialFollowPolicy::Nobody) {
                throw ValidationException::withMessages([
                    'target' => __('social_relationships.validation.requests_disabled'),
                ]);
            }

            return;
        }

        if (! $this->eligibility->allows(
            $settings->friend_request_policy,
            $sourceUser,
            $targetActor,
        )) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.requests_disabled'),
            ]);
        }
    }
}
