<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

final class ForumActor
{
    public function __construct(private readonly AuthFactory $auth) {}

    public function key(): string
    {
        $user = $this->user();

        return $user === null ? 'guest' : $user->actor_key;
    }

    public function requireUser(): User
    {
        $user = $this->user();

        if ($user === null) {
            throw new AuthenticationException;
        }

        return $user;
    }

    /**
     * @return array{key: string, name: string, initials: string, role: string}
     */
    public function identity(): array
    {
        $user = $this->user();

        if ($user === null) {
            return [
                'key' => 'guest',
                'name' => __('auth.guest.name'),
                'initials' => __('auth.guest.initials'),
                'role' => __('auth.guest.role'),
            ];
        }

        return [
            'key' => $user->actor_key,
            'name' => $user->name,
            'initials' => $this->initials($user->name),
            'role' => __('auth.member_role'),
        ];
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
