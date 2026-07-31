<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Str;

final class RegisterUser
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly Translator $translator,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        $user = User::query()->create([
            'actor_key' => 'user-'.Str::lower((string) Str::ulid()),
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => $data['password'],
            'locale' => $this->defaultLocale(),
            'timezone' => $this->defaultTimezone(),
            'status' => UserStatus::Active,
        ]);

        event(new Registered($user));

        return $user;
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
