<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\RegisterUserResult;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RegisterUser
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Translator $translator,
        private readonly EmailVerificationMode $emailVerification,
        private readonly InitializeUserOnboarding $initializeOnboarding,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): RegisterUserResult
    {
        $verificationEnabled = $this->emailVerification->isEnabled();
        $email = Str::lower(Str::trim($data['email']));

        try {
            $user = DB::transaction(function () use ($data, $email, $verificationEnabled): User {
                $user = User::query()->forceCreate([
                    'actor_key' => 'user-'.Str::lower((string) Str::ulid()),
                    'name' => Str::trim($data['name']),
                    'email' => $email,
                    'password' => $data['password'],
                    'locale' => $this->defaultLocale(),
                    'timezone' => $this->defaultTimezone(),
                    'status' => UserStatus::Active,
                ]);

                if (! $verificationEnabled) {
                    $user->forceFill(['email_verified_at' => now()])->saveOrFail();
                }

                $this->initializeOnboarding->handle($user);

                return $user;
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! User::query()->where('email', $email)->exists()) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'email' => __('auth.register.unavailable'),
            ]);
        }

        $verificationNotificationDelivered = true;

        event(new Registered($user));

        if ($verificationEnabled) {
            $verificationNotificationDelivered = $user->verificationNotificationWasDelivered();
        }

        return new RegisterUserResult($user, $verificationNotificationDelivered);
    }

    private function defaultLocale(): string
    {
        $configured = $this->config->get('platform.supported_locales', ['en']);
        $supported = is_array($configured)
            ? array_values(array_filter($configured, is_string(...)))
            : ['en'];
        $current = $this->translator->getLocale();

        if (in_array($current, $supported, true)) {
            return $current;
        }

        $fallback = $this->config->get('app.fallback_locale', 'en');

        if (is_string($fallback) && in_array($fallback, $supported, true)) {
            return $fallback;
        }

        return $supported[0] ?? 'en';
    }

    private function defaultTimezone(): string
    {
        $timezone = $this->config->get('app.timezone', 'UTC');

        return is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone
            : 'UTC';
    }
}
