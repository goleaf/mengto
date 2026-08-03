<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileCompletionStep;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PrototypeState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfileStep
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PrototypeState $state,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
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
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'taxon_id',
                'breed',
                'domestic_classification_id',
                'birth_date',
                'birth_date_precision',
                'sex',
                'reproductive_status',
                'visibility',
                'status',
                'lock_version',
                'profile_data',
            ])
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
                ->lockForUpdate()
                ->findOrFail($target->id);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            [$attributes, $fields] = $this->changes($locked, $step, $data);
            $attributes['lock_version'] = $locked->lock_version + 1;
            $locked->forceFill($attributes)->save();

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
        if (! in_array($step, [
            PetProfileCompletionStep::Basics,
            PetProfileCompletionStep::AgeAndSex,
            PetProfileCompletionStep::BreedAndOrigin,
            PetProfileCompletionStep::Appearance,
            PetProfileCompletionStep::Character,
            PetProfileCompletionStep::SocialPreferences,
            PetProfileCompletionStep::Location,
        ], true)) {
            throw ValidationException::withMessages([
                'step' => __('pet_profiles.validation.step'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function changes(
        PetProfile $profile,
        PetProfileCompletionStep $step,
        array $data,
    ): array {
        $profileData = $profile->profile_data ?? [];

        return match ($step) {
            PetProfileCompletionStep::Basics => [[
                'name' => trim((string) $data['name']),
                'species' => (string) $data['species'],
            ], ['name', 'species']],
            PetProfileCompletionStep::AgeAndSex => [[
                'birth_date' => $this->nullableString($data['birth_date'] ?? null),
                'birth_date_precision' => (string) $data['birth_date_precision'],
                'sex' => (string) $data['sex'],
                'reproductive_status' => (string) $data['reproductive_status'],
            ], ['birth_date', 'birth_date_precision', 'sex', 'reproductive_status']],
            PetProfileCompletionStep::BreedAndOrigin => [[
                'taxon_id' => isset($data['taxon_id']) ? (int) $data['taxon_id'] : null,
                'breed' => $this->nullableString($data['breed'] ?? null),
            ], ['taxon_id', 'breed']],
            PetProfileCompletionStep::Appearance => [[
                'profile_data' => [
                    ...$profileData,
                    'appearance_summary' => trim((string) ($data['appearance_summary'] ?? '')),
                    'identifying_marks' => trim((string) ($data['identifying_marks'] ?? '')),
                ],
            ], ['appearance_summary', 'identifying_marks']],
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

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim(is_string($value) ? $value : '');

        return $normalized === '' ? null : $normalized;
    }
}
