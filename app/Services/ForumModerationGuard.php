<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumEvent;
use App\Models\ForumModerationAction;
use App\Models\User;

final class ForumModerationGuard
{
    /** @var list<string> */
    public const USER_SUSPENSION_KEYS = [
        'temporary-suspension',
        'permanent-suspension',
        'emergency-account-protection',
    ];

    /** @var list<string> */
    public const CONTENT_HIDING_KEYS = [
        'temporary-content-hiding',
        'content-removal',
        'sensitive-data-removal',
    ];

    public function userIsSuspended(User $user): bool
    {
        return ForumModerationAction::query()
            ->currentlyActive()
            ->where('target_user_id', $user->id)
            ->whereHas('definition', static fn ($definitions) => $definitions
                ->whereIn('stable_key', self::USER_SUSPENSION_KEYS))
            ->exists();
    }

    public function hides(ForumEvent $event): bool
    {
        return $event->moderationActions()
            ->currentlyActive()
            ->whereHas('definition', static fn ($definitions) => $definitions
                ->whereIn('stable_key', self::CONTENT_HIDING_KEYS))
            ->exists();
    }
}
