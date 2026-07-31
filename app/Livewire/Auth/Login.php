<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\AuthenticateUser;
use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class Login extends AuthPage
{
    public LoginForm $form;

    public function authenticate(AuthenticateUser $authenticateUser): void
    {
        try {
            $this->form->validate();

            $user = $authenticateUser->handle(
                $this->form->email,
                $this->form->password,
                $this->form->remember,
                request()->ip() ?? 'unknown',
            );
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());
            $this->dispatch('auth-validation-failed');

            return;
        }

        Session::regenerate();
        Session::put('locale', $user->locale);

        $this->redirectIntended(default: route('home'));
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.login.title')));
    }
}
