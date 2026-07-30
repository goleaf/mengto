<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use App\Services\ForumActor;

class ConsultationPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Consultation $consultation): bool
    {
        return $consultation->booking()
            ->select(['id', 'client_key'])
            ->where('client_key', $this->actor->key())
            ->exists()
            || $consultation->expertProfile()
                ->select(['id', 'owner_key'])
                ->where('owner_key', $this->actor->key())
                ->exists();
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, Consultation $consultation): bool
    {
        return $consultation->expertProfile()
            ->select(['id', 'owner_key'])
            ->where('owner_key', $this->actor->key())
            ->exists();
    }

    public function delete(?User $user, Consultation $consultation): bool
    {
        return false;
    }

    public function restore(?User $user, Consultation $consultation): bool
    {
        return false;
    }

    public function forceDelete(?User $user, Consultation $consultation): bool
    {
        return false;
    }
}
