<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetManagerStatus;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\EmailVerificationMode;
use App\Services\ForumActor;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AcceptPetProfileManagerInvitation
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly PetProfileEventRecorder $events,
        private readonly EmailVerificationMode $emailVerification,
        private readonly ValidationFactory $validator,
    ) {}

    public function handle(
        PetProfileManager $invitation,
        string $idempotencyKey,
    ): PetProfileManager {
        $authenticated = $this->actor->requireUser();
        /** @var array{idempotency_key: string} $validated */
        $validated = $this->validator->make([
            'idempotency_key' => trim($idempotencyKey),
        ], [
            'idempotency_key' => ['required', 'string', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $authenticated,
            $invitation,
            $validated,
        ): PetProfileManager {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($authenticated->getKey());
            abort_unless(
                $user->isActive() && $this->emailVerification->allows($user),
                403,
            );
            $locked = PetProfileManager::query()
                ->with('profile:id,user_id,profile_key,status,lock_version')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if ($locked->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'invitation' => __('pet_profiles.validation.invitation_unavailable'),
                ]);
            }

            $eventKey = hash(
                'sha256',
                "pet-manager-accept|{$locked->id}|{$user->id}|{$validated['idempotency_key']}",
            );
            $existingEvent = $locked->profile->lifecycleEvents()
                ->where(function ($events) use ($eventKey, $locked, $user, $validated): void {
                    $events
                        ->where('idempotency_key', $eventKey)
                        ->orWhere(function ($legacy) use ($locked, $user, $validated): void {
                            $legacy
                                ->where('idempotency_key', $validated['idempotency_key'])
                                ->where('event_type', 'manager-accepted')
                                ->where('manager_id', $locked->id)
                                ->where('actor_user_id', $user->id);
                        });
                })
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
                idempotencyKey: $eventKey,
                manager: $locked,
            );

            return $locked->refresh();
        }, 3);
    }
}
