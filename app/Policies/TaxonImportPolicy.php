<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TaxonImport;
use App\Models\User;

final class TaxonImportPolicy
{
    public function viewAny(?User $user): bool
    {
        return $this->canCurate($user);
    }

    public function view(?User $user, TaxonImport $import): bool
    {
        return $this->canCurate($user);
    }

    public function create(?User $user): bool
    {
        return $this->canCurate($user);
    }

    public function process(?User $user, TaxonImport $import): bool
    {
        return $this->canCurate($user);
    }

    public function activate(?User $user, TaxonImport $import): bool
    {
        return $this->canCurate($user);
    }

    public function cancel(?User $user, TaxonImport $import): bool
    {
        return $this->canCurate($user);
    }

    private function canCurate(?User $user): bool
    {
        return $user?->isAdministrator() === true;
    }
}
