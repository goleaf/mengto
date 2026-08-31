<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\AuthenticateUser;
use App\Actions\RecordSuccessfulLogin;
use App\Livewire\Forms\Auth\LoginForm;
use App\Services\AccountEntryDestination;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class Login extends AuthPage
{
    public LoginForm $form;

    public function authenticate(
        AuthenticateUser $authenticateUser,
        RecordSuccessfulLogin $recordSuccessfulLogin,
        AccountEntryDestination $destination,
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
        $recordSuccessfulLogin->handle($user);
        Session::put('locale', $user->locale);

        $this->redirect($destination->urlFor($user, route('home')));
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.login.title')));
    }
}
