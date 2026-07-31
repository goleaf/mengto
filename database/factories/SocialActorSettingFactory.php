<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;

/** @extends ApplicationFactory<SocialActorSetting> */
final class SocialActorSettingFactory extends ApplicationFactory
{
    protected $model = SocialActorSetting::class;

    public function definition(): array
    {
        return [
            'social_actor_id' => SocialActor::factory(),
            'friend_request_policy' => SocialFriendRequestPolicy::Everyone,
            'follow_policy' => SocialFollowPolicy::Public,
            'friend_list_visibility' => SocialListVisibility::Friends,
            'follower_list_visibility' => SocialListVisibility::CountOnly,
            'is_recommendable' => true,
            'allow_message_requests' => true,
            'lock_version' => 1,
            'updated_by_user_id' => null,
        ];
    }
}
