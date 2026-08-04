<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileCompletionStep;
use App\Enums\PetSpeciesConfidence;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetAppearanceNormalizer;
use App\Services\PetBirthDetailsNormalizer;
use App\Services\PetBreedOriginNormalizer;
use App\Services\PetBreedOriginSynchronizer;
use App\Services\PetLifeStageOverrideNormalizer;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PetProfileNameHistory;
use App\Services\PrototypeState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfileStep
{
    /** @var list<string> */
    private const PROFILE_COLUMNS = [
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
    ];

    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PrototypeState $state,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
        private readonly PetProfileNameHistory $nameHistory,
        private readonly PetAppearanceNormalizer $appearance,
        private readonly PetLifeStageOverrideNormalizer $lifeStageOverrides,
        private readonly PetBirthDetailsNormalizer $birthDetails,
        private readonly PetBreedOriginNormalizer $breedOrigins,
        private readonly PetBreedOriginSynchronizer $breedOriginSynchronizer,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(
        PetProfile $profile,
        PetProfileCompletionStep $step,
        array $data,
        int $expectedLockVersion,
        string $idempotencyKey,
    ): PetProfile {
        $user = $this->actor->requireUser();
        $target = PetProfile::query()
            ->select(self::PROFILE_COLUMNS)
            ->managedBy($user)
            ->find($profile->id);

        if (! $target instanceof PetProfile) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('update', $target);
        $this->guardStep($step);
        $eventIdempotencyKey = 'pet-step-update:'.hash('sha256', $idempotencyKey);

        return DB::transaction(function () use (
            $data,
            $eventIdempotencyKey,
            $expectedLockVersion,
            $step,
            $target,
            $user,
        ): PetProfile {
            if ($target->lifecycleEvents()
                ->where('idempotency_key', $eventIdempotencyKey)
                ->exists()) {
                return PetProfile::query()->findOrFail($target->id);
            }

            $locked = PetProfile::query()
                ->select(self::PROFILE_COLUMNS)
                ->lockForUpdate()
                ->findOrFail($target->id);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            $normalizedBreedOrigins = null;

            if ($step === PetProfileCompletionStep::BreedAndOrigin) {
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
                $normalizedBreedOrigins = $this->breedOrigins->normalize($data, $locked);
            }

            [$attributes, $fields] = $this->changes(
                $locked,
                $step,
                $data,
                $normalizedBreedOrigins,
                $user->id,
            );
            $locked->forceFill($attributes);
            $breedOriginsChanged = $normalizedBreedOrigins !== null
                && $this->breedOriginSynchronizer->differs(
                    $locked,
                    $normalizedBreedOrigins['origins'],
                );

            if (! $locked->isDirty(array_keys($attributes)) && ! $breedOriginsChanged) {
                return $locked->refresh();
            }

            if ($locked->isDirty('name')) {
                $this->nameHistory->rememberPrevious(
                    $locked,
                    $user,
                    (string) $locked->getOriginal('name'),
                );
            }

            $locked->lock_version++;
            $locked->save();

            if ($normalizedBreedOrigins !== null && $breedOriginsChanged) {
                $this->breedOriginSynchronizer->sync(
                    $locked,
                    $normalizedBreedOrigins['origins'],
                );
            }

            $profileData = $locked->profile_data ?? [];
            $this->state->updatePet([
                'name' => $locked->name,
                'story' => is_string($profileData['story'] ?? null) ? $profileData['story'] : '',
                'status' => is_string($profileData['status'] ?? null) ? $profileData['status'] : '',
                'breed' => $locked->breed ?? '',
            ], $locked->slug);

            $manager = $this->access->membership($locked, $user);
            $this->events->record(
                profile: $locked,
                actor: $user,
                eventType: 'profile-step-updated',
                reasonCode: 'profile-step-updated',
                publicMetadata: [
                    'step' => $step->value,
                    'fields' => $fields,
                ],
                idempotencyKey: $eventIdempotencyKey,
                manager: $manager,
            );
            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => $manager?->role->value ?? 'legacy-owner',
                'action' => 'pet-profile.step-updated',
                'target_type' => PetProfile::class,
                'target_id' => (string) $locked->id,
                'metadata' => [
                    'profile_key' => $locked->profile_key,
                    'step' => $step->value,
                    'fields' => $fields,
                ],
            ]);
            $this->cache->invalidate($locked);

            return $locked->refresh();
        }, 3);
    }

    private function guardStep(PetProfileCompletionStep $step): void
    {
        if (! $step->supportsAutosave()) {
            throw ValidationException::withMessages([
                'step' => __('pet_profiles.validation.step'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>|null  $normalizedBreedOrigins
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function changes(
        PetProfile $profile,
        PetProfileCompletionStep $step,
        array $data,
        ?array $normalizedBreedOrigins,
        int $actorId,
    ): array {
        $profileData = $profile->profile_data ?? [];

        return match ($step) {
            PetProfileCompletionStep::Basics => [[
                'name' => trim((string) $data['name']),
                'species' => (string) $data['species'],
                'species_confidence' => PetSpeciesConfidence::normalize(
                    (string) $data['species'],
                    $data['species_confidence'] ?? null,
                ),
            ], ['name', 'species', 'species_confidence']],
            PetProfileCompletionStep::AgeAndSex => [[
                ...$this->birthDetails->normalize($data, $profile),
                ...(array_key_exists('life_stage_override', $data)
                    ? $this->lifeStageOverrides->attributes(
                        $profile,
                        $data['life_stage_override'],
                        $actorId,
                    )
                    : []),
                'sex' => (string) $data['sex'],
                'reproductive_status' => (string) $data['reproductive_status'],
            ], [
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                ...(array_key_exists('life_stage_override', $data) ? [
                    'life_stage_override',
                    'life_stage_override_by_user_id',
                    'life_stage_override_at',
                ] : []),
                'sex',
                'reproductive_status',
            ]],
            PetProfileCompletionStep::BreedAndOrigin => [[
                'taxon_id' => $normalizedBreedOrigins['taxon_id'] ?? null,
                'breed' => $normalizedBreedOrigins['legacy_snapshot'] ?? null,
                'domestic_classification_id' => $normalizedBreedOrigins['domestic_classification_id'] ?? null,
                'breed_origin_type' => $normalizedBreedOrigins['type'] ?? null,
            ], [
                'taxon_id',
                'breed',
                'domestic_classification_id',
                'breed_origin_type',
                'breed_origins',
            ]],
            PetProfileCompletionStep::Appearance => [[
                'profile_data' => $this->appearance->apply($data, $profileData),
            ], [
                'primary_color',
                'additional_colors',
                'patterns',
                'color_details',
                'feather_color_details',
                'scale_color_details',
                'seasonal_color_changes',
                'appearance_summary',
                'identifying_marks',
            ]],
            PetProfileCompletionStep::Character => [[
                'profile_data' => [
                    ...$profileData,
                    'story' => trim((string) ($data['story'] ?? '')),
                    'temperament_summary' => trim((string) ($data['temperament_summary'] ?? '')),
                ],
            ], ['story', 'temperament_summary']],
            PetProfileCompletionStep::SocialPreferences => [[
                'profile_data' => [
                    ...$profileData,
                    'social_preferences' => trim((string) ($data['social_preferences'] ?? '')),
                    'meeting_preferences' => trim((string) ($data['meeting_preferences'] ?? '')),
                ],
            ], ['social_preferences', 'meeting_preferences']],
            PetProfileCompletionStep::Location => [[
                'profile_data' => [
                    ...$profileData,
                    'location_label' => trim((string) ($data['location_label'] ?? '')),
                    'location_precision' => (string) $data['location_precision'],
                ],
            ], ['location_label', 'location_precision']],
            default => throw ValidationException::withMessages([
                'step' => __('pet_profiles.validation.step'),
            ]),
        };
    }
}
