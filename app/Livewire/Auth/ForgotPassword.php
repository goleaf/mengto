<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;

final class ForgotPassword extends AuthPage
{
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        Password::sendResetLink(['email' => mb_strtolower(trim($validated['email']))]);

        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.password.forgot_title')));
    }
}
