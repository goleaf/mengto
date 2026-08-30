<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserOnboarding;

final readonly class AccountEntryDestination
{
    public function __construct(private EmailVerificationMode $emailVerification) {}

    public function pendingRoute(User $user): ?string
    {
        if ($this->emailVerification->isEnabled() && ! $user->hasVerifiedEmail()) {
            return 'verification.notice';
        }

        $onboarding = $user->onboarding()->first();

        if ($onboarding instanceof UserOnboarding && ! $onboarding->isComplete()) {
            return 'onboarding.show';
        }

        return null;
    }
}
