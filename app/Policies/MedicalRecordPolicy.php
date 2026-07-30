<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $user?->isActive() === true
            && $medicalRecord->isOwnedBy($user->actor_key);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    public function share(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    public function export(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->view($user, $medicalRecord);
    }

    public function delete(?User $user, MedicalRecord $medicalRecord): bool
    {
        return false;
    }

    public function restore(?User $user, MedicalRecord $medicalRecord): bool
    {
        return false;
    }

    public function forceDelete(?User $user, MedicalRecord $medicalRecord): bool
    {
        return false;
    }
}
