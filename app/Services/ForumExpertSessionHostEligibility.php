<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CredentialStatus;
use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ForumExpertSessionHostEligibility
{
    public function allows(
        User $user,
        ExpertProfile $profile,
        string $professionalScope,
        string $jurisdiction,
    ): bool {
        if (
            ! $user->isActive()
            || ! $user->hasVerifiedEmail()
            || $profile->owner_id !== $user->id
            || $profile->status !== ExpertProfileStatus::Published
            || ! in_array($profile->verification_status, [
                VerificationStatus::Verified,
                VerificationStatus::Expiring,
            ], true)
            || $profile->verification_expires_at?->isPast() === true
            || ! $this->profileAllowsScope($profile, $professionalScope)
        ) {
            return false;
        }

        $credentials = $profile->relationLoaded('credentials')
            ? $profile->credentials
            : $profile->credentials()
                ->select([
                    'id',
                    'expert_profile_id',
                    'status',
                    'jurisdiction',
                    'scope',
                    'expires_at',
                    'renewal_due_at',
                    'suspended_at',
                    'revoked_at',
                ])
                ->whereIn('status', [
                    CredentialStatus::Verified->value,
                    CredentialStatus::Expiring->value,
                ])
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', today());
                })
                ->get();

        return $credentials->contains(fn (Credential $credential): bool => $this->credentialAllows(
            $credential,
            $professionalScope,
            $jurisdiction,
        ));
    }

    public function hasAnyProfile(User $user): bool
    {
        if (! $user->isActive() || ! $user->hasVerifiedEmail()) {
            return false;
        }

        return $this->candidateProfiles($user)->contains(
            fn (ExpertProfile $profile): bool => $this->allows(
                $user,
                $profile,
                $profile->primary_type,
                $this->defaultJurisdiction($profile),
            ),
        );
    }

    /** @return Collection<int, ExpertProfile> */
    public function candidateProfiles(User $user): Collection
    {
        return ExpertProfile::query()
            ->select([
                'id',
                'owner_id',
                'public_name',
                'primary_type',
                'specializations',
                'country',
                'status',
                'verification_status',
                'verification_expires_at',
            ])
            ->with([
                'credentials' => static fn ($credentials) => $credentials->select([
                    'id',
                    'expert_profile_id',
                    'status',
                    'jurisdiction',
                    'scope',
                    'expires_at',
                    'renewal_due_at',
                    'suspended_at',
                    'revoked_at',
                ]),
            ])
            ->where('owner_id', $user->id)
            ->where('status', ExpertProfileStatus::Published->value)
            ->whereIn('verification_status', [
                VerificationStatus::Verified->value,
                VerificationStatus::Expiring->value,
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('verification_expires_at')
                    ->orWhere('verification_expires_at', '>', now());
            })
            ->orderBy('public_name')
            ->get();
    }

    public function defaultJurisdiction(ExpertProfile $profile): string
    {
        $credential = $profile->relationLoaded('credentials')
            ? $profile->credentials->first(
                fn (Credential $candidate): bool => in_array(
                    $candidate->effectiveStatus(),
                    [CredentialStatus::Verified, CredentialStatus::Expiring],
                    true,
                ) && filled($candidate->jurisdiction),
            )
            : null;

        return strtoupper(trim((string) ($credential?->jurisdiction ?: $profile->country)));
    }

    private function profileAllowsScope(ExpertProfile $profile, string $professionalScope): bool
    {
        $scope = trim($professionalScope);

        return $scope !== ''
            && (
                hash_equals($profile->primary_type, $scope)
                || in_array($scope, $profile->specializations, true)
            );
    }

    private function credentialAllows(
        Credential $credential,
        string $professionalScope,
        string $jurisdiction,
    ): bool {
        if (! in_array(
            $credential->effectiveStatus(),
            [CredentialStatus::Verified, CredentialStatus::Expiring],
            true,
        )) {
            return false;
        }

        $credentialJurisdiction = strtoupper(trim((string) $credential->jurisdiction));
        $requestedJurisdiction = strtoupper(trim($jurisdiction));

        if (
            $credentialJurisdiction === ''
            || $requestedJurisdiction === ''
            || ! in_array($credentialJurisdiction, [$requestedJurisdiction, 'GLOBAL'], true)
        ) {
            return false;
        }

        $scope = is_array($credential->scope) ? $credential->scope : [];

        return in_array($professionalScope, $scope, true);
    }
}
