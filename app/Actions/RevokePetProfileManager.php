<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Models\PetProfileManager;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RevokePetProfileManager
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
    ) {}

    public function handle(
        PetProfileManager $membership,
        string $reasonCode,
        string $idempotencyKey,
    ): PetProfileManager {
        $actor = $this->actor->requireUser();
        $profile = $membership->profile()->firstOrFail();
        $this->gate->authorize('manageManagers', $profile);

        if ($membership->role === PetManagerRole::PrimaryOwner) {
            throw ValidationException::withMessages([
                'membership' => __('pet_profiles.validation.primary_owner_workflow'),
            ]);
        }

        return DB::transaction(function () use (
            $membership,
            $reasonCode,
            $idempotencyKey,
            $actor,
            $profile,
        ): PetProfileManager {
            $existingEvent = $profile->lifecycleEvents()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return PetProfileManager::query()->findOrFail($membership->id);
            }

            $locked = PetProfileManager::query()
                ->lockForUpdate()
                ->findOrFail($membership->id);
            $locked->forceFill([
                'status' => PetManagerStatus::Revoked,
                'revoked_at' => now(),
                'revoked_by_user_id' => $actor->id,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $actorMembership = $this->access->membership($profile, $actor);
            $this->events->record(
                profile: $profile,
                actor: $actor,
                eventType: 'manager-revoked',
                reasonCode: $reasonCode,
                privateMetadata: ['revoked_manager_id' => $locked->id],
                idempotencyKey: $idempotencyKey,
                manager: $actorMembership,
            );

            return $locked->refresh();
        }, 3);
    }
}
