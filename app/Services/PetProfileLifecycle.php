<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetProfileStatus;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PetProfileLifecycle
{
    public function __construct(
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
    ) {}

    /** @param array<string, mixed> $privateMetadata */
    public function transition(
        PetProfile $profile,
        PetProfileStatus $target,
        User $actor,
        string $reasonCode,
        int $expectedLockVersion,
        string $idempotencyKey,
        array $privateMetadata = [],
        bool $administrativeOverride = false,
    ): PetProfile {
        return DB::transaction(function () use (
            $profile,
            $target,
            $actor,
            $reasonCode,
            $expectedLockVersion,
            $idempotencyKey,
            $privateMetadata,
            $administrativeOverride,
        ): PetProfile {
            $existingEvent = $profile->lifecycleEvents()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return PetProfile::query()->findOrFail($existingEvent->pet_profile_id);
            }

            $locked = PetProfile::query()
                ->lockForUpdate()
                ->findOrFail($profile->id);
            $from = $locked->status;

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            if ($from === $target) {
                throw ValidationException::withMessages([
                    'status' => __('pet_profiles.validation.same_status'),
                ]);
            }

            if (! $administrativeOverride && ! $this->canTransition($from, $target)) {
                throw ValidationException::withMessages([
                    'status' => __('pet_profiles.validation.transition', [
                        'from' => $from->label(),
                        'to' => $target->label(),
                    ]),
                ]);
            }

            $now = now();
            $attributes = [
                'status' => $target,
                'state_entered_at' => $now,
                'lock_version' => $locked->lock_version + 1,
            ];

            if ($target === PetProfileStatus::Active) {
                $attributes['published_at'] = $locked->published_at ?? $now;
                $attributes['hidden_at'] = null;
                $attributes['archived_at'] = null;
                $attributes['deletion_requested_at'] = null;
                $attributes['deletion_scheduled_for'] = null;
            }

            if ($target === PetProfileStatus::Hidden) {
                $attributes['hidden_at'] = $now;
                $attributes['is_discoverable'] = false;
            }

            if ($target === PetProfileStatus::Archived) {
                $attributes['archived_at'] = $now;
                $attributes['is_discoverable'] = false;
            }

            if ($target === PetProfileStatus::Memorial) {
                $attributes['memorialized_at'] = $now;
            }

            if ($target === PetProfileStatus::DeletionPending) {
                $attributes['deletion_requested_at'] = $now;
                $attributes['deletion_scheduled_for'] = $now->addDays(
                    (int) config('pet_profiles.deletion_grace_days', 30),
                );
                $attributes['is_discoverable'] = false;
            }

            if ($target === PetProfileStatus::Merged) {
                $attributes['merged_at'] = $now;
                $attributes['is_discoverable'] = false;
            }

            $locked->forceFill($attributes)->save();
            $manager = $this->access->membership($locked, $actor);
            $this->events->record(
                profile: $locked,
                actor: $actor,
                eventType: 'status-changed',
                reasonCode: $reasonCode,
                fromStatus: $from->value,
                toStatus: $target->value,
                privateMetadata: $privateMetadata,
                idempotencyKey: $idempotencyKey,
                manager: $manager,
            );
            $this->cache->invalidate($locked);

            return $locked->refresh();
        }, 3);
    }

    public function canTransition(PetProfileStatus $from, PetProfileStatus $to): bool
    {
        return in_array($to, match ($from) {
            PetProfileStatus::Draft,
            PetProfileStatus::IdentityUnverified => [
                PetProfileStatus::Active,
                PetProfileStatus::Shelter,
                PetProfileStatus::FosterCare,
                PetProfileStatus::Found,
                PetProfileStatus::Hidden,
                PetProfileStatus::DeletionPending,
                PetProfileStatus::Archived,
            ],
            PetProfileStatus::Active,
            PetProfileStatus::FosterCare,
            PetProfileStatus::Shelter,
            PetProfileStatus::SeekingHome,
            PetProfileStatus::AdoptionInProgress,
            PetProfileStatus::Lost,
            PetProfileStatus::Found,
            PetProfileStatus::Transferred => [
                PetProfileStatus::Active,
                PetProfileStatus::FosterCare,
                PetProfileStatus::Shelter,
                PetProfileStatus::SeekingHome,
                PetProfileStatus::AdoptionInProgress,
                PetProfileStatus::Transferred,
                PetProfileStatus::Lost,
                PetProfileStatus::Found,
                PetProfileStatus::Hidden,
                PetProfileStatus::Memorial,
                PetProfileStatus::DeletionPending,
                PetProfileStatus::Archived,
            ],
            PetProfileStatus::Hidden,
            PetProfileStatus::Archived,
            PetProfileStatus::DeletionPending => [
                PetProfileStatus::Active,
                PetProfileStatus::Hidden,
                PetProfileStatus::Archived,
                PetProfileStatus::DeletionPending,
            ],
            PetProfileStatus::Memorial => [PetProfileStatus::Archived],
            PetProfileStatus::DisputedOwnership,
            PetProfileStatus::Merged => [],
        }, true);
    }
}
