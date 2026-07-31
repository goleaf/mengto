<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AdoptionProviderIdentityStatus;
use App\Enums\AdoptionProviderType;
use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SynchronizeAdoptionProviderIdentity
{
    public function handle(AdoptionCase $case): AdoptionCase
    {
        return DB::transaction(function () use ($case): AdoptionCase {
            $lockedCase = AdoptionCase::query()
                ->lockForUpdate()
                ->findOrFail($case->id);
            $listing = Listing::query()
                ->select(['id', 'owner_id', 'owner_key'])
                ->findOrFail($lockedCase->listing_id);
            $credential = $this->credentialFor($lockedCase->provider_type, $listing);
            $status = $this->statusFor($credential);
            $previousStatus = $lockedCase->provider_identity_status;

            $lockedCase->forceFill([
                'provider_expert_profile_id' => $credential?->expert_profile_id,
                'provider_credential_id' => $credential?->id,
                'provider_identity_status' => $status,
                'provider_verified' => $status->isVerified(),
                'provider_verified_at' => $status->isVerified() ? $credential?->verified_at : null,
                'provider_verification_expires_at' => $credential?->expires_at,
            ]);

            if (! $lockedCase->isDirty()) {
                return $lockedCase;
            }

            $lockedCase->save();

            AdoptionEvent::query()->create([
                'adoption_case_id' => $lockedCase->id,
                'event_type' => 'provider-identity-status-changed',
                'previous_status' => $previousStatus->value,
                'current_status' => $status->value,
                'reason_translation_key' => 'adoption.events.provider_identity_changed',
                'metadata' => array_filter([
                    'expert_profile_id' => $credential?->expert_profile_id,
                    'credential_id' => $credential?->id,
                ]),
            ]);

            return $lockedCase->refresh();
        }, 3);
    }

    public function handleCredential(Credential $credential): void
    {
        $profile = ExpertProfile::query()
            ->select(['id', 'owner_id', 'owner_key'])
            ->find($credential->expert_profile_id);

        if ($profile === null) {
            return;
        }

        AdoptionCase::query()
            ->select(['adoption_cases.id'])
            ->whereHas('listing', function (Builder $query) use ($profile): void {
                $query->where(function (Builder $owner) use ($profile): void {
                    if ($profile->owner_id !== null) {
                        $owner->where('owner_id', $profile->owner_id)
                            ->orWhere('owner_key', $profile->owner_key);

                        return;
                    }

                    $owner->where('owner_key', $profile->owner_key);
                });
            })
            ->chunkById(100, function (Collection $cases): void {
                $cases->each(fn (AdoptionCase $case): AdoptionCase => $this->handle($case));
            });
    }

    private function credentialFor(
        AdoptionProviderType $providerType,
        Listing $listing,
    ): ?Credential {
        $types = collect(CredentialType::cases())
            ->filter(static fn (CredentialType $type): bool => match ($providerType) {
                AdoptionProviderType::PrivatePerson => $type->verifiesIdentity(),
                AdoptionProviderType::Organization => $type->verifiesOrganization(),
            })
            ->map(static fn (CredentialType $type): string => $type->value)
            ->all();

        return Credential::query()
            ->select([
                'id',
                'expert_profile_id',
                'type',
                'status',
                'verified_at',
                'expires_at',
                'renewal_due_at',
                'updated_at',
            ])
            ->whereIn('type', $types)
            ->whereHas('expertProfile', function (Builder $query) use ($listing): void {
                $query->where(function (Builder $owner) use ($listing): void {
                    if ($listing->owner_id !== null) {
                        $owner->where('owner_id', $listing->owner_id)
                            ->orWhere('owner_key', $listing->owner_key);

                        return;
                    }

                    $owner->where('owner_key', $listing->owner_key);
                });
            })
            ->with('expertProfile:id,owner_id,owner_key')
            ->latest('updated_at')
            ->latest('id')
            ->limit(100)
            ->get()
            ->sortBy(fn (Credential $credential): int => $this->statusPriority($credential))
            ->first();
    }

    private function statusFor(?Credential $credential): AdoptionProviderIdentityStatus
    {
        if ($credential === null) {
            return AdoptionProviderIdentityStatus::Unverified;
        }

        return match ($credential->effectiveStatus()) {
            CredentialStatus::Submitted,
            CredentialStatus::InReview => AdoptionProviderIdentityStatus::Pending,
            CredentialStatus::Verified,
            CredentialStatus::Expiring => AdoptionProviderIdentityStatus::Verified,
            CredentialStatus::Expired => AdoptionProviderIdentityStatus::Expired,
            CredentialStatus::Suspended => AdoptionProviderIdentityStatus::Suspended,
            CredentialStatus::Rejected => AdoptionProviderIdentityStatus::Rejected,
            CredentialStatus::Revoked => AdoptionProviderIdentityStatus::Revoked,
        };
    }

    private function statusPriority(Credential $credential): int
    {
        return match ($credential->effectiveStatus()) {
            CredentialStatus::Verified => 0,
            CredentialStatus::Expiring => 1,
            CredentialStatus::InReview => 2,
            CredentialStatus::Submitted => 3,
            CredentialStatus::Expired => 4,
            CredentialStatus::Suspended => 5,
            CredentialStatus::Rejected => 6,
            CredentialStatus::Revoked => 7,
        };
    }
}
