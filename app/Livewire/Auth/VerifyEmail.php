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
        $this->sent = false;
        $user = request()->user();

        if ($user instanceof User && ! $user->isActive()) {
            abort(403);
        }

        if (
            ! $emailVerification->isEnabled()
            || ($user instanceof User && $user->hasVerifiedEmail())
        ) {
            $this->redirect(
                $user instanceof User
                    ? $destination->urlFor($user, route('home'))
                    : route('home'),
            );
        }
    }

    public function resend(
        EmailVerificationMode $emailVerification,
        AccountEntryDestination $destination,
        RateLimiter $limiter,
    ): void {
        $this->sent = false;
        $this->resetErrorBag('resend');
        $user = request()->user();

        if ($user instanceof User) {
            $user = User::query()->find($user->getKey());
        }

        if ($user instanceof User && ! $user->isActive()) {
            abort(403);
        }

        if (
            ! $emailVerification->isEnabled()
            || ! $user instanceof User
            || $user->hasVerifiedEmail()
        ) {
            $this->redirect(
                $user instanceof User
                    ? $destination->urlFor($user, route('home'))
                    : route('home'),
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

        if (! $user->verificationNotificationWasDelivered()) {
            $this->addError('resend', __('auth.verification.delivery_failed'));

            return;
        }

        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.verify-email')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.verification.title')));
    }
}
