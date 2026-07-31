<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Demo seed data may only be created in an explicitly allowed environment.');
        }

        $this->call(ForumSystemSeeder::class);

        $this->demoUser([
            'actor_key' => 'mia-carter',
            'name' => 'Mia Carter',
            'email' => 'mia@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
        ]);
        $this->demoUser([
            'actor_key' => 'demo-administrator',
            'name' => 'Demo Administrator',
            'email' => 'administrator@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Active,
            'is_admin' => true,
        ]);
        $this->demoUser([
            'actor_key' => 'demo-lithuanian',
            'name' => 'Demo Lithuanian Member',
            'email' => 'lithuanian@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'lt',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
        ]);
        $this->demoUser([
            'actor_key' => 'demo-russian-blocked',
            'name' => 'Demo Blocked Member',
            'email' => 'blocked@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'ru',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Blocked,
            'is_admin' => false,
        ]);
        $this->demoUser([
            'actor_key' => 'demo-unverified',
            'name' => 'Demo Unverified Member',
            'email' => 'unverified@example.test',
            'email_verified_at' => null,
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Active,
            'is_admin' => false,
        ]);

        $this->call(SocialIdentitySeeder::class);
        $this->call(ForumGroupDemoSeeder::class);
        $this->call(ForumSeeder::class);
        $this->call(CollaborativeGuideDemoSeeder::class);
        $this->call(MentorshipDemoSeeder::class);
        $this->call(ForumTopicTaxonomyBackfillSeeder::class);
        $this->call(ExpertSeeder::class);
        $this->call(ListingSeeder::class);
        $this->call(AdoptionCaseSeeder::class);
        $this->call(AdoptionDemoSeeder::class);
        $this->call(MarketplaceExpansionSeeder::class);
        $this->call(SearchSeeder::class);
        $this->call(SearchCaseIntegritySeeder::class);
        $this->call(MedicalRecordSeeder::class);
        $this->call(CareJournalSeeder::class);
        $this->call(SmartDeviceSeeder::class);
    }

    /** @param array<string, mixed> $attributes */
    private function demoUser(array $attributes): User
    {
        $user = User::query()->firstOrNew([
            'actor_key' => $attributes['actor_key'],
        ]);
        $user->forceFill($attributes)->save();

        return $user;
    }
}
