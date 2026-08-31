<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\InitializeUserOnboarding;
use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class CanonicalIdentityBrowserSeeder extends Seeder
{
    public function run(InitializeUserOnboarding $initializeOnboarding): void
    {
        if (! app()->environment('testing')) {
            throw new LogicException('Canonical identity browser fixtures are restricted to testing.');
        }

        $user = User::query()->firstOrCreate(
            ['email' => 'andrej-browser@example.test'],
            [
                'actor_key' => 'andrej-browser',
                'name' => 'Andrej Browser',
                'email_verified_at' => now(),
                'password' => 'password',
                'locale' => 'en',
                'timezone' => 'Europe/Vilnius',
            ],
        );

        $onboarding = $initializeOnboarding->handle($user);
        $user->socialActor()->firstOrFail()->forceFill([
            'actor_key' => '00000000-0000-4000-8000-000000000001',
        ])->saveOrFail();
        $completedAt = $onboarding->completed_at ?? now();

        $onboarding->forceFill([
            'current_step' => OnboardingStep::Complete,
            'pet_relationship_choice' => OnboardingPetChoice::AddLater,
            'introduction_completed_at' => $onboarding->introduction_completed_at ?? $completedAt,
            'preferences_completed_at' => $onboarding->preferences_completed_at ?? $completedAt,
            'pet_relationship_completed_at' => $onboarding->pet_relationship_completed_at ?? $completedAt,
            'privacy_discovery_completed_at' => $onboarding->privacy_discovery_completed_at ?? $completedAt,
            'completed_at' => $completedAt,
            'lock_version' => max($onboarding->lock_version, OnboardingStep::Complete->position()),
        ])->saveOrFail();
    }
}
