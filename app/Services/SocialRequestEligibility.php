<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumGroupMembershipState;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialRelationshipType;
use App\Models\ForumEventRegistration;
use App\Models\ForumGroupMembership;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\User;

final class SocialRequestEligibility
{
    public function __construct(private readonly SocialActorAccess $access) {}

    public function allows(
        SocialFriendRequestPolicy $policy,
        User $sourceUser,
        SocialActor $targetActor,
    ): bool {
        return match ($policy) {
            SocialFriendRequestPolicy::Everyone => true,
            SocialFriendRequestPolicy::FriendsOfFriends => $this->hasMutualFriend(
                $sourceUser,
                $targetActor,
            ),
            SocialFriendRequestPolicy::SharedGroups => $this->sharesGroup(
                $sourceUser,
                $targetActor,
            ),
            SocialFriendRequestPolicy::SharedEvents => $this->sharesEvent(
                $sourceUser,
                $targetActor,
            ),
            SocialFriendRequestPolicy::LocalProfiles,
            SocialFriendRequestPolicy::LinkOnly,
            SocialFriendRequestPolicy::Nobody => false,
        };
    }

    private function hasMutualFriend(User $sourceUser, SocialActor $targetActor): bool
    {
        $targetUserId = $this->access->primaryControllerUserId($targetActor);

        if ($targetUserId === null || $targetUserId === $sourceUser->id) {
            return false;
        }

        $actorIds = SocialActor::query()
            ->select(['id', 'user_id'])
            ->whereIn('user_id', [$sourceUser->id, $targetUserId])
            ->get()
            ->keyBy('user_id');
        $sourceActor = $actorIds->get($sourceUser->id);
        $targetUserActor = $actorIds->get($targetUserId);

        if (! $sourceActor instanceof SocialActor || ! $targetUserActor instanceof SocialActor) {
            return false;
        }

        $sourceFriendIds = SocialRelationship::query()
            ->select(['id', 'source_actor_id', 'target_actor_id'])
            ->active()
            ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
            ->where(function ($query) use ($sourceActor): void {
                $query
                    ->where('source_actor_id', $sourceActor->id)
                    ->orWhere('target_actor_id', $sourceActor->id);
            })
            ->orderBy('id')
            ->limit(1000)
            ->get()
            ->map(static fn (SocialRelationship $relationship): int => $relationship->source_actor_id === $sourceActor->id
                    ? $relationship->target_actor_id
                    : $relationship->source_actor_id)
            ->unique()
            ->values()
            ->all();

        if ($sourceFriendIds === []) {
            return false;
        }

        return SocialRelationship::query()
            ->active()
            ->where('relationship_type', SocialRelationshipType::OwnerFriendship->value)
            ->where(function ($query) use ($sourceFriendIds, $targetUserActor): void {
                $query
                    ->where(function ($direct) use ($sourceFriendIds, $targetUserActor): void {
                        $direct
                            ->where('source_actor_id', $targetUserActor->id)
                            ->whereIn('target_actor_id', $sourceFriendIds);
                    })
                    ->orWhere(function ($reverse) use ($sourceFriendIds, $targetUserActor): void {
                        $reverse
                            ->where('target_actor_id', $targetUserActor->id)
                            ->whereIn('source_actor_id', $sourceFriendIds);
                    });
            })
            ->exists();
    }

    private function sharesGroup(User $sourceUser, SocialActor $targetActor): bool
    {
        $targetUserId = $this->access->primaryControllerUserId($targetActor);

        if ($targetUserId === null || $targetUserId === $sourceUser->id) {
            return false;
        }

        $groupIds = ForumGroupMembership::query()
            ->select(['id', 'forum_group_id'])
            ->where('user_id', $sourceUser->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->orderBy('id')
            ->limit(1000)
            ->pluck('forum_group_id');

        return $groupIds->isNotEmpty()
            && ForumGroupMembership::query()
                ->where('user_id', $targetUserId)
                ->where('state', ForumGroupMembershipState::Active->value)
                ->whereIn('forum_group_id', $groupIds)
                ->exists();
    }

    private function sharesEvent(User $sourceUser, SocialActor $targetActor): bool
    {
        $targetUserId = $this->access->primaryControllerUserId($targetActor);

        if ($targetUserId === null || $targetUserId === $sourceUser->id) {
            return false;
        }

        $statuses = [
            ForumEventRegistrationStatus::Confirmed->value,
            ForumEventRegistrationStatus::CheckedIn->value,
        ];
        $eventIds = ForumEventRegistration::query()
            ->select(['id', 'forum_event_id'])
            ->where('user_id', $sourceUser->id)
            ->whereIn('status', $statuses)
            ->orderBy('id')
            ->limit(1000)
            ->pluck('forum_event_id');

        return $eventIds->isNotEmpty()
            && ForumEventRegistration::query()
                ->where('user_id', $targetUserId)
                ->whereIn('status', $statuses)
                ->whereIn('forum_event_id', $eventIds)
                ->exists();
    }
}
