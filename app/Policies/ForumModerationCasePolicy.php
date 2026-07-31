<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumModerationCase;
use App\Models\User;

final class ForumModerationCasePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function view(?User $user, ForumModerationCase $case): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function update(?User $user, ForumModerationCase $case): bool
    {
        return $user?->isAdministrator() === true;
    }
}
