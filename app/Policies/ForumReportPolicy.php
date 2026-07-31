<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForumReport;
use App\Models\User;

final class ForumReportPolicy
{
    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, ForumReport $report): bool
    {
        return $user?->isActive() === true
            && ($user->isAdministrator() || $report->reporter_id === $user->id);
    }

    public function triage(?User $user): bool
    {
        return $user?->isAdministrator() === true;
    }
}
