<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class InvitePetProfileManager
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
    ) {}

    /** @param array<string, list<string>> $permissionOverrides */
    public function handle(
        PetProfile $profile,
        User $invitee,
        PetManagerRole $role,
        ?\DateTimeInterface $endsAt,
        array $permissionOverrides,
        string $idempotencyKey,
    ): PetProfileManager {
        $inviter = $this->actor->requireUser();
        $this->gate->authorize('manageManagers', $profile);

        if ($invitee->id === $inviter->id) {
            throw ValidationException::withMessages([
                'email' => __('pet_profiles.validation.cannot_invite_self'),
            ]);
        }

        if (in_array($role, [
            PetManagerRole::PrimaryOwner,
            PetManagerRole::LegalRepresentative,
            PetManagerRole::OrganizationAdministrator,
            PetManagerRole::PreviousOwner,
        ], true)) {
            throw ValidationException::withMessages([
                'role' => __('pet_profiles.validation.critical_role_workflow'),
            ]);
        }

        return DB::transaction(function () use (
            $profile,
            $invitee,
            $role,
            $endsAt,
            $permissionOverrides,
            $idempotencyKey,
            $inviter,
        ): PetProfileManager {
            $existingEvent = $profile->lifecycleEvents()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent !== null) {
                return PetProfileManager::query()
                    ->where('pet_profile_id', $profile->id)
                    ->where('user_id', $invitee->id)
                    ->firstOrFail();
            }

            $manager = PetProfileManager::query()
                ->where('pet_profile_id', $profile->id)
                ->where('user_id', $invitee->id)
                ->lockForUpdate()
                ->first();
            $lockVersion = $manager instanceof PetProfileManager
                ? $manager->lock_version
                : 0;
            $attributes = [
                'actor_key_snapshot' => $invitee->actor_key,
                'role' => $role,
                'status' => PetManagerStatus::Invited,
                'permission_overrides' => $this->sanitizeOverrides($permissionOverrides),
                'evidence_status' => PetEvidenceStatus::Unverified,
                'starts_at' => null,
                'ends_at' => $endsAt,
                'accepted_at' => null,
                'revoked_at' => null,
                'invited_by_user_id' => $inviter->id,
                'revoked_by_user_id' => null,
                'lock_version' => $lockVersion + 1,
            ];

            if ($manager instanceof PetProfileManager) {
                $manager->forceFill($attributes)->save();
            } else {
                $manager = PetProfileManager::query()->create([
                    'pet_profile_id' => $profile->id,
                    'user_id' => $invitee->id,
                    ...$attributes,
                ]);
            }

            $inviterMembership = $this->access->membership($profile, $inviter);
            $this->events->record(
                profile: $profile,
                actor: $inviter,
                eventType: 'manager-invited',
                reasonCode: 'manager-invited',
                publicMetadata: ['role' => $role->value],
                privateMetadata: ['invitee_user_id' => $invitee->id],
                idempotencyKey: $idempotencyKey,
                manager: $inviterMembership,
            );

            return $manager->refresh();
        }, 3);
    }

    /**
     * @param  array<string, list<string>>  $overrides
     * @return array{grant: list<string>, deny: list<string>}
     */
    private function sanitizeOverrides(array $overrides): array
    {
        $allowed = collect(PetProfilePermission::cases())
            ->map(static fn (PetProfilePermission $permission): string => $permission->value)
            ->all();

        return [
            'grant' => collect($overrides['grant'] ?? [])->intersect($allowed)->unique()->values()->all(),
            'deny' => collect($overrides['deny'] ?? [])->intersect($allowed)->unique()->values()->all(),
        ];
    }
}
