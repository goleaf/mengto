<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\ForumActor;

class MedicalRecordPolicy
{
    public function __construct(private readonly ForumActor $actor) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, MedicalRecord $medicalRecord): bool
    {
        return $medicalRecord->isOwnedBy($this->actor->key());
    }

    public function create(?User $user): bool
    {
        return true;
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
