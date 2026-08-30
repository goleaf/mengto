<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\AccountEntryDestination;
use App\Services\EmailVerificationMode;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\View\View;

final class VerifyEmail extends AuthPage
{
    public bool $sent = false;

    public function mount(
        EmailVerificationMode $emailVerification,
        AccountEntryDestination $destination,
    ): void {
        $user = request()->user();

        if (
            ! $emailVerification->isEnabled()
            || ($user instanceof User && $user->hasVerifiedEmail())
        ) {
            $this->redirectRoute(
                $user instanceof User ? ($destination->pendingRoute($user) ?? 'home') : 'home',
            );
        }
    }

    public function resend(
        EmailVerificationMode $emailVerification,
        AccountEntryDestination $destination,
        RateLimiter $limiter,
    ): void {
        $user = request()->user();

        if (
            ! $emailVerification->isEnabled()
            || ! $user instanceof User
            || $user->hasVerifiedEmail()
        ) {
            $this->redirectRoute(
                $user instanceof User ? ($destination->pendingRoute($user) ?? 'home') : 'home',
            );

            return;
        }

        $rateLimitKey = 'verification-resend|'.$user->id.'|'.(request()->ip() ?? 'unknown');

        if ($limiter->tooManyAttempts($rateLimitKey, 3)) {
            $this->addError('resend', __('auth.verification.throttled', [
                'seconds' => $limiter->availableIn($rateLimitKey),
            ]));

            return;
        }

        $limiter->hit($rateLimitKey, 60);
        $user->sendEmailVerificationNotification();
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.verify-email')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.verification.title')));
    }
}
