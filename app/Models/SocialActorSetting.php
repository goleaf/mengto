<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialFollowPolicy;
use App\Enums\SocialFriendRequestPolicy;
use App\Enums\SocialListVisibility;
use Database\Factories\SocialActorSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property bool $allow_message_requests
 * @property SocialListVisibility $follower_list_visibility
 * @property SocialFollowPolicy $follow_policy
 * @property SocialListVisibility $friend_list_visibility
 * @property SocialFriendRequestPolicy $friend_request_policy
 * @property int $id
 * @property bool $is_recommendable
 * @property int $lock_version
 * @property int $social_actor_id
 * @property int|null $updated_by_user_id
 * @property-read SocialActor $actor
 * @property-read User|null $updatedBy
 */
final class SocialActorSetting extends Model
{
    /** @use HasFactory<SocialActorSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'social_actor_id',
        'friend_request_policy',
        'follow_policy',
        'friend_list_visibility',
        'follower_list_visibility',
        'is_recommendable',
        'allow_message_requests',
        'lock_version',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'friend_request_policy' => SocialFriendRequestPolicy::class,
            'follow_policy' => SocialFollowPolicy::class,
            'friend_list_visibility' => SocialListVisibility::class,
            'follower_list_visibility' => SocialListVisibility::class,
            'is_recommendable' => 'boolean',
            'allow_message_requests' => 'boolean',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'social_actor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
