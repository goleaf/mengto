<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetProfileVisibility;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfileFact;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecordPetProfileFact
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
    ) {}

    /**
     * @param  array<string, mixed>  $value
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        PetProfile $profile,
        string $factKey,
        array $value,
        string $precision,
        string $sourceType,
        ?string $sourceReference,
        PetEvidenceStatus $verificationStatus,
        PetProfileVisibility $visibility,
        int $expectedLockVersion,
        string $idempotencyKey,
        array $metadata = [],
    ): PetProfileFact {
        $user = $this->actor->requireUser();
        $this->validateDefinition($factKey, $precision, $sourceType, $value);
        $this->gate->authorize('recordFact', [$profile, $factKey]);

        return DB::transaction(function () use (
            $profile,
            $factKey,
            $value,
            $precision,
            $sourceType,
            $sourceReference,
            $verificationStatus,
            $visibility,
            $expectedLockVersion,
            $idempotencyKey,
            $metadata,
            $user,
        ): PetProfileFact {
            $existingEvent = $profile->lifecycleEvents()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return PetProfileFact::query()->findOrFail(
                    (int) data_get($existingEvent->private_metadata, 'fact_id'),
                );
            }

            $lockedProfile = PetProfile::query()
                ->lockForUpdate()
                ->findOrFail($profile->id);

            if ($lockedProfile->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            $currentKey = "pet:{$lockedProfile->id}:fact:{$factKey}";
            $previous = PetProfileFact::query()
                ->where('current_key', $currentKey)
                ->lockForUpdate()
                ->first();

            if ($previous instanceof PetProfileFact) {
                $previous->forceFill([
                    'is_current' => false,
                    'current_key' => null,
                    'retired_at' => now(),
                ])->save();
            }

            $fact = PetProfileFact::query()->create([
                'pet_profile_id' => $lockedProfile->id,
                'fact_key' => $factKey,
                'value' => $value,
                'normalized_value_hash' => $this->valueHash($value),
                'precision' => $precision,
                'source_type' => $sourceType,
                'source_reference' => $sourceReference,
                'author_user_id' => $user->id,
                'verification_status' => $verificationStatus,
                'visibility' => $visibility,
                'is_current' => true,
                'current_key' => $currentKey,
                'replaces_fact_id' => $previous?->id,
                'recorded_at' => now(),
                'metadata' => $metadata,
            ]);
            $lockedProfile->forceFill([
                'lock_version' => $lockedProfile->lock_version + 1,
            ])->save();
            $manager = $this->access->membership($lockedProfile, $user);
            $this->events->record(
                profile: $lockedProfile,
                actor: $user,
                eventType: 'fact-recorded',
                reasonCode: 'fact-recorded',
                publicMetadata: [
                    'fact_key' => $factKey,
                    'verification_status' => $verificationStatus->value,
                ],
                privateMetadata: [
                    'fact_id' => $fact->id,
                    'replaces_fact_id' => $previous?->id,
                    'source_type' => $sourceType,
                ],
                idempotencyKey: $idempotencyKey,
                manager: $manager,
            );
            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => $manager?->role->value ?? 'legacy-owner',
                'action' => 'pet-profile.fact-recorded',
                'target_type' => PetProfile::class,
                'target_id' => (string) $lockedProfile->id,
                'metadata' => [
                    'fact_key' => $factKey,
                    'fact_id' => $fact->id,
                ],
            ]);
            $this->cache->invalidate($lockedProfile);

            return $fact->refresh();
        }, 3);
    }

    /** @param array<string, mixed> $value */
    private function valueHash(array $value): string
    {
        $normalized = $this->sortRecursively($value);
        $encoded = json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return hash('sha256', $encoded);
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>
     */
    private function sortRecursively(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->sortRecursively($item);
            }
        }

        ksort($value);

        return $value;
    }

    /** @param array<string, mixed> $value */
    private function validateDefinition(
        string $factKey,
        string $precision,
        string $sourceType,
        array $value,
    ): void {
        $messages = [];

        if (! in_array($factKey, config('pet_profiles.versioned_fact_keys', []), true)) {
            $messages['fact_key'] = __('pet_profiles.validation.fact_key');
        }

        if (! in_array($precision, config('pet_profiles.fact_precisions', []), true)) {
            $messages['precision'] = __('pet_profiles.validation.fact_precision');
        }

        if (! in_array($sourceType, config('pet_profiles.fact_source_types', []), true)) {
            $messages['source_type'] = __('pet_profiles.validation.fact_source');
        }

        if ($value === []) {
            $messages['value'] = __('pet_profiles.validation.fact_value');
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }
}
