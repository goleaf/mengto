<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Models\AuditLog;
use App\Models\DomesticClassification;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use App\Models\Taxon;
use App\Services\ForumActor;
use App\Services\PetBirthDetailsNormalizer;
use App\Services\PetBreedOriginNormalizer;
use App\Services\PetBreedOriginSynchronizer;
use App\Services\PetProfileDuplicateReview;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreatePetProfile
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileDuplicateReview $duplicateReview,
        private readonly PetBirthDetailsNormalizer $birthDetails,
        private readonly PetBreedOriginNormalizer $breedOrigins,
        private readonly PetBreedOriginSynchronizer $breedOriginSynchronizer,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): PetProfile
    {
        $user = $this->actor->requireUser();
        $this->gate->authorize('create', PetProfile::class);
        $name = trim((string) ($data['title'] ?? ''));
        $species = (string) ($data['species'] ?? $data['category'] ?? 'unknown');
        $speciesConfidence = PetSpeciesConfidence::normalize(
            $species,
            $data['species_confidence'] ?? null,
        );

        $idempotencyKey = (string) ($data['idempotency_key'] ?? Str::uuid());
        $creationKey = hash('sha256', "pet-create|{$user->id}|{$idempotencyKey}");
        $existing = PetProfile::query()
            ->where('creation_key', $creationKey)
            ->first();

        if ($existing instanceof PetProfile) {
            if ($existing->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'idempotency_key' => __('pet_profiles.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        if (! $this->duplicateReview->hasCompletedReview(
            $user,
            $name,
            $species,
            (string) ($data['duplicate_review_token'] ?? ''),
        )) {
            throw ValidationException::withMessages([
                'duplicate_review' => __('pet_profiles.validation.duplicate_review_required'),
            ]);
        }

        $relationship = PetManagerRole::tryFrom(
            (string) ($data['relationship_role'] ?? PetManagerRole::PrimaryOwner->value),
        ) ?? PetManagerRole::Other;
        $visibility = PetProfileVisibility::tryFrom(
            (string) ($data['visibility'] ?? PetProfileVisibility::Private->value),
        ) ?? PetProfileVisibility::Private;
        $taxon = $this->taxon($data['taxon_id'] ?? null);
        $classification = $this->classification(
            $data['domestic_classification_id'] ?? null,
            $taxon,
        );
        $birthDetails = $this->birthDetails->normalize($data);
        $reportedBreed = trim((string) ($data['breed'] ?? $data['detail'] ?? ''));

        try {
            return DB::transaction(function () use (
                $data,
                $user,
                $creationKey,
                $relationship,
                $visibility,
                $taxon,
                $classification,
                $name,
                $species,
                $speciesConfidence,
                $birthDetails,
                $reportedBreed,
            ): PetProfile {
                $existing = PetProfile::query()
                    ->where('creation_key', $creationKey)
                    ->first();

                if ($existing instanceof PetProfile) {
                    return $existing;
                }

                $profileKey = 'created-pet-'.Str::lower((string) Str::uuid());
                $status = $this->initialStatus($relationship);
                $story = trim((string) ($data['body'] ?? ''));
                $profile = PetProfile::query()->create([
                    'user_id' => $user->id,
                    'profile_key' => $profileKey,
                    'slug' => $this->uniqueSlug($user->id, (string) $data['title'], $profileKey),
                    'name' => $name,
                    'species' => $species,
                    'species_confidence' => $speciesConfidence,
                    'taxon_id' => $taxon?->id,
                    'breed' => $reportedBreed === '' ? null : $reportedBreed,
                    'domestic_classification_id' => $classification?->id,
                    ...$birthDetails,
                    'sex' => (string) ($data['sex'] ?? 'unknown'),
                    'reproductive_status' => (string) ($data['reproductive_status'] ?? 'unknown'),
                    'visibility' => $visibility->value,
                    'status' => $status,
                    'creation_key' => $creationKey,
                    'creator_relationship' => $relationship->value,
                    'is_discoverable' => false,
                    'allow_external_indexing' => false,
                    'lock_version' => 1,
                    'state_entered_at' => now(),
                    'profile_data' => [
                        'story' => $story,
                        'status' => $story,
                    ],
                ]);

                if ($reportedBreed !== '' || $classification instanceof DomesticClassification) {
                    $profile->setRelation('breedOrigins', collect());
                    $normalizedBreedOrigins = $this->breedOrigins->normalize([
                        'taxon_id' => $taxon?->id,
                        'breed_origin_type' => PetBreedOriginType::Single->value,
                        'breed_origins' => [[
                            'origin_key' => null,
                            'domestic_classification_id' => $classification?->id,
                            'name' => $reportedBreed,
                            'confidence' => PetBreedConfidence::OwnerReported->value,
                            'source' => $classification instanceof DomesticClassification
                                ? PetBreedSource::Unknown->value
                                : PetBreedSource::OwnerAssumption->value,
                            'approximate_share_percent' => null,
                        ]],
                    ], $profile);
                    $profile->forceFill([
                        'taxon_id' => $normalizedBreedOrigins['taxon_id'],
                        'breed' => $normalizedBreedOrigins['legacy_snapshot'],
                        'domestic_classification_id' => $normalizedBreedOrigins['domestic_classification_id'],
                        'breed_origin_type' => $normalizedBreedOrigins['type'],
                    ])->save();
                    $this->breedOriginSynchronizer->sync(
                        $profile,
                        $normalizedBreedOrigins['origins'],
                    );
                }
                $manager = PetProfileManager::query()->create([
                    'pet_profile_id' => $profile->id,
                    'user_id' => $user->id,
                    'actor_key_snapshot' => $user->actor_key,
                    'role' => $relationship,
                    'status' => PetManagerStatus::Active,
                    'permission_overrides' => null,
                    'evidence_status' => PetEvidenceStatus::Unverified,
                    'starts_at' => now(),
                    'accepted_at' => now(),
                    'lock_version' => 1,
                    'metadata' => ['source' => 'profile-creation'],
                ]);
                PetProfilePrivacySetting::query()->create([
                    'pet_profile_id' => $profile->id,
                    'profile_visibility' => $visibility,
                    'section_rules' => [],
                    'is_discoverable' => false,
                    'allow_external_indexing' => false,
                    'allow_direct_link' => false,
                    'owner_display_mode' => 'contact-button',
                    'manager_display_mode' => 'hidden',
                    'public_location_precision' => 'hidden',
                    'lock_version' => 1,
                    'updated_by_user_id' => $user->id,
                ]);
                PetProfileSlugAlias::query()->create([
                    'pet_profile_id' => $profile->id,
                    'slug' => $profile->slug,
                    'source' => 'profile-creation',
                    'is_active' => true,
                ]);
                $this->events->record(
                    profile: $profile,
                    actor: $user,
                    eventType: 'profile-created',
                    reasonCode: 'profile-created',
                    toStatus: $status->value,
                    publicMetadata: [
                        'relationship' => $relationship->value,
                        'species' => $profile->species,
                        'species_confidence' => $profile->species_confidence->value,
                    ],
                    idempotencyKey: "pet-create:{$creationKey}",
                    manager: $manager,
                );

                AuditLog::query()->create([
                    'actor_key' => $this->actor->key(),
                    'actor_role' => $relationship->value,
                    'action' => 'pet-profile.created',
                    'target_type' => PetProfile::class,
                    'target_id' => (string) $profile->id,
                    'metadata' => [
                        'profile_key' => $profile->profile_key,
                        'species' => $profile->species,
                        'species_confidence' => $profile->species_confidence->value,
                        'visibility' => $profile->visibility,
                        'status' => $profile->status->value,
                    ],
                ]);

                return $profile;
            }, 3);
        } catch (QueryException $exception) {
            $existing = PetProfile::query()
                ->where('creation_key', $creationKey)
                ->where('user_id', $user->id)
                ->first();

            if ($existing instanceof PetProfile) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function uniqueSlug(int $userId, string $name, string $profileKey): string
    {
        $base = Str::slug($name) ?: 'pet';
        $slug = $base;
        if (PetProfile::query()
            ->where('user_id', $userId)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.substr(hash('sha256', $profileKey), 0, 8);
        }

        return $slug;
    }

    private function initialStatus(PetManagerRole $relationship): PetProfileStatus
    {
        return match ($relationship) {
            PetManagerRole::Shelter,
            PetManagerRole::OrganizationAdministrator => PetProfileStatus::Shelter,
            PetManagerRole::FosterCarer => PetProfileStatus::FosterCare,
            PetManagerRole::Finder => PetProfileStatus::Found,
            PetManagerRole::PrimaryOwner,
            PetManagerRole::CoOwner,
            PetManagerRole::LegalRepresentative,
            PetManagerRole::FamilyMember => PetProfileStatus::Draft,
            default => PetProfileStatus::IdentityUnverified,
        };
    }

    private function taxon(mixed $taxonId): ?Taxon
    {
        if ($taxonId === null || $taxonId === '') {
            return null;
        }

        $taxon = Taxon::query()->active()->find((int) $taxonId);

        if (! $taxon instanceof Taxon) {
            throw ValidationException::withMessages([
                'taxon_id' => __('pet_profiles.validation.taxon_unavailable'),
            ]);
        }

        return $taxon;
    }

    private function classification(
        mixed $classificationId,
        ?Taxon $taxon,
    ): ?DomesticClassification {
        if ($classificationId === null || $classificationId === '') {
            return null;
        }

        $classification = DomesticClassification::query()
            ->where('is_active', true)
            ->find((int) $classificationId);

        if (! $classification instanceof DomesticClassification
            || ($taxon !== null && $classification->taxon_id !== $taxon->id)
        ) {
            throw ValidationException::withMessages([
                'domestic_classification_id' => __('pet_profiles.validation.classification_unavailable'),
            ]);
        }

        return $classification;
    }
}
