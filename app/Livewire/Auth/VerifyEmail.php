<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;

final class VerifyEmail extends AuthPage
{
    public bool $sent = false;

    public function resend(): void
    {
        $user = request()->user();

        if (! $user instanceof User || $user->hasVerifiedEmail()) {
            $this->redirectRoute('home');

            return;
        }

        $user->sendEmailVerificationNotification();
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.verify-email')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.verification.title')));
    }
}
