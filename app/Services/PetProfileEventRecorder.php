<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\User;

final class PetProfileEventRecorder
{
    /**
     * @param  array<string, mixed>  $publicMetadata
     * @param  array<string, mixed>  $privateMetadata
     */
    public function record(
        PetProfile $profile,
        ?User $actor,
        string $eventType,
        string $reasonCode,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $publicMetadata = [],
        array $privateMetadata = [],
        ?string $idempotencyKey = null,
        ?PetProfileManager $manager = null,
    ): PetProfileLifecycleEvent {
        if ($idempotencyKey !== null) {
            $existing = PetProfileLifecycleEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof PetProfileLifecycleEvent) {
                return $existing;
            }
        }

        $actorKey = $actor instanceof User ? $actor->actor_key : 'system';

        return PetProfileLifecycleEvent::query()->create([
            'pet_profile_id' => $profile->id,
            'actor_user_id' => $actor?->id,
            'manager_id' => $manager?->id,
            'actor_key_snapshot' => $actorKey,
            'actor_role_snapshot' => $manager?->role->value,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason_code' => $reasonCode,
            'reason_translation_key' => "pet_profiles.reasons.{$reasonCode}",
            'lock_version' => $profile->lock_version,
            'idempotency_key' => $idempotencyKey,
            'public_metadata' => $publicMetadata,
            'private_metadata' => $privateMetadata,
            'occurred_at' => now(),
        ]);
    }
}
