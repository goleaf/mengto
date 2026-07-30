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

final class UpdatePetProfilePrivacy
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

        $privacy = [
            'location' => (string) $data['location_visibility'],
            'posts' => (string) $data['posts_visibility'],
            'friends' => (string) $data['friends_visibility'],
            'care' => (string) $data['care_visibility'],
            'activity' => (string) $data['activity_visibility'],
        ];

        return DB::transaction(function () use ($privacy, $profile, $slug): PetProfile {
            $profile->forceFill([
                'profile_data' => [
                    ...($profile->profile_data ?? []),
                    'privacy' => $privacy,
                ],
            ])->save();

            // Keep pre-normalization profile snapshots readable during the compatibility window.
            $this->state->updatePetPrivacy($slug, $privacy);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'pet-profile-owner',
                'action' => 'pet-profile.privacy-updated',
                'target_type' => PetProfile::class,
                'target_id' => (string) $profile->id,
                'metadata' => [
                    'profile_key' => $profile->profile_key,
                    'sections' => array_keys($privacy),
                ],
            ]);

            return $profile->refresh();
        }, 3);
    }
}
