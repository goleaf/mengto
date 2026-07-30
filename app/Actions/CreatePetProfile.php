<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Services\ForumActor;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreatePetProfile
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): PetProfile
    {
        $user = $this->actor->requireUser();
        $this->gate->authorize('create', PetProfile::class);

        return DB::transaction(function () use ($data, $user): PetProfile {
            $profile = PetProfile::query()->create([
                'user_id' => $user->id,
                'profile_key' => 'created-pet-'.Str::lower((string) Str::uuid()),
                'slug' => $this->uniqueSlug($user->id, (string) $data['title']),
                'name' => (string) $data['title'],
                'species' => (string) $data['category'],
                'breed' => ($data['detail'] ?? null) ?: null,
                'visibility' => 'private',
                'status' => 'active',
                'profile_data' => [
                    'story' => (string) $data['body'],
                    'status' => (string) $data['body'],
                ],
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'pet-profile-owner',
                'action' => 'pet-profile.created',
                'target_type' => PetProfile::class,
                'target_id' => (string) $profile->id,
                'metadata' => [
                    'profile_key' => $profile->profile_key,
                    'species' => $profile->species,
                    'visibility' => $profile->visibility,
                ],
            ]);

            return $profile;
        }, 3);
    }

    private function uniqueSlug(int $userId, string $name): string
    {
        $base = Str::slug($name) ?: 'pet';
        $slug = $base;
        $suffix = 2;

        while (PetProfile::query()
            ->where('user_id', $userId)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
