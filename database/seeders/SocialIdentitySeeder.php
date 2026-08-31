<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PetProfile;
use App\Models\User;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use LogicException;

final class SocialIdentitySeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        $this->assertDemoSeedingIsAllowed();

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
            'birth_date' => '2022-04-18',
            'birth_date_precision' => 'exact',
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
            'birth_date' => '2020-09-01',
            'birth_date_precision' => 'month',
            'profile_data' => [
                'age' => '6 years',
                'status' => 'Indoor enrichment friend',
            ],
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function profile(User $user, array $attributes): void
    {
        $profile = PetProfile::query()->firstOrNew([
            'profile_key' => $attributes['profile_key'],
        ]);

        if ($profile->exists && $profile->user_id !== $user->id) {
            throw new LogicException('Demo pet profile key is already owned by another account.');
        }

        $profile->forceFill([
            ...$attributes,
            'user_id' => $user->id,
            'visibility' => 'public',
            'status' => 'active',
            'is_discoverable' => true,
            'published_at' => '2026-07-31 12:00:00',
        ])->saveOrFail();
    }
}
