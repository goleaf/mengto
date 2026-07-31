<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfileStatus;
use App\Enums\PetProfileVisibility;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use Illuminate\Support\Facades\DB;

final class PetProfileFoundationBackfill
{
    public function __construct(private readonly PetProfileEventRecorder $events) {}

    /**
     * @return array{processed: int, managers: int, privacy: int, aliases: int, profiles_normalized: int}
     */
    public function run(int $chunkSize = 500): array
    {
        $counts = [
            'processed' => 0,
            'managers' => 0,
            'privacy' => 0,
            'aliases' => 0,
            'profiles_normalized' => 0,
        ];

        PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'visibility',
                'status',
                'lock_version',
                'is_discoverable',
                'allow_external_indexing',
                'profile_data',
                'created_at',
            ])
            ->with('user:id,actor_key')
            ->chunkById($chunkSize, function ($profiles) use (&$counts): void {
                foreach ($profiles as $profile) {
                    DB::transaction(function () use ($profile, &$counts): void {
                        $manager = PetProfileManager::query()->firstOrCreate(
                            [
                                'pet_profile_id' => $profile->id,
                                'user_id' => $profile->user_id,
                            ],
                            [
                                'actor_key_snapshot' => $profile->user->actor_key,
                                'role' => PetManagerRole::PrimaryOwner,
                                'status' => PetManagerStatus::Active,
                                'permission_overrides' => null,
                                'evidence_status' => PetEvidenceStatus::Unverified,
                                'starts_at' => $profile->created_at ?? now(),
                                'accepted_at' => $profile->created_at ?? now(),
                                'lock_version' => 1,
                                'metadata' => ['source' => 'legacy-owner-backfill'],
                            ],
                        );

                        if ($manager->wasRecentlyCreated) {
                            $counts['managers']++;
                        }

                        $profileData = $profile->profile_data ?? [];
                        $legacyRules = is_array($profileData['privacy'] ?? null)
                            ? $profileData['privacy']
                            : [];
                        $allowedSections = config('pet_profiles.allowed_section_privacy_keys', []);
                        $sectionRules = collect($legacyRules)
                            ->map(static fn (mixed $value): mixed => is_string($value)
                                ? PetProfileVisibility::fromStored($value)->value
                                : $value)
                            ->filter(
                                static fn (mixed $value, mixed $key): bool => is_string($key)
                                    && is_string($value)
                                    && in_array($key, $allowedSections, true)
                                    && PetProfileVisibility::tryFrom($value) !== null,
                            )
                            ->all();
                        $visibility = PetProfileVisibility::fromStored($profile->visibility);
                        $rawStatus = $profile->getRawOriginal('status');
                        $status = $rawStatus === 'inactive'
                            ? PetProfileStatus::Archived
                            : $profile->status;
                        $isDiscoverable = $visibility === PetProfileVisibility::Public
                            && $profile->is_discoverable;
                        $allowExternalIndexing = $visibility === PetProfileVisibility::Public
                            && $profile->allow_external_indexing;

                        if ($rawStatus !== $status->value
                            || $profile->visibility !== $visibility->value
                            || $profile->is_discoverable !== $isDiscoverable
                            || $profile->allow_external_indexing !== $allowExternalIndexing
                        ) {
                            $profile->forceFill([
                                'status' => $status,
                                'visibility' => $visibility->value,
                                'is_discoverable' => $isDiscoverable,
                                'allow_external_indexing' => $allowExternalIndexing,
                                'lock_version' => $profile->lock_version + 1,
                            ])->save();
                            $counts['profiles_normalized']++;
                        }

                        $privacy = PetProfilePrivacySetting::query()->firstOrCreate(
                            ['pet_profile_id' => $profile->id],
                            [
                                'profile_visibility' => $visibility,
                                'section_rules' => $sectionRules,
                                'is_discoverable' => $isDiscoverable,
                                'allow_external_indexing' => $allowExternalIndexing,
                                'allow_direct_link' => $visibility === PetProfileVisibility::Public,
                                'owner_display_mode' => 'contact-button',
                                'manager_display_mode' => 'hidden',
                                'public_location_precision' => 'hidden',
                                'lock_version' => 1,
                                'updated_by_user_id' => $profile->user_id,
                            ],
                        );

                        if ($privacy->wasRecentlyCreated) {
                            $counts['privacy']++;
                        }

                        $alias = PetProfileSlugAlias::query()->firstOrCreate(
                            [
                                'pet_profile_id' => $profile->id,
                                'slug' => $profile->slug,
                            ],
                            [
                                'source' => 'legacy-profile',
                                'is_active' => true,
                            ],
                        );

                        if ($alias->wasRecentlyCreated) {
                            $counts['aliases']++;
                        }

                        $this->events->record(
                            profile: $profile,
                            actor: null,
                            eventType: 'foundation-backfilled',
                            reasonCode: 'foundation-backfilled',
                            publicMetadata: ['profile_key' => $profile->profile_key],
                            idempotencyKey: "pet-foundation:backfill:{$profile->id}",
                            manager: $manager,
                        );
                        $counts['processed']++;
                    }, 3);
                }
            });

        return $counts;
    }
}
