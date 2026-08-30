<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserOnboarding;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<User>
 */
class UserFactory extends ApplicationFactory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_key' => 'user-'.Str::lower((string) Str::ulid()),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => UserStatus::Active,
            'is_admin' => false,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => UserStatus::Blocked,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => UserStatus::Suspended,
        ]);
    }

    public function administrator(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_admin' => true,
            'status' => UserStatus::Active,
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes): array => [
            'locale' => 'lt',
            'timezone' => 'Europe/Vilnius',
        ]);
    }

    public function russian(): static
    {
        return $this->state(fn (array $attributes): array => [
            'locale' => 'ru',
            'timezone' => 'Europe/Vilnius',
        ]);
    }

    public function onboardingIncomplete(): static
    {
        return $this->has(UserOnboarding::factory(), 'onboarding');
    }

    public function onboardingAtPreferences(): static
    {
        return $this->has(
            UserOnboarding::factory()->preferences(),
            'onboarding',
        );
    }

    public function onboardingAtPets(): static
    {
        return $this->has(
            UserOnboarding::factory()->petRelationship(),
            'onboarding',
        );
    }

    public function onboardingAtPrivacy(): static
    {
        return $this->has(
            UserOnboarding::factory()->privacyDiscovery(),
            'onboarding',
        );
    }

    public function onboarded(): static
    {
        return $this->has(
            UserOnboarding::factory()->completed(),
            'onboarding',
        );
    }
}
