<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class ConfirmUserPassword
{
    public function __construct(
        private Hasher $hasher,
        private RateLimiter $limiter,
    ) {}

    public function handle(User $user, string $password, string $ipAddress): void
    {
        $rateLimitKey = Str::transliterate(
            'password-confirm|'.$user->getAuthIdentifier().'|'.$ipAddress,
        );

        if ($this->limiter->tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'form.password' => __('auth.confirm_password.throttled', [
                    'seconds' => $this->limiter->availableIn($rateLimitKey),
                ]),
            ]);
        }

        if (! $this->hasher->check($password, $user->getAuthPassword())) {
            $this->limiter->hit($rateLimitKey, 60);

            throw ValidationException::withMessages([
                'form.password' => __('auth.confirm_password.failed'),
            ]);
        }

        $this->limiter->clear($rateLimitKey);
    }
}
