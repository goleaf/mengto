<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class RecordSuccessfulLogin
{
    public function handle(User $user): void
    {
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
    }
}
