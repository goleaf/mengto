<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumEventRegistration;
use App\Models\User;

final class ForumEventRegistrationPolicy
{
    public function cancelRegistration(
        ?User $user,
        ForumEventRegistration $registration,
    ): bool {
        return $user?->isActive() === true
            && $registration->user_id === $user->id
            && $registration->status->canCancel();
    }
}
