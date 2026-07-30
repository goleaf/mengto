<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\RegisterUser;
use App\Livewire\Forms\Auth\RegistrationForm;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;

final class Register extends AuthPage
{
    public RegistrationForm $form;

    public function register(RegisterUser $registerUser, AuthManager $auth): void
    {
        $user = $registerUser->handle($this->form->validatedData());

        $auth->guard('web')->login($user);
        Session::regenerate();
        Session::put('locale', $user->locale);

        $this->redirectRoute('verification.notice', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register', [
            'locales' => collect(config('platform.supported_locales'))
                ->mapWithKeys(fn (string $locale): array => [
                    $locale => __('auth.locales.'.$locale),
                ])
                ->all(),
        ])->layout('components.auth-layout', $this->authLayoutData(__('auth.register.title')));
    }
}
