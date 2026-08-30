<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\RegisterUser;
use App\Livewire\Forms\Auth\RegistrationForm;
use App\Services\AccountEntryDestination;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;

final class Register extends AuthPage
{
    public RegistrationForm $form;

    public function register(
        RegisterUser $registerUser,
        AuthManager $auth,
        AccountEntryDestination $destination,
        RateLimiter $limiter,
    ): void {
        abort_if($auth->guard('web')->check(), 403);
        $rateLimitKey = 'registration|'.(request()->ip() ?? 'unknown');

        if ($limiter->tooManyAttempts($rateLimitKey, 5)) {
            $this->addError('form.email', __('auth.register.throttled', [
                'seconds' => $limiter->availableIn($rateLimitKey),
            ]));
            $this->dispatch('auth-validation-failed');

            return;
        }

        $limiter->hit($rateLimitKey, 60);
        $user = $registerUser->handle($this->form->validatedData());

        $auth->guard('web')->login($user);
        Session::regenerate();
        Session::put('locale', $user->locale);

        $this->redirectRoute($destination->pendingRoute($user) ?? 'home');
    }

    public function render(): View
    {
        return view('livewire.auth.register')
            ->layout('components.auth-layout', $this->authLayoutData(__('auth.register.title')));
    }
}
