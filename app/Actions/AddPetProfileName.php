<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfileName;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PetProfileNameNormalizer;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AddPetProfileName
{
    public function __construct(
        private ForumActor $actor,
        private Gate $gate,
        private PetProfileAccess $access,
        private PetProfileCache $cache,
        private PetProfileEventRecorder $events,
        private PetProfileNameNormalizer $normalizer,
    ) {}

    /** @param array{name: string, type: string, visibility: string, locale: string|null} $data */
    public function handle(PetProfile $profile, array $data): PetProfileName
    {
        $user = $this->actor->requireUser();
        $target = PetProfile::query()
            ->select(['id', 'user_id', 'profile_key', 'name', 'status', 'lock_version'])
            ->managedBy($user)
            ->find($profile->id);

        if (! $target instanceof PetProfile) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('manageNames', $target);
        $normalized = $this->normalizer->normalize($data['name']);

        if ($normalized === $this->normalizer->normalize($target->name)) {
            throw ValidationException::withMessages([
                'nameForm.name' => __('pet_profiles.validation.name_matches_current'),
            ]);
        }

        return DB::transaction(function () use ($data, $normalized, $target, $user): PetProfileName {
            $locked = PetProfile::query()->lockForUpdate()->findOrFail($target->id);
            $this->gate->authorize('manageNames', $locked);

            if ($normalized === $this->normalizer->normalize($locked->name)) {
                throw ValidationException::withMessages([
                    'nameForm.name' => __('pet_profiles.validation.name_matches_current'),
                ]);
            }

            $existing = $locked->names()
                ->withTrashed()
                ->where('normalized_name', $normalized)
                ->first();

            if ($existing instanceof PetProfileName && ! $existing->trashed()) {
                return $existing;
            }

            $attributes = [
                'name' => trim($data['name']),
                'normalized_name' => $normalized,
                'type' => PetProfileNameType::from($data['type']),
                'visibility' => PetProfileNameVisibility::from($data['visibility']),
                'locale' => filled($data['locale']) ? $data['locale'] : null,
                'is_searchable' => true,
                'recorded_by_user_id' => $user->id,
                'recorded_at' => now(),
            ];
            $name = $existing instanceof PetProfileName
                ? tap($existing, static function (PetProfileName $restored) use ($attributes): void {
                    $restored->restore();
                    $restored->forceFill($attributes)->save();
                })
                : $locked->names()->create($attributes);
            $locked->increment('lock_version');
            $manager = $this->access->membership($locked, $user);
            $this->events->record(
                profile: $locked,
                actor: $user,
                eventType: 'profile-name-added',
                reasonCode: 'profile-name-added',
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
                'action' => 'pet-profile.name-added',
                'target_type' => PetProfile::class,
                'target_id' => (string) $locked->id,
                'metadata' => [
                    'profile_key' => $locked->profile_key,
                    'name_id' => $name->id,
                    'type' => $name->type->value,
                    'visibility' => $name->visibility->value,
                ],
            ]);
            $this->cache->invalidate($locked);

            return $name;
        }, 3);
    }
}
