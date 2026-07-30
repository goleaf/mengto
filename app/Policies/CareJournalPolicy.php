<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CareJournal;
use App\Models\User;

class CareJournalPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, CareJournal $careJournal): bool
    {
        return $user?->isActive() === true
            && $careJournal->isOwnedBy($user->actor_key);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, CareJournal $careJournal): bool
    {
        return $this->view($user, $careJournal);
    }

    public function share(?User $user, CareJournal $careJournal): bool
    {
        return $this->view($user, $careJournal);
    }

    public function export(?User $user, CareJournal $careJournal): bool
    {
        return $this->view($user, $careJournal);
    }

    public function delete(?User $user, CareJournal $careJournal): bool
    {
        return false;
    }

    public function restore(?User $user, CareJournal $careJournal): bool
    {
        return false;
    }

    public function forceDelete(?User $user, CareJournal $careJournal): bool
    {
        return false;
    }
}
