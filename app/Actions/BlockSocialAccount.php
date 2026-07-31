<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialAccountBlockStatus;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\SocialAccountActorQuery;
use App\Services\SocialGraphCache;
use App\Services\SocialRelationshipEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class BlockSocialAccount
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialAccountActorQuery $accountActors,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
    ) {}

    public function handle(
        SocialActor $source,
        SocialActor $target,
        User $blockedUser,
        string $idempotencyKey,
        ?string $reasonCode = null,
    ): SocialAccountBlock {
        $blocker = $this->actor->requireUser();
        $this->gate->authorize('represent', $source);

        if ($blocker->id === $blockedUser->id
            || ! $this->accountActors->controlledByUser($target, $blockedUser)) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.account_block_unavailable'),
            ]);
        }

        return DB::transaction(function () use (
            $source,
            $target,
            $blockedUser,
            $idempotencyKey,
            $reasonCode,
            $blocker,
        ): SocialAccountBlock {
            $existingByIdempotency = SocialAccountBlock::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingByIdempotency instanceof SocialAccountBlock) {
                $this->assertMatches($existingByIdempotency, $blocker, $blockedUser);

                return $existingByIdempotency;
            }

            User::query()
                ->whereIn('id', [$blocker->id, $blockedUser->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $activeKey = hash('sha256', "{$blocker->id}|{$blockedUser->id}");
            $block = SocialAccountBlock::query()
                ->where('active_key', $activeKey)
                ->lockForUpdate()
                ->first();

            if ($block instanceof SocialAccountBlock) {
                return $block;
            }

            $block = SocialAccountBlock::query()->create([
                'block_key' => (string) Str::uuid(),
                'blocker_user_id' => $blocker->id,
                'blocked_user_id' => $blockedUser->id,
                'source_actor_id' => $source->id,
                'target_actor_id' => $target->id,
                'status' => SocialAccountBlockStatus::Active,
                'scope' => 'all-managed-profiles',
                'active_key' => $activeKey,
                'idempotency_key' => $idempotencyKey,
                'reason_code' => $reasonCode,
                'lock_version' => 1,
                'created_by_user_id' => $blocker->id,
                'blocked_at' => now(),
            ]);

            $sourceActors = $this->accountActors->controlledBy($blocker);
            $targetActors = $this->accountActors->controlledBy($blockedUser);
            $sourceActorIds = $sourceActors->modelKeys();
            $targetActorIds = collect($targetActors->modelKeys())
                ->reject(static fn (int $id): bool => in_array($id, $sourceActorIds, true))
                ->values()
                ->all();
            $closedRequests = $this->closeRequests(
                $sourceActorIds,
                $targetActorIds,
                $blocker,
            );
            $closedRelationships = $this->closeRelationships(
                $sourceActorIds,
                $targetActorIds,
            );

            $this->events->record(
                source: $source,
                target: $target,
                representedActor: $source,
                actor: $blocker,
                eventType: 'account-block-created',
                type: SocialRelationshipType::Block,
                idempotencyKey: "{$idempotencyKey}:event",
                toStatus: SocialAccountBlockStatus::Active->value,
                reasonCode: $reasonCode,
                accountBlock: $block,
                publicMetadata: [
                    'scope' => 'all-managed-profiles',
                    'closed_requests' => $closedRequests,
                    'closed_relationships' => $closedRelationships,
                ],
            );
            $this->cache->invalidate(...$sourceActors, ...$targetActors);

            return $block->refresh();
        }, 3);
    }

    /** @param list<int> $sourceActorIds @param list<int> $targetActorIds */
    private function closeRequests(array $sourceActorIds, array $targetActorIds, User $blocker): int
    {
        if ($sourceActorIds === [] || $targetActorIds === []) {
            return 0;
        }

        return SocialRelationshipRequest::query()
            ->open()
            ->where($this->pairConstraint($sourceActorIds, $targetActorIds))
            ->incrementEach(['lock_version' => 1], [
                'status' => SocialRequestStatus::Blocked->value,
                'active_key' => null,
                'decided_by_user_id' => $blocker->id,
                'reason_code' => 'account-blocked',
                'decided_at' => now(),
                'repeat_after' => null,
                'prevent_repeats' => true,
            ]);
    }

    /** @param list<int> $sourceActorIds @param list<int> $targetActorIds */
    private function closeRelationships(array $sourceActorIds, array $targetActorIds): int
    {
        if ($sourceActorIds === [] || $targetActorIds === []) {
            return 0;
        }

        return SocialRelationship::query()
            ->active()
            ->where('relationship_type', '!=', SocialRelationshipType::Block->value)
            ->where($this->pairConstraint($sourceActorIds, $targetActorIds))
            ->incrementEach(['lock_version' => 1], [
                'status' => SocialRelationshipStatus::Ended->value,
                'active_key' => null,
                'reason_code' => 'account-blocked',
                'ended_at' => now(),
            ]);
    }

    /**
     * @param  list<int>  $sourceActorIds
     * @param  list<int>  $targetActorIds
     */
    private function pairConstraint(array $sourceActorIds, array $targetActorIds): \Closure
    {
        return static function ($query) use ($sourceActorIds, $targetActorIds): void {
            $query
                ->where(function ($direct) use ($sourceActorIds, $targetActorIds): void {
                    $direct
                        ->whereIn('source_actor_id', $sourceActorIds)
                        ->whereIn('target_actor_id', $targetActorIds);
                })
                ->orWhere(function ($reverse) use ($sourceActorIds, $targetActorIds): void {
                    $reverse
                        ->whereIn('source_actor_id', $targetActorIds)
                        ->whereIn('target_actor_id', $sourceActorIds);
                });
        };
    }

    private function assertMatches(
        SocialAccountBlock $block,
        User $blocker,
        User $blocked,
    ): void {
        if ($block->blocker_user_id !== $blocker->id || $block->blocked_user_id !== $blocked->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('social_relationships.validation.idempotency_conflict'),
            ]);
        }
    }
}
