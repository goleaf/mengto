<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EventCompetition;
use App\Models\User;

final class EventCompetitionPolicy
{
    public function manage(User $user, EventCompetition $competition): bool { return $user->isAdministrator() || $competition->event->isOrganizer($user) || $competition->event->isOwner($user); }
    public function viewResults(?User $user, EventCompetition $competition): bool { return $competition->status->value === 'published' && app(ForumEventPolicy::class)->view($user, $competition->event); }
}
