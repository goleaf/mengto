<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\HomeDestination;
use App\Models\User;

final class HomeDestinationResolver
{
    public function resolve(?User $user): HomeDestination
    {
        if (! $user instanceof User) {
            return HomeDestination::Join;
        }

        if (! $user->isActive()) {
            return HomeDestination::UnavailableAccount;
        }

        if (! $user->hasVerifiedEmail()) {
            return HomeDestination::VerifyEmail;
        }

        return HomeDestination::ContentFeed;
    }
}
