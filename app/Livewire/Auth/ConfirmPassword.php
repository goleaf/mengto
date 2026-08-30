<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\ConfirmUserPassword;
use App\Livewire\Forms\Auth\ConfirmPasswordForm;
use App\Models\User;
use App\Services\AccountEntryDestination;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class ConfirmPassword extends AuthPage
{
    public ConfirmPasswordForm $form;

    public function confirm(
        ConfirmUserPassword $confirmUserPassword,
        AccountEntryDestination $destination,
    ): void {
        $this->form->validate();

        $user = auth()->user();

        if (! $user instanceof User || ! $user->isActive()) {
            $this->redirectRoute('login');

            return;
        }

        try {
            $confirmUserPassword->handle(
                $user,
                $this->form->password,
                request()->ip() ?? 'unknown',
            );
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());
            $this->dispatch('auth-validation-failed');

            return;
        }

        Session::passwordConfirmed();

        $this->redirect($destination->urlFor($user, route('home')));
    }

    public function render(): View
    {
        return view('livewire.auth.confirm-password')
            ->layout(
                'components.auth-layout',
                $this->authLayoutData(__('auth.confirm_password.title')),
            );
    }
}
