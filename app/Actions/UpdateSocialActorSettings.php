<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Services\ForumActor;
use App\Services\SocialGraphCache;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateSocialActorSettings
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialGraphCache $cache,
    ) {}

    public function handle(
        SocialActor $actor,
        SocialFriendRequestPolicy $friendRequestPolicy,
        SocialFollowPolicy $followPolicy,
        SocialListVisibility $friendListVisibility,
        SocialListVisibility $followerListVisibility,
        bool $isDiscoverable,
        bool $isRecommendable,
        bool $allowMessageRequests,
        int $expectedLockVersion,
    ): SocialActorSetting {
        $user = $this->actor->requireUser();
        $this->gate->authorize('updateSettings', $actor);

        if (! $friendRequestPolicy->isEnforceable()) {
            throw ValidationException::withMessages([
                'friend_request_policy' => __('social_relationships.validation.requests_disabled'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $friendRequestPolicy,
            $followPolicy,
            $friendListVisibility,
            $followerListVisibility,
            $isDiscoverable,
            $isRecommendable,
            $allowMessageRequests,
            $expectedLockVersion,
            $user,
        ): SocialActorSetting {
            $lockedActor = SocialActor::query()
                ->lockForUpdate()
                ->findOrFail($actor->id);
            $this->gate->authorize('updateSettings', $lockedActor);
            $settings = SocialActorSetting::query()
                ->where('social_actor_id', $lockedActor->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($settings->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'settings' => __('social_relationships.validation.settings_changed'),
                ]);
            }

            $settings->forceFill([
                'friend_request_policy' => $friendRequestPolicy,
                'follow_policy' => $followPolicy,
                'friend_list_visibility' => $friendListVisibility,
                'follower_list_visibility' => $followerListVisibility,
                'is_recommendable' => $isRecommendable,
                'allow_message_requests' => $allowMessageRequests,
                'updated_by_user_id' => $user->id,
                'lock_version' => $settings->lock_version + 1,
            ])->save();

            $lockedActor->forceFill([
                'is_discoverable' => $isDiscoverable,
                'lock_version' => $lockedActor->lock_version + 1,
            ])->save();
            $this->cache->invalidate($lockedActor);

            return $settings->refresh();
        }, 3);
    }
}
