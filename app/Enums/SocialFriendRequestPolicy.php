<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialFriendRequestPolicy: string
{
    case Everyone = 'everyone';
    case FriendsOfFriends = 'friends-of-friends';
    case SharedGroups = 'shared-groups';
    case SharedEvents = 'shared-events';
    case LocalProfiles = 'local-profiles';
    case LinkOnly = 'link-only';
    case Nobody = 'nobody';

    /** @return list<self> */
    public static function enforceableCases(): array
    {
        return [
            self::Everyone,
            self::FriendsOfFriends,
            self::SharedGroups,
            self::SharedEvents,
            self::Nobody,
        ];
    }

    public function isEnforceable(): bool
    {
        return in_array($this, self::enforceableCases(), true);
    }

    public function label(): string
    {
        return __("social_relationships.friend_request_policies.{$this->value}");
    }
}
