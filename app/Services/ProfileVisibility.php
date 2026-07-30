<?php

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
            'public' => 'Everyone',
            'members' => 'Registered members',
            'followers' => 'Followers',
            'friends' => 'Friends',
            'owners' => 'Owners and managers',
            'hidden' => 'Hidden',
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
