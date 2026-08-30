<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\User;

final readonly class RegisterUserResult
{
    public function __construct(
        public User $user,
        public bool $verificationNotificationDelivered,
    ) {}
}
