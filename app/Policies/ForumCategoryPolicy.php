<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumCategory;
use App\Models\User;

final class ForumCategoryPolicy
{
    public function manage(?User $user, ForumCategory $category): bool
    {
        return $user?->isAdministrator() === true;
    }
}
