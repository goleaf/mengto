<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ResetPasswordForm;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

final class ResetPassword extends AuthPage
{
    public ResetPasswordForm $form;

    #[Locked]
    public string $token;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->form->email = (string) request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $validated = $this->form->validate();

        $status = Password::reset(
            [
                'email' => mb_strtolower(trim($validated['email'])),
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $this->token,
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordResetEvent($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'form.email' => __($status),
            ]);
        }

        session()->flash('feedback', __('auth.password.reset_success'));
        $this->redirectRoute('login');
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.password.reset_title')));
    }
}
