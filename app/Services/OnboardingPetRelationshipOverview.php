<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OnboardingPetChoice;
use App\Enums\PetManagerStatus;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class OnboardingPetRelationshipOverview
{
    private const int PREVIEW_LIMIT = 5;

    public function __construct(
        private OnboardingPetEvidence $evidence,
        private PetSpeciesLabel $speciesLabels,
    ) {}

    /**
     * @return array{
     *     managed: bool,
     *     access_requested: bool,
     *     has_current_invitation: bool,
     *     has_inactive_relationship: bool,
     *     has_more_managed: bool,
     *     managed_pets: list<array{profile_key: string, name: string, species: string}>
     * }
     */
    public function for(User $user): array
    {
        $profiles = PetProfile::query()
            ->select(['id', 'profile_key', 'name', 'species', 'species_confidence'])
            ->managedBy($user)
            ->visibleTo($user)
            ->orderBy('name')
            ->orderBy('id')
            ->limit(self::PREVIEW_LIMIT + 1)
            ->get();

        return [
            'managed' => $this->evidence->supports($user, OnboardingPetChoice::ManagedPet),
            'access_requested' => $this->evidence->supports(
                $user,
                OnboardingPetChoice::AccessRequested,
            ),
            'has_current_invitation' => $this->hasCurrentInvitation($user),
            'has_inactive_relationship' => $this->hasInactiveRelationship($user),
            'has_more_managed' => $profiles->count() > self::PREVIEW_LIMIT,
            'managed_pets' => $profiles
                ->take(self::PREVIEW_LIMIT)
                ->map(fn (PetProfile $profile): array => [
                    'profile_key' => $profile->profile_key,
                    'name' => $profile->name,
                    'species' => $this->speciesLabels->for(
                        $profile->species,
                        $profile->species_confidence,
                    ),
                ])
                ->values()
                ->all(),
        ];
    }

    private function hasCurrentInvitation(User $user): bool
    {
        $at = now();

        return PetProfileManager::query()
            ->whereBelongsTo($user)
            ->whereHas('profile')
            ->where('status', PetManagerStatus::Invited)
            ->whereNull('revoked_at')
            ->where(function (Builder $ends) use ($at): void {
                $ends->whereNull('ends_at')->orWhere('ends_at', '>', $at);
            })
            ->exists();
    }

    private function hasInactiveRelationship(User $user): bool
    {
        $at = now();

        return PetProfileManager::query()
            ->whereBelongsTo($user)
            ->whereHas('profile')
            ->where(function (Builder $inactive) use ($at): void {
                $inactive
                    ->whereNot('status', PetManagerStatus::Active)
                    ->orWhereNotNull('revoked_at')
                    ->orWhere('starts_at', '>', $at)
                    ->orWhere('ends_at', '<=', $at);
            })
            ->exists();
    }
}
