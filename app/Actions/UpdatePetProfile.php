<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetSpeciesConfidence;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PetProfileNameHistory;
use App\Services\PrototypeState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfile
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PrototypeState $state,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
        private readonly PetProfileNameHistory $nameHistory,
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

        return DB::transaction(function () use ($data, $eventIdempotencyKey, $profile, $slug, $user): PetProfile {
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

            $attributes = [
                'name' => (string) $data['title'],
                'breed' => ($data['breed'] ?? $data['category'] ?? null) ?: null,
                'lock_version' => $locked->lock_version + 1,
                'profile_data' => $nextProfileData,
            ];

            foreach ([
                'species',
                'species_confidence',
                'taxon_id',
                'domestic_classification_id',
                'birth_date',
                'birth_date_precision',
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

            $locked->forceFill($attributes);

            if ($locked->isDirty('name')) {
                $this->nameHistory->rememberPrevious(
                    $locked,
                    $user,
                    (string) $locked->getOriginal('name'),
                );
            }

            $locked->save();

            // Keep pre-normalization profile snapshots readable during the compatibility window.
            $this->state->updatePet([
                'name' => $locked->name,
                'story' => (string) $data['body'],
                'status' => (string) ($nextProfileData['status'] ?? ''),
                'breed' => $locked->breed ?? '',
            ], $slug);

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
}
