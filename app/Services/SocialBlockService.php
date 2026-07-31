<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class SocialBlockService
{
    public function __construct(private readonly SocialAccountActorQuery $accountActors) {}

    public function blockedBetween(SocialActor $first, SocialActor $second): bool
    {
        return SocialRelationship::query()
            ->active()
            ->where('relationship_type', SocialRelationshipType::Block->value)
            ->where(function ($query) use ($first, $second): void {
                $query
                    ->where(function ($direct) use ($first, $second): void {
                        $direct
                            ->where('source_actor_id', $first->id)
                            ->where('target_actor_id', $second->id);
                    })
                    ->orWhere(function ($reverse) use ($first, $second): void {
                        $reverse
                            ->where('source_actor_id', $second->id)
                            ->where('target_actor_id', $first->id);
                    });
            })
            ->exists();
    }

    public function blockedForContact(User $sourceUser, SocialActor $target): bool
    {
        $targetUserIds = $this->accountActors->controllerUserIds($target);

        return $this->accountBlockedBetween([$sourceUser->id], $targetUserIds);
    }

    /**
     * @param  list<int>  $firstUserIds
     * @param  list<int>  $secondUserIds
     */
    public function accountBlockedBetween(array $firstUserIds, array $secondUserIds): bool
    {
        if ($firstUserIds === [] || $secondUserIds === []) {
            return false;
        }

        return SocialAccountBlock::query()
            ->active()
            ->where(function ($query) use ($firstUserIds, $secondUserIds): void {
                $query
                    ->where(function ($direct) use ($firstUserIds, $secondUserIds): void {
                        $direct
                            ->whereIn('blocker_user_id', $firstUserIds)
                            ->whereIn('blocked_user_id', $secondUserIds);
                    })
                    ->orWhere(function ($reverse) use ($firstUserIds, $secondUserIds): void {
                        $reverse
                            ->whereIn('blocker_user_id', $secondUserIds)
                            ->whereIn('blocked_user_id', $firstUserIds);
                    });
            })
            ->exists();
    }

    /** @return list<int> */
    public function blockedActorIdsFor(User $user): array
    {
        $blockedUserIds = SocialAccountBlock::query()
            ->select(['id', 'blocker_user_id', 'blocked_user_id'])
            ->active()
            ->where(function ($query) use ($user): void {
                $query
                    ->where('blocker_user_id', $user->id)
                    ->orWhere('blocked_user_id', $user->id);
            })
            ->orderBy('id')
            ->limit((int) config('social_relationships.account_block_limit', 1000))
            ->get()
            ->map(static fn (SocialAccountBlock $block): int => $block->blocker_user_id === $user->id
                    ? $block->blocked_user_id
                    : $block->blocker_user_id)
            ->unique()
            ->values()
            ->all();

        if ($blockedUserIds === []) {
            return [];
        }

        $ownActorIds = $this->accountActors->controlledBy($user)->modelKeys();

        return $this->accountActors
            ->controlledByUserIds($blockedUserIds)
            ->filter(static fn (SocialActor $actor): bool => ! in_array($actor->id, $ownActorIds, true))
            ->map(static fn (SocialActor $actor): int => $actor->id)
            ->values()
            ->all();
    }

    /** @return Collection<int, SocialAccountBlock> */
    public function outgoingAccountBlocks(User $user): Collection
    {
        return SocialAccountBlock::query()
            ->select([
                'id',
                'block_key',
                'blocker_user_id',
                'blocked_user_id',
                'source_actor_id',
                'target_actor_id',
                'status',
                'scope',
                'reason_code',
                'lock_version',
                'blocked_at',
                'revoked_at',
                'created_at',
                'updated_at',
            ])
            ->with('blockedUser:id,name,actor_key')
            ->active()
            ->where('blocker_user_id', $user->id)
            ->orderByDesc('blocked_at')
            ->orderByDesc('id')
            ->limit((int) config('social_relationships.account_block_limit', 1000))
            ->get();
    }
}
