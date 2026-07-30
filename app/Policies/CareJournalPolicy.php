<?php

namespace App\Policies;

use App\Models\CareJournal;
use App\Models\User;
use App\Services\ForumActor;

class CareJournalPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, CareJournal $careJournal): bool
    {
        return $careJournal->isOwnedBy($this->actor->key());
    }

    public function create(?User $user): bool
    {
        return true;
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
