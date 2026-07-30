<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

final class RegisterUser
{
    /**
     * @param  array{name: string, email: string, password: string, locale: string, timezone: string}  $data
     */
    public function handle(array $data): User
    {
        $user = User::query()->create([
            'actor_key' => 'user-'.Str::lower((string) Str::ulid()),
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => $data['password'],
            'locale' => $data['locale'],
            'timezone' => $data['timezone'],
            'status' => UserStatus::Active,
        ]);

        event(new Registered($user));

        return $user;
    }
}
