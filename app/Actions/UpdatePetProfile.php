<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Enums\PetSpeciesConfidence;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetBirthDetailsNormalizer;
use App\Services\PetBreedOriginNormalizer;
use App\Services\PetBreedOriginSynchronizer;
use App\Services\PetLifeStageOverrideNormalizer;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PetProfileNameHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfile
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
        private readonly PetProfileNameHistory $nameHistory,
        private readonly PetLifeStageOverrideNormalizer $lifeStageOverrides,
        private readonly PetBirthDetailsNormalizer $birthDetails,
        private readonly PetBreedOriginNormalizer $breedOrigins,
        private readonly PetBreedOriginSynchronizer $breedOriginSynchronizer,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(string $slug, array $data): PetProfile
    {
        $user = $this->actor->requireUser();
        $profile = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'species_confidence',
                'taxon_id',
                'breed',
                'domestic_classification_id',
                'breed_origin_type',
                'size_category',
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                'life_stage_override',
                'life_stage_override_by_user_id',
                'life_stage_override_at',
                'sex',
                'reproductive_status',
                'visibility',
                'status',
                'lock_version',
                'profile_data',
            ])
            ->managedBy($user)
            ->where('slug', $slug)
            ->first();

        if ($profile === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('update', $profile);
        $eventIdempotencyKey = isset($data['idempotency_key'])
            ? 'pet-update:'.hash('sha256', (string) $data['idempotency_key'])
            : null;

        return DB::transaction(function () use ($data, $eventIdempotencyKey, $profile, $user): PetProfile {
            if ($eventIdempotencyKey !== null && $profile->lifecycleEvents()
                ->where('idempotency_key', $eventIdempotencyKey)
                ->exists()) {
                return PetProfile::query()->findOrFail($profile->id);
            }

            $locked = PetProfile::query()
                ->lockForUpdate()
                ->findOrFail($profile->id);
            $expectedVersion = (int) ($data['lock_version'] ?? $locked->lock_version);

            if ($locked->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            $profileData = $locked->profile_data ?? [];
            $nextProfileData = [
                ...$profileData,
                'story' => (string) $data['body'],
            ];

            if (array_key_exists('detail', $data)) {
                $nextProfileData['status'] = (string) $data['detail'];
            }

            $incomingBreed = trim((string) ($data['breed'] ?? $data['category'] ?? ''));
            $normalizedBreedOrigins = null;

            if ($incomingBreed !== (string) $locked->breed
                || $locked->breed_origin_type === null) {
                $locked->load(['breedOrigins' => fn ($query) => $query->select([
                    'id',
                    'origin_key',
                    'pet_profile_id',
                    'domestic_classification_id',
                    'breed_name',
                    'confidence',
                    'source',
                    'approximate_share_percent',
                    'position',
                ])]);
                $normalizedBreedOrigins = $this->breedOrigins->normalize([
                    'taxon_id' => $data['taxon_id'] ?? $locked->taxon_id,
                    'breed_origin_type' => $incomingBreed === ''
                        ? PetBreedOriginType::Unknown->value
                        : PetBreedOriginType::Single->value,
                    'breed_origins' => $incomingBreed === '' ? [] : [[
                        'origin_key' => null,
                        'domestic_classification_id' => null,
                        'name' => $incomingBreed,
                        'confidence' => PetBreedConfidence::OwnerReported->value,
                        'source' => PetBreedSource::OwnerAssumption->value,
                        'approximate_share_percent' => null,
                    ]],
                ], $locked);
            }

            $attributes = [
                'name' => (string) $data['title'],
                'breed' => $normalizedBreedOrigins['legacy_snapshot'] ?? ($incomingBreed === '' ? null : $incomingBreed),
                'lock_version' => $locked->lock_version + 1,
                'profile_data' => $nextProfileData,
            ];

            if ($normalizedBreedOrigins !== null) {
                $attributes = [
                    ...$attributes,
                    'taxon_id' => $normalizedBreedOrigins['taxon_id'],
                    'domestic_classification_id' => $normalizedBreedOrigins['domestic_classification_id'],
                    'breed_origin_type' => $normalizedBreedOrigins['type'],
                ];
            }

            foreach ([
                'species',
                'species_confidence',
                'taxon_id',
                'domestic_classification_id',
                'sex',
                'reproductive_status',
            ] as $optionalField) {
                if (array_key_exists($optionalField, $data)) {
                    $attributes[$optionalField] = $data[$optionalField] ?: null;
                }
            }

            $nextSpecies = (string) ($attributes['species'] ?? $locked->species);
            $attributes['species_confidence'] = PetSpeciesConfidence::normalize(
                $nextSpecies,
                $attributes['species_confidence'] ?? $locked->species_confidence,
            );

            if ($this->hasBirthDetails($data)) {
                $attributes = [
                    ...$attributes,
                    ...$this->birthDetails->normalize($data, $locked),
                ];
            }

            if (array_key_exists('life_stage_override', $data)) {
                $attributes = [
                    ...$attributes,
                    ...$this->lifeStageOverrides->attributes(
                        $locked,
                        $data['life_stage_override'],
                        $user->id,
                    ),
                ];
            }

            $locked->forceFill($attributes);

            if ($locked->isDirty('name')) {
                $this->nameHistory->rememberPrevious(
                    $locked,
                    $user,
                    (string) $locked->getOriginal('name'),
                );
            }

            $locked->save();

            if ($normalizedBreedOrigins !== null
                && $this->breedOriginSynchronizer->differs(
                    $locked,
                    $normalizedBreedOrigins['origins'],
                )) {
                $this->breedOriginSynchronizer->sync(
                    $locked,
                    $normalizedBreedOrigins['origins'],
                );
            }

            $manager = $this->access->membership($locked, $user);
            $this->events->record(
                profile: $locked,
                actor: $user,
                eventType: 'profile-updated',
                reasonCode: 'profile-updated',
                publicMetadata: ['fields' => ['name', 'breed', 'story']],
                idempotencyKey: $eventIdempotencyKey,
                manager: $manager,
            );

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => $manager?->role->value ?? 'legacy-owner',
                'action' => 'pet-profile.updated',
                'target_type' => PetProfile::class,
                'target_id' => (string) $locked->id,
                'metadata' => ['profile_key' => $locked->profile_key],
            ]);
            $this->cache->invalidate($locked);

            return $locked->refresh();
        }, 3);
    }

    /** @param array<string, mixed> $data */
    private function hasBirthDetails(array $data): bool
    {
        foreach ([
            'birth_date',
            'birth_date_precision',
            'birth_month',
            'birth_year',
            'estimated_age_years',
            'estimated_age_month_remainder',
            'estimated_age_months',
            'birthday_celebration_month',
            'birthday_celebration_day',
        ] as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }
}
