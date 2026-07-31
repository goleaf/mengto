<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PetProfileVisibility;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\PetProfilePrivacySetting;
use App\Services\ForumActor;
use App\Services\PetProfileAccess;
use App\Services\PetProfileCache;
use App\Services\PetProfileEventRecorder;
use App\Services\PrototypeState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfilePrivacy
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PrototypeState $state,
        private readonly PetProfileAccess $access,
        private readonly PetProfileEventRecorder $events,
        private readonly PetProfileCache $cache,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(string $slug, array $data): PetProfile
    {
        $user = $this->actor->requireUser();
        $profile = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'visibility',
                'status',
                'is_discoverable',
                'allow_external_indexing',
                'lock_version',
                'profile_data',
            ])
            ->managedBy($user)
            ->where('slug', $slug)
            ->first();

        if ($profile === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('managePrivacy', $profile);

        $privacy = [
            'location' => (string) $data['location_visibility'],
            'posts' => (string) $data['posts_visibility'],
            'friends' => (string) $data['friends_visibility'],
            'care' => (string) $data['care_visibility'],
            'activity' => (string) $data['activity_visibility'],
        ];
        $normalizedPrivacy = collect($privacy)
            ->map(static fn (string $value): string => PetProfileVisibility::fromStored($value)->value)
            ->all();
        $eventIdempotencyKey = isset($data['idempotency_key'])
            ? 'pet-privacy:'.hash('sha256', (string) $data['idempotency_key'])
            : null;

        return DB::transaction(function () use ($data, $eventIdempotencyKey, $normalizedPrivacy, $privacy, $profile, $slug, $user): PetProfile {
            if ($eventIdempotencyKey !== null && $profile->lifecycleEvents()
                ->where('idempotency_key', $eventIdempotencyKey)
                ->exists()) {
                return PetProfile::query()->findOrFail($profile->id);
            }

            $locked = PetProfile::query()
                ->lockForUpdate()
                ->findOrFail($profile->id);
            $expectedVersion = (int) ($data['lock_version'] ?? $locked->lock_version);

            if ($locked->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('pet_profiles.validation.stale_profile'),
                ]);
            }

            $visibility = PetProfileVisibility::fromStored(
                (string) ($data['profile_visibility'] ?? $locked->visibility),
            );
            $isDiscoverable = (bool) ($data['is_discoverable'] ?? $locked->is_discoverable);
            $allowExternalIndexing = (bool) (
                $data['allow_external_indexing'] ?? $locked->allow_external_indexing
            );

            if ($visibility !== PetProfileVisibility::Public) {
                $allowExternalIndexing = false;
            }

            $locked->forceFill([
                'visibility' => $visibility->value,
                'is_discoverable' => $isDiscoverable,
                'allow_external_indexing' => $allowExternalIndexing,
                'lock_version' => $locked->lock_version + 1,
                'profile_data' => [
                    ...($locked->profile_data ?? []),
                    'privacy' => $privacy,
                ],
            ])->save();
            $settings = PetProfilePrivacySetting::query()->firstOrNew([
                'pet_profile_id' => $locked->id,
            ]);
            $settings->fill([
                'profile_visibility' => $visibility,
                'section_rules' => $normalizedPrivacy,
                'is_discoverable' => $isDiscoverable,
                'allow_external_indexing' => $allowExternalIndexing,
                'allow_direct_link' => (bool) ($data['allow_direct_link'] ?? false),
                'owner_display_mode' => (string) ($data['owner_display_mode'] ?? 'contact-button'),
                'manager_display_mode' => (string) ($data['manager_display_mode'] ?? 'hidden'),
                'public_location_precision' => (string) (
                    $data['public_location_precision'] ?? 'hidden'
                ),
                'lock_version' => ((int) ($settings->lock_version ?? 0)) + 1,
                'updated_by_user_id' => $user->id,
            ])->save();

            // Keep pre-normalization profile snapshots readable during the compatibility window.
            $this->state->updatePetPrivacy($slug, $privacy);

            $manager = $this->access->membership($locked, $user);
            $this->events->record(
                profile: $locked,
                actor: $user,
                eventType: 'privacy-updated',
                reasonCode: 'privacy-updated',
                publicMetadata: ['sections' => array_keys($privacy)],
                privateMetadata: [
                    'visibility' => $visibility->value,
                    'is_discoverable' => $isDiscoverable,
                    'allow_external_indexing' => $allowExternalIndexing,
                ],
                idempotencyKey: $eventIdempotencyKey,
                manager: $manager,
            );

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => $manager?->role->value ?? 'legacy-owner',
                'action' => 'pet-profile.privacy-updated',
                'target_type' => PetProfile::class,
                'target_id' => (string) $locked->id,
                'metadata' => [
                    'profile_key' => $locked->profile_key,
                    'sections' => array_keys($privacy),
                ],
            ]);
            $this->cache->invalidate($locked);

            return $locked->refresh();
        }, 3);
    }
}
