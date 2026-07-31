<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Enums\VerificationStatus;
use App\Models\Credential;
use App\Models\ExpertProfile;
use Illuminate\Support\Collection;

final class SynchronizeExpertVerificationStatus
{
    public function handle(?ExpertProfile $profile): void
    {
        if ($profile === null) {
            return;
        }

        $statuses = $profile->credentials()
            ->select(['id', 'expert_profile_id', 'type', 'status', 'expires_at', 'renewal_due_at'])
            ->get();
        $effectiveStatuses = $statuses
            ->map(static fn (Credential $credential): CredentialStatus => $credential->effectiveStatus());
        $currentCredentials = $statuses->filter(
            static fn (Credential $credential): bool => in_array(
                $credential->effectiveStatus(),
                [CredentialStatus::Verified, CredentialStatus::Expiring],
                true,
            ),
        );
        $status = match (true) {
            $effectiveStatuses->contains(CredentialStatus::Verified) => VerificationStatus::Verified,
            $effectiveStatuses->contains(CredentialStatus::Expiring) => VerificationStatus::Expiring,
            $effectiveStatuses->contains(CredentialStatus::InReview) => VerificationStatus::InReview,
            $effectiveStatuses->contains(CredentialStatus::Submitted) => VerificationStatus::Submitted,
            $effectiveStatuses->contains(CredentialStatus::Suspended),
            $effectiveStatuses->contains(CredentialStatus::Revoked) => VerificationStatus::Suspended,
            $effectiveStatuses->isEmpty() => VerificationStatus::Unsubmitted,
            default => VerificationStatus::Rejected,
        };

        $profile->forceFill([
            'verification_status' => $status,
            'identity_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => $type->verifiesIdentity(),
            ),
            'education_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => in_array(
                    $type,
                    [CredentialType::Education, CredentialType::Qualification],
                    true,
                ),
            ),
            'qualification_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => in_array(
                    $type,
                    [CredentialType::Qualification, CredentialType::License],
                    true,
                ),
            ),
            'license_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => $type === CredentialType::License,
            ),
            'workplace_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => $type === CredentialType::Workplace,
            ),
            'organization_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => $type->verifiesOrganization(),
            ),
            'contact_verified' => $this->hasCurrentType(
                $currentCredentials,
                static fn (CredentialType $type): bool => $type === CredentialType::Contact,
            ),
            'verification_expires_at' => $currentCredentials->max('expires_at'),
        ])->save();
    }

    /**
     * @param  Collection<int, Credential>  $credentials
     * @param  callable(CredentialType): bool  $matches
     */
    private function hasCurrentType(
        Collection $credentials,
        callable $matches,
    ): bool {
        return $credentials->contains(
            static function (Credential $credential) use ($matches): bool {
                $type = $credential->credentialType();

                return $type !== null && $matches($type);
            },
        );
    }
}
