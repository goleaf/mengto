<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class PerformanceSeeder extends Seeder
{
    private const PROFILE_COUNT = 250;

    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Performance seed data may only be created in an explicitly allowed environment.');
        }

        $owner = User::query()->firstOrNew([
            'actor_key' => 'performance-owner',
        ]);
        $owner->forceFill([
            'name' => 'Performance Fixture Owner',
            'email' => 'performance@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Active,
            'is_admin' => false,
        ])->save();

        foreach (range(1, self::PROFILE_COUNT) as $sequence) {
            $identifier = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            PetProfile::query()->updateOrCreate(
                ['profile_key' => 'performance-pet-'.$identifier],
                [
                    'user_id' => $owner->id,
                    'slug' => 'performance-pet-'.$identifier,
                    'name' => 'Performance Pet '.$identifier,
                    'species' => $sequence % 2 === 0 ? 'Dog' : 'Cat',
                    'breed' => 'Deterministic fixture',
                    'birth_date' => now()->subYears(($sequence % 12) + 1)->toDateString(),
                    'visibility' => $sequence % 10 === 0 ? 'private' : 'public',
                    'status' => $sequence % 25 === 0 ? 'inactive' : 'active',
                    'profile_data' => [
                        'fixture_sequence' => $sequence,
                        'purpose' => 'bounded pagination and visibility checks',
                    ],
                ],
            );
        }
    }
}
