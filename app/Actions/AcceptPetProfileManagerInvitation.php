<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetManagerStatus;
use App\Models\PetProfileManager;
use App\Services\ForumActor;
use App\Services\PetProfileEventRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AcceptPetProfileManagerInvitation
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PetProfileEventRecorder $events,
    ) {}

    public function handle(
        PetProfileManager $invitation,
        string $idempotencyKey,
    ): PetProfileManager {
        $user = $this->actor->requireUser();

        return DB::transaction(function () use (
            $invitation,
            $idempotencyKey,
            $user,
        ): PetProfileManager {
            $locked = PetProfileManager::query()
                ->with('profile:id,user_id,profile_key,status,lock_version')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if ($locked->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'invitation' => __('pet_profiles.validation.invitation_unavailable'),
                ]);
            }

            $existingEvent = $locked->profile->lifecycleEvents()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null && $locked->status === PetManagerStatus::Active) {
                return $locked;
            }

            if ($locked->status !== PetManagerStatus::Invited) {
                throw ValidationException::withMessages([
                    'invitation' => __('pet_profiles.validation.invitation_unavailable'),
                ]);
            }

            if ($locked->ends_at !== null && $locked->ends_at->isPast()) {
                $locked->forceFill([
                    'status' => PetManagerStatus::Expired,
                    'lock_version' => $locked->lock_version + 1,
                ])->save();

                throw ValidationException::withMessages([
                    'invitation' => __('pet_profiles.validation.invitation_expired'),
                ]);
            }

            $locked->forceFill([
                'status' => PetManagerStatus::Active,
                'starts_at' => now(),
                'accepted_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->events->record(
                profile: $locked->profile,
                actor: $user,
                eventType: 'manager-accepted',
                reasonCode: 'manager-accepted',
                publicMetadata: ['role' => $locked->role->value],
                idempotencyKey: $idempotencyKey,
                manager: $locked,
            );

            return $locked->refresh();
        }, 3);
    }
}
