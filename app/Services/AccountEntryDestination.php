<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class AccountEntryDestination
{
    public function __construct(
        private EmailVerificationMode $emailVerification,
        private SafeIntendedUrl $intendedUrl,
    ) {}

    public function urlFor(User $user, string $fallback): string
    {
        $pendingRoute = $this->pendingRoute($user);

        return is_string($pendingRoute)
            ? route($pendingRoute)
            : $this->intendedUrl->pull($fallback);
    }

    private function pendingRoute(User $user): ?string
    {
        if ($this->emailVerification->isEnabled() && ! $user->hasVerifiedEmail()) {
            return 'verification.notice';
        }

        if ($user->requiresOnboarding()) {
            return 'onboarding.show';
        }

        return null;
    }
}
