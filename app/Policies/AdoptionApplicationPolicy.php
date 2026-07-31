<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdoptionApplication;
use App\Models\Listing;
use App\Models\User;

final class AdoptionApplicationPolicy
{
    public function view(User $user, AdoptionApplication $application): bool
    {
        return $user->isActive()
            && (
                $user->isAdministrator()
                || $application->applicant_user_id === $user->id
                || $this->ownerKey($application) === $user->actor_key
            );
    }

    public function transition(User $user, AdoptionApplication $application): bool
    {
        return $this->view($user, $application);
    }

    private function ownerKey(AdoptionApplication $application): string
    {
        return Listing::query()
            ->whereHas(
                'adoptionCase',
                fn ($query) => $query->whereKey($application->adoption_case_id),
            )
            ->value('owner_key') ?? '';
    }
}
