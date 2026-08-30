<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OnboardingPetChoice;
use App\Enums\PetProfileAccessRequestStatus;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileManager;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class OnboardingPetEvidence
{
    public function supports(User $user, OnboardingPetChoice $choice): bool
    {
        return match ($choice) {
            OnboardingPetChoice::NotNow => true,
            OnboardingPetChoice::ManagedPet => PetProfile::query()
                ->managedBy($user)
                ->exists(),
            OnboardingPetChoice::AccessRequested => $this->hasCurrentAccessRequestEvidence($user),
        };
    }

    /**
     * Lock the canonical evidence used by the completion transaction so that
     * it cannot be revoked between the final check and the onboarding write.
     */
    public function supportsForCompletion(User $user, OnboardingPetChoice $choice): bool
    {
        $at = now();

        return match ($choice) {
            OnboardingPetChoice::NotNow => true,
            OnboardingPetChoice::ManagedPet => $this->lockManagedPetEvidence($user, $at),
            OnboardingPetChoice::AccessRequested => $this->lockAccessRequestEvidence($user, $at),
        };
    }

    private function hasCurrentAccessRequestEvidence(User $user): bool
    {
        $at = now();

        return PetProfileAccessRequest::query()
            ->whereBelongsTo($user, 'requester')
            ->whereHas('profile')
            ->where(function (Builder $evidence) use ($at): void {
                $evidence
                    ->where(function (Builder $pending) use ($at): void {
                        $pending
                            ->where('status', PetProfileAccessRequestStatus::Pending)
                            ->where(function (Builder $temporary) use ($at): void {
                                $temporary
                                    ->whereNull('temporary_access_ends_at')
                                    ->orWhere('temporary_access_ends_at', '>', $at);
                            });
                    })
                    ->orWhere(function (Builder $approved) use ($at): void {
                        $approved
                            ->where('status', PetProfileAccessRequestStatus::Approved)
                            ->whereHas(
                                'grantedManager',
                                fn (Builder $manager): Builder => PetProfileManager::constrainActiveAt(
                                    $manager->whereColumn(
                                        'pet_profile_managers.user_id',
                                        'pet_profile_access_requests.requester_user_id',
                                    ),
                                    $at,
                                ),
                            );
                    });
            })
            ->exists();
    }

    private function lockManagedPetEvidence(User $user, Carbon $at): bool
    {
        $profile = PetProfile::query()
            ->managedBy($user)
            ->orderBy('id')
            ->lockForUpdate()
            ->first(['id', 'user_id']);

        if (! $profile instanceof PetProfile) {
            return false;
        }

        $manager = PetProfileManager::query()
            ->whereBelongsTo($profile, 'profile')
            ->whereBelongsTo($user)
            ->activeAt($at)
            ->lockForUpdate()
            ->first(['id']);

        if ($manager instanceof PetProfileManager) {
            return true;
        }

        return $profile->user_id === $user->id
            && ! PetProfileManager::query()
                ->whereBelongsTo($profile, 'profile')
                ->whereBelongsTo($user)
                ->exists();
    }

    private function lockAccessRequestEvidence(User $user, Carbon $at): bool
    {
        $pendingCandidate = PetProfileAccessRequest::query()
            ->whereBelongsTo($user, 'requester')
            ->whereHas('profile')
            ->where('status', PetProfileAccessRequestStatus::Pending)
            ->where(function (Builder $temporary) use ($at): void {
                $temporary
                    ->whereNull('temporary_access_ends_at')
                    ->orWhere('temporary_access_ends_at', '>', $at);
            })
            ->orderByDesc('id')
            ->first(['id', 'pet_profile_id']);

        if ($pendingCandidate instanceof PetProfileAccessRequest) {
            $profile = $this->lockCurrentProfile($pendingCandidate->pet_profile_id);

            if ($profile instanceof PetProfile) {
                $pending = PetProfileAccessRequest::query()
                    ->whereKey($pendingCandidate->id)
                    ->whereBelongsTo($user, 'requester')
                    ->where('pet_profile_id', $profile->id)
                    ->where('status', PetProfileAccessRequestStatus::Pending)
                    ->where(function (Builder $temporary) use ($at): void {
                        $temporary
                            ->whereNull('temporary_access_ends_at')
                            ->orWhere('temporary_access_ends_at', '>', $at);
                    })
                    ->lockForUpdate()
                    ->first(['id']);

                if ($pending instanceof PetProfileAccessRequest) {
                    return true;
                }
            }
        }

        $approvedCandidate = PetProfileAccessRequest::query()
            ->whereBelongsTo($user, 'requester')
            ->whereHas('profile')
            ->where('status', PetProfileAccessRequestStatus::Approved)
            ->whereNotNull('granted_manager_id')
            ->whereHas(
                'grantedManager',
                fn (Builder $manager): Builder => PetProfileManager::constrainActiveAt(
                    $manager->whereBelongsTo($user),
                    $at,
                ),
            )
            ->orderByDesc('id')
            ->first(['id', 'pet_profile_id']);

        if (! $approvedCandidate instanceof PetProfileAccessRequest) {
            return false;
        }

        $profile = $this->lockCurrentProfile($approvedCandidate->pet_profile_id);

        if (! $profile instanceof PetProfile) {
            return false;
        }

        $approved = PetProfileAccessRequest::query()
            ->whereKey($approvedCandidate->id)
            ->whereBelongsTo($user, 'requester')
            ->where('pet_profile_id', $profile->id)
            ->where('status', PetProfileAccessRequestStatus::Approved)
            ->whereNotNull('granted_manager_id')
            ->lockForUpdate()
            ->first(['id', 'granted_manager_id']);

        if (! $approved instanceof PetProfileAccessRequest || $approved->granted_manager_id === null) {
            return false;
        }

        return PetProfileManager::query()
            ->whereKey($approved->granted_manager_id)
            ->whereBelongsTo($profile, 'profile')
            ->whereBelongsTo($user)
            ->activeAt($at)
            ->lockForUpdate()
            ->first(['id']) instanceof PetProfileManager;
    }

    private function lockCurrentProfile(int $profileId): ?PetProfile
    {
        return PetProfile::query()
            ->whereKey($profileId)
            ->lockForUpdate()
            ->first(['id', 'user_id']);
    }
}
