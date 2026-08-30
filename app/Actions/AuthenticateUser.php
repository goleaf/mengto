<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

final readonly class AuthenticateUser
{
    public function __construct(
        private AuthManager $auth,
        private RateLimiter $limiter,
    ) {}

    public function handle(
        string $email,
        string $password,
        bool $remember,
        string $ipAddress,
    ): User {
        $normalizedEmail = mb_strtolower(trim($email));
        $rateLimitKey = Str::transliterate($normalizedEmail.'|'.$ipAddress);

        if ($this->limiter->tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'form.email' => __('auth.login.throttled', [
                    'seconds' => $this->limiter->availableIn($rateLimitKey),
                ]),
            ]);
        }

        $authenticated = $this->auth->guard('web')->attempt([
            'email' => $normalizedEmail,
            'password' => $password,
            'status' => UserStatus::Active->value,
        ], $remember);

        if (! $authenticated) {
            $this->limiter->hit($rateLimitKey, 60);

            throw ValidationException::withMessages([
                'form.email' => __('auth.login.failed'),
            ]);
        }

        $this->limiter->clear($rateLimitKey);

        $user = $this->auth->guard('web')->user();

        if (! $user instanceof User) {
            throw new LogicException(__('messages.authenticated_principal_is_not_a_brand_user'));
        }

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return $user;
    }
}
