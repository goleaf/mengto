<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(EmailVerificationMode $emailVerification): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Demo seed data may only be created in an explicitly allowed environment.');
        }

        $this->call(ForumSystemSeeder::class);

        $this->demoUser([
            'actor_key' => 'mia-carter',
            'name' => 'Mia Carter',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => now()->subDay(),
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
            'actor_key' => 'demo-caregiver',
            'name' => 'Demo Caregiver',
            'email' => 'caregiver@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'Europe/London',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => now()->subDays(2),
        ]);
        $this->demoUser([
            'actor_key' => 'demo-volunteer',
            'name' => 'Demo Search Volunteer',
            'email' => 'volunteer@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => now()->subHours(8),
        ]);
        $this->demoUser([
            'actor_key' => 'demo-expert-client',
            'name' => 'Demo Expert Client',
            'email' => 'expert-client@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'ru',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => now()->subWeek(),
        ]);
        $this->demoUser([
            'actor_key' => 'demo-suspended',
            'name' => 'Demo Suspended Member',
            'email' => 'suspended@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Suspended,
            'is_admin' => false,
            'last_login_at' => now()->subMonth(),
        ]);
        $this->demoUser([
            'actor_key' => 'demo-marketplace-member',
            'name' => 'Demo Marketplace Member',
            'email' => 'marketplace@example.test',
            'email_verified_at' => now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => now()->subDays(4),
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
            'email_verified_at' => $emailVerification->isEnabled()
                ? null
                : now(),
            'password' => 'password',
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Active,
            'is_admin' => false,
        ]);

        $this->call(SocialIdentitySeeder::class);
        $this->call(OrganizationAuthoritySeeder::class);
        $this->call(PetProfileFoundationSeeder::class);
        $this->call(ForumEventBackfillSeeder::class);
        $this->call(ForumSeeder::class);
        $this->call(ForumTopicLifecycleBackfillSeeder::class);
        $this->call(ForumTopicLifecycleDemoSeeder::class);
        $this->call(ForumJournalBackfillSeeder::class);
        $this->call(ForumJournalDemoSeeder::class);
        $this->call(CollaborativeGuideDemoSeeder::class);
        $this->call(ForumGroupDemoSeeder::class);
        $this->call(MentorshipDemoSeeder::class);
        $this->call(ForumTopicTaxonomyBackfillSeeder::class);
        $this->call(ExpertSeeder::class);
        $this->call(ForumExpertSessionDemoSeeder::class);
        $this->call(ForumEventDemoSeeder::class);
        $this->call(PlaceAuthoritySeeder::class);
        $this->call(PlaceDemoSeeder::class);
        $this->call(PlaceSubmissionDemoSeeder::class);
        $this->call(ForumEventLifecycleBackfillSeeder::class);
        $this->call(ListingSeeder::class);
        $this->call(AdoptionCaseSeeder::class);
        $this->call(AdoptionDemoSeeder::class);
        $this->call(MarketplaceExpansionSeeder::class);
        $this->call(SearchSeeder::class);
        $this->call(SearchCaseIntegritySeeder::class);
        $this->call(MedicalRecordSeeder::class);
        $this->call(CareJournalSeeder::class);
        $this->call(SmartDeviceSeeder::class);
        $this->call(SocialActorFoundationSeeder::class);
        $this->call(DiscoveryDemoSeeder::class);
        $this->call(RepresentativeDomainSeeder::class);
        $this->call(RepresentativeFieldCoverageSeeder::class);

        // Representative factories can introduce new parent aggregates. Run
        // idempotent foundation/backfill passes once more so a fresh seed is
        // complete on its first execution.
        $this->call(AdoptionCaseSeeder::class);
        $this->call(PetProfileFoundationSeeder::class);
        $this->call(SocialActorFoundationSeeder::class);
        $this->call(ForumTopicLifecycleBackfillSeeder::class);
        $this->call(ForumTopicTaxonomyBackfillSeeder::class);
        $this->call(ForumJournalBackfillSeeder::class);
        $this->call(ForumEventBackfillSeeder::class);
        $this->call(ForumEventLifecycleBackfillSeeder::class);
        $this->call(SearchCaseIntegritySeeder::class);
    }

    /** @param array<string, mixed> $attributes */
    private function demoUser(array $attributes): User
    {
        $actorKey = (string) $attributes['actor_key'];
        $email = (string) $attributes['email'];
        $actorUser = User::query()->where('actor_key', $actorKey)->first();
        $emailUser = User::query()->where('email', $email)->first();

        if (
            ($actorUser instanceof User && $actorUser->email !== $email)
            ||
            ($emailUser instanceof User && $emailUser->actor_key !== $actorKey)
            || ($actorUser instanceof User && $emailUser instanceof User && ! $actorUser->is($emailUser))
        ) {
            throw new LogicException(
                "Demo identity conflict for actor {$actorKey} and email {$email}.",
            );
        }

        $user = $actorUser ?? $emailUser ?? new User;
        $user->forceFill($attributes)->save();

        return $user;
    }
}
