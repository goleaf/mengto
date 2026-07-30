<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SocialIdentitySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->select(['id', 'actor_key'])
            ->where('actor_key', 'mia-carter')
            ->firstOrFail();

        $this->profile($user, [
            'profile_key' => 'pet-scout',
            'slug' => 'scout',
            'name' => 'Scout',
            'species' => 'Dog',
            'breed' => 'Border Collie mix',
            'profile_data' => [
                'age' => '4 years',
                'status' => 'Available for park walks',
            ],
        ]);
        $this->profile($user, [
            'profile_key' => 'pet-nori',
            'slug' => 'nori',
            'name' => 'Nori',
            'species' => 'Cat',
            'breed' => 'Domestic Shorthair',
            'profile_data' => [
                'age' => '6 years',
                'status' => 'Indoor enrichment friend',
            ],
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function profile(User $user, array $attributes): void
    {
        PetProfile::query()->updateOrCreate(
            ['profile_key' => $attributes['profile_key']],
            [
                ...$attributes,
                'user_id' => $user->id,
                'visibility' => 'public',
                'status' => 'active',
            ],
        );
    }
}
