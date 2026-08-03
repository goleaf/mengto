<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ContentMediaStatus;
use App\Enums\PetProfileMediaStatus;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileMedia;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileEventRecorder;
use App\Validation\PetProfileMediaRules;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class RestorePetPrimaryPhoto
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private PetProfileAccess $access,
        private PetProfileEventRecorder $events,
    ) {}

    public function handle(
        PetProfile $profile,
        PetProfileMedia $media,
        string $idempotencyKey,
    ): PetProfileMedia {
        $user = $this->actor->requireUser();
        $this->gate->authorize('manageMedia', $profile);
        Validator::make(
            ['idempotency_key' => $idempotencyKey],
            ['idempotency_key' => PetProfileMediaRules::idempotencyKey()],
        )->validate();
        $eventKey = $this->eventKey($profile, $media, $user->id, $idempotencyKey);
        $existing = PetProfileLifecycleEvent::query()
            ->where('idempotency_key', $eventKey)
            ->first();

        if ($existing instanceof PetProfileLifecycleEvent) {
            $this->validateReplay($existing, $profile);

            return $media->refresh();
        }

        return DB::transaction(function () use ($eventKey, $media, $profile, $user): PetProfileMedia {
            $lockedProfile = PetProfile::query()
                ->select(['id', 'user_id', 'profile_key', 'status', 'lock_version'])
                ->lockForUpdate()
                ->findOrFail($profile->id);
            $this->gate->forUser($user)->authorize('manageMedia', $lockedProfile);
            $existing = PetProfileLifecycleEvent::query()
                ->where('idempotency_key', $eventKey)
                ->first();

            if ($existing instanceof PetProfileLifecycleEvent) {
                $this->validateReplay($existing, $lockedProfile);

                return $media->refresh();
            }

            $restored = PetProfileMedia::query()
                ->with('asset')
                ->where('pet_profile_id', $lockedProfile->id)
                ->lockForUpdate()
                ->findOrFail($media->id);

            if (! in_array($restored->status, [
                PetProfileMediaStatus::Superseded,
                PetProfileMediaStatus::Removed,
            ], true)
                || $restored->recoverable_until === null
                || $restored->recoverable_until->isPast()
                || $restored->asset->status !== ContentMediaStatus::Ready
            ) {
                throw ValidationException::withMessages([
                    'mediaForm.upload' => __('pet_profiles.validation.photo_not_recoverable'),
                ]);
            }

            $current = PetProfileMedia::query()
                ->where('pet_profile_id', $lockedProfile->id)
                ->where('current_key', "primary:{$lockedProfile->id}")
                ->lockForUpdate()
                ->first();

            if ($current instanceof PetProfileMedia && ! $current->is($restored)) {
                $current->forceFill([
                    'status' => PetProfileMediaStatus::Superseded,
                    'current_key' => null,
                    'recoverable_until' => now()->addDays(
                        config('images.pet_profile_uploads.recovery_days', 30),
                    ),
                    'replaced_at' => now(),
                ])->save();
            }

            $restored->forceFill([
                'status' => PetProfileMediaStatus::Active,
                'current_key' => "primary:{$lockedProfile->id}",
                'recoverable_until' => null,
                'replaced_at' => null,
                'removed_at' => null,
                'restored_at' => now(),
            ])->save();
            $lockedProfile->increment('lock_version');
            $manager = $this->access->membership($lockedProfile, $user);
            $this->events->record(
                profile: $lockedProfile,
                actor: $user,
                eventType: 'primary-photo-restored',
                reasonCode: 'primary-photo-restored',
                publicMetadata: ['media_key' => $restored->media_key],
                idempotencyKey: $eventKey,
                manager: $manager,
            );
            AuditLog::query()->create([
                'actor_key' => $user->actor_key,
                'actor_role' => $manager?->role->value ?? 'profile-manager',
                'action' => 'pet-profile.primary-photo-restored',
                'target_type' => PetProfile::class,
                'target_id' => (string) $lockedProfile->id,
                'metadata' => ['media_key' => $restored->media_key],
            ]);

            return $restored;
        }, 3);
    }

    private function eventKey(
        PetProfile $profile,
        PetProfileMedia $media,
        int $userId,
        string $idempotencyKey,
    ): string {
        return 'pet-photo-restore:'.hash(
            'sha256',
            "{$profile->id}|{$media->id}|{$userId}|{$idempotencyKey}",
        );
    }

    private function validateReplay(
        PetProfileLifecycleEvent $event,
        PetProfile $profile,
    ): void {
        if ($event->pet_profile_id !== $profile->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => __('pet_profiles.validation.photo_idempotency_conflict'),
            ]);
        }
    }
}
