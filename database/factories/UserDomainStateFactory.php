<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDomainState;

/**
 * @extends ApplicationFactory<UserDomainState>
 */
final class UserDomainStateFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'namespace' => 'test.'.fake()->unique()->slug(2),
            'version' => 1,
            'payload' => ['enabled' => true],
        ];
    }
}
