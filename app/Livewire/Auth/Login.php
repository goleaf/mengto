<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\AuthenticateUser;
use App\Livewire\Forms\Auth\LoginForm;
use App\Services\AccountEntryDestination;
use App\Services\SafeIntendedUrl;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class Login extends AuthPage
{
    public LoginForm $form;

    public function authenticate(
        AuthenticateUser $authenticateUser,
        AccountEntryDestination $destination,
        SafeIntendedUrl $intendedUrl,
        AuthManager $auth,
    ): void {
        abort_if($auth->guard('web')->check(), 403);

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

        $pendingRoute = $destination->pendingRoute($user);

        if (is_string($pendingRoute)) {
            $this->redirectRoute($pendingRoute);

            return;
        }

        $this->redirect($intendedUrl->pull(route('home')));
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.login.title')));
    }
}
