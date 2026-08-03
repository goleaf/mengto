<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileNameVisibility;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RemovePetProfileName
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private PetProfileAccess $access,
        private PetProfileCache $cache,
        private PetProfileEventRecorder $events,
    ) {}

    public function handle(PetProfile $profile, int $nameId): void
    {
        $user = $this->actor->requireUser();
        $target = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'status', 'lock_version'])
            ->managedBy($user)
            ->find($profile->id);

        if (! $target instanceof PetProfile) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('manageNames', $target);

        DB::transaction(function () use ($nameId, $target, $user): void {
            $locked = PetProfile::query()->lockForUpdate()->findOrFail($target->id);
            $this->gate->authorize('manageNames', $locked);
            $name = $locked->names()
                ->where(function ($visibility) use ($user): void {
                    $visibility
                        ->where('visibility', '!=', PetProfileNameVisibility::Private->value)
                        ->orWhere('recorded_by_user_id', $user->id);
                })
                ->lockForUpdate()
                ->findOrFail($nameId);
            $name->delete();
            $locked->increment('lock_version');
            $manager = $this->access->membership($locked, $user);
            $this->events->record(
                profile: $locked,
                actor: $user,
                eventType: 'profile-name-removed',
                reasonCode: 'profile-name-removed',
                publicMetadata: [
                    'type' => $name->type->value,
                    'visibility' => $name->visibility->value,
                ],
                privateMetadata: ['name' => $name->name],
                manager: $manager,
            );
            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => $manager?->role->value ?? 'legacy-owner',
                'action' => 'pet-profile.name-removed',
                'target_type' => PetProfile::class,
                'target_id' => (string) $locked->id,
                'metadata' => [
                    'profile_key' => $locked->profile_key,
                    'name_id' => $name->id,
                ],
            ]);
            $this->cache->invalidate($locked);
        }, 3);
    }
}
