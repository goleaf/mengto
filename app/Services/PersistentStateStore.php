<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserDomainState;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PersistentStateStore
{
    /**
     * @var array<string, array{id: int|null, payload: array<string, mixed>, version: int}>
     */
    private array $loaded = [];

    public function __construct(private readonly AuthFactory $auth) {}

    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function get(string $namespace, array $defaults = []): array
    {
        $user = $this->user();

        if ($user === null) {
            return $defaults;
        }

        $cacheKey = $this->cacheKey($user, $namespace);

        if (! isset($this->loaded[$cacheKey])) {
            $state = UserDomainState::query()
                ->select(['id', 'user_id', 'namespace', 'version', 'payload'])
                ->where('user_id', $user->id)
                ->where('namespace', $namespace)
                ->first();

            $this->loaded[$cacheKey] = [
                'id' => $state?->id,
                'payload' => $state->payload ?? [],
                'version' => $state->version ?? 0,
            ];
        }

        return [
            ...$defaults,
            ...$this->loaded[$cacheKey]['payload'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function put(string $namespace, array $payload): void
    {
        $user = $this->user() ?? throw new AuthenticationException;
        $cacheKey = $this->cacheKey($user, $namespace);
        $expectedVersion = $this->loaded[$cacheKey]['version'] ?? 0;

        $state = DB::transaction(function () use (
            $user,
            $namespace,
            $payload,
            $expectedVersion,
        ): UserDomainState {
            $state = UserDomainState::query()
                ->where('user_id', $user->id)
                ->where('namespace', $namespace)
                ->lockForUpdate()
                ->first();

            if ($state !== null && $state->version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'state' => __('messages.social_state_changed_reload'),
                ]);
            }

            if ($state === null && $expectedVersion !== 0) {
                throw ValidationException::withMessages([
                    'state' => __('messages.social_state_changed_reload'),
                ]);
            }

            $state ??= new UserDomainState([
                'user_id' => $user->id,
                'namespace' => $namespace,
            ]);
            $state->forceFill([
                'payload' => $payload,
                'version' => $expectedVersion + 1,
            ])->save();

            return $state;
        }, 3);

        $this->loaded[$cacheKey] = [
            'id' => $state->id,
            'payload' => $payload,
            'version' => $state->version,
        ];
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    private function cacheKey(User $user, string $namespace): string
    {
        return $user->id.'|'.$namespace;
    }
}
