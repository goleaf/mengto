<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ModerationStatus;
use App\Models\SearchCase;
use App\Models\User;

class SearchCasePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, SearchCase $searchCase): bool
    {
        return ($user?->isActive() === true && $searchCase->isManagedBy($user->actor_key))
            || ($searchCase->moderation_status === ModerationStatus::Approved
                && in_array($searchCase->visibility, ['public', 'link'], true));
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, SearchCase $searchCase): bool
    {
        return $user?->isActive() === true
            && $searchCase->isManagedBy($user->actor_key);
    }

    public function coordinate(?User $user, SearchCase $searchCase): bool
    {
        return $this->update($user, $searchCase);
    }

    public function submitSighting(?User $user, SearchCase $searchCase): bool
    {
        return $searchCase->moderation_status === ModerationStatus::Approved
            && $searchCase->alerts_active
            && ! $searchCase->status->isClosed();
    }

    public function volunteer(?User $user, SearchCase $searchCase): bool
    {
        return $searchCase->volunteer_join_open
            && $searchCase->alerts_active
            && ! $searchCase->status->isClosed();
    }

    public function report(?User $user, SearchCase $searchCase): bool
    {
        return $this->view($user, $searchCase);
    }

    public function viewPoster(?User $user, SearchCase $searchCase): bool
    {
        return $this->view($user, $searchCase);
    }

    public function delete(?User $user, SearchCase $searchCase): bool
    {
        return false;
    }

    public function restore(?User $user, SearchCase $searchCase): bool
    {
        return false;
    }

    public function forceDelete(?User $user, SearchCase $searchCase): bool
    {
        return false;
    }
}
