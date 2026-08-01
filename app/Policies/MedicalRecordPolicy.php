<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PetProfilePermission;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\PetProfileAccess;

class MedicalRecordPolicy
{
    public function __construct(private readonly PetProfileAccess $petAccess) {}

    public function viewAny(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function view(?User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        if ($medicalRecord->isOwnedBy($user->actor_key)) {
            return true;
        }

        $profile = $medicalRecord->petProfile;

        return $profile !== null
            && $this->petAccess->allows($profile, $user, PetProfilePermission::ViewMedical);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true;
    }

    public function update(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->allowsManagement($user, $medicalRecord);
    }

    public function share(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->allowsManagement($user, $medicalRecord);
    }

    public function export(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $this->allowsManagement($user, $medicalRecord);
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

    private function allowsManagement(?User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        if ($medicalRecord->isOwnedBy($user->actor_key)) {
            return true;
        }

        $profile = $medicalRecord->petProfile;

        return $profile !== null
            && $this->petAccess->allows($profile, $user, PetProfilePermission::ManageMedical);
    }
}
