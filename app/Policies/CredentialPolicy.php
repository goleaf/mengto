<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CredentialStatus;
use App\Models\Credential;
use App\Models\User;

final class CredentialPolicy
{
    public function view(User $user, Credential $credential): bool
    {
        return $user->isAdministrator() || $this->owns($user, $credential);
    }

    public function viewEvidence(User $user, Credential $credential): bool
    {
        return $this->view($user, $credential);
    }

    public function update(User $user, Credential $credential): bool
    {
        return $this->owns($user, $credential)
            && in_array($credential->effectiveStatus(), [
                CredentialStatus::Submitted,
                CredentialStatus::Rejected,
                CredentialStatus::Expired,
            ], true);
    }

    public function review(User $user, Credential $credential): bool
    {
        return $user->isAdministrator() && ! $this->owns($user, $credential);
    }

    public function appeal(User $user, Credential $credential): bool
    {
        return $this->owns($user, $credential)
            && in_array($credential->effectiveStatus(), [
                CredentialStatus::Rejected,
                CredentialStatus::Suspended,
                CredentialStatus::Revoked,
            ], true);
    }

    private function owns(User $user, Credential $credential): bool
    {
        if (! $credential->relationLoaded('expertProfile')) {
            return $credential->expertProfile()
                ->where(static function ($query) use ($user): void {
                    $query
                        ->where('owner_id', $user->id)
                        ->orWhere('owner_key', $user->actor_key);
                })
                ->exists();
        }

        $profile = $credential->expertProfile;

        return $profile !== null
            && (
                $profile->owner_id === $user->id
                || hash_equals($profile->owner_key, $user->actor_key)
            );
    }
}
