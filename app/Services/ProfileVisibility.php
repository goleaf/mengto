<?php

declare(strict_types=1);

namespace App\Services;

final class ProfileVisibility
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return ['public', 'members', 'followers', 'friends', 'owners', 'hidden'];
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return [
            'public' => __('messages.visibility.everyone'),
            'members' => __('messages.registered_members_1757e26849'),
            'followers' => __('messages.followers_a145ab342a'),
            'friends' => __('messages.visibility.friends'),
            'owners' => __('messages.owners_and_managers_28539cb842'),
            'hidden' => __('messages.visibility.hidden'),
        ];
    }

    public function allows(string $visibility, string $audience): bool
    {
        if ($audience === 'owner') {
            return true;
        }

        $audienceRank = match ($audience) {
            'friend' => 3,
            'follower' => 2,
            default => 0,
        };

        $requiredRank = match ($visibility) {
            'public' => 0,
            'members' => 1,
            'followers' => 2,
            'friends' => 3,
            'owners', 'hidden' => 4,
            default => 4,
        };

        return $audienceRank >= $requiredRank;
    }
}
