<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserOnboarding;
use App\Services\SocialActorResolver;
use Illuminate\Database\Seeder;
use LogicException;

final class OnboardingBrowserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new LogicException('Onboarding browser fixtures are restricted to testing.');
        }

        $this->seedAccount(
            email: 'onboarding-browser@example.test',
            actorKey: 'onboarding-browser-en',
            locale: 'en',
            state: 'introduction',
        );
        $this->seedAccount(
            email: 'onboarding-browser-ru@example.test',
            actorKey: 'onboarding-browser-ru',
            locale: 'ru',
            state: 'privacy',
        );
    }

    private function seedAccount(
        string $email,
        string $actorKey,
        string $locale,
        string $state,
    ): void {
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $user = User::factory()->create([
                'actor_key' => $actorKey,
                'name' => 'Onboarding Browser Member',
                'email' => $email,
                'locale' => $locale,
                'timezone' => 'Europe/Vilnius',
                'email_verified_at' => now(),
            ]);
        }

        if (! $user->onboarding()->exists()) {
            $factory = UserOnboarding::factory()->for($user);

            $state === 'privacy'
                ? $factory->privacyDiscovery()->create()
                : $factory->create();
        }

        app(SocialActorResolver::class)->provisionPrivateForUser($user);
    }
}
