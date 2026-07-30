<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use App\Services\PrototypeState;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePetProfile
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly PrototypeState $state,
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
                'name',
                'species',
                'breed',
                'visibility',
                'status',
                'profile_data',
            ])
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->first();

        if ($profile === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.pet_profile_unavailable'),
            ]);
        }

        $this->gate->authorize('update', $profile);

        return DB::transaction(function () use ($data, $profile, $slug): PetProfile {
            $profileData = $profile->profile_data ?? [];
            $profile->forceFill([
                'name' => (string) $data['title'],
                'breed' => ($data['category'] ?? null) ?: null,
                'profile_data' => [
                    ...$profileData,
                    'story' => (string) $data['body'],
                    'status' => (string) ($data['detail'] ?? ''),
                ],
            ])->save();

            // Keep pre-normalization profile snapshots readable during the compatibility window.
            $this->state->updatePet([
                'name' => $profile->name,
                'story' => (string) $data['body'],
                'status' => (string) ($data['detail'] ?? ''),
                'breed' => $profile->breed ?? '',
            ], $slug);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'pet-profile-owner',
                'action' => 'pet-profile.updated',
                'target_type' => PetProfile::class,
                'target_id' => (string) $profile->id,
                'metadata' => ['profile_key' => $profile->profile_key],
            ]);

            return $profile->refresh();
        }, 3);
    }
}
