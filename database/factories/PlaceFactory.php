<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceStatus;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use App\Models\Organization;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<Place> */
final class PlaceFactory extends ApplicationFactory
{
    protected $model = Place::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Community Place';

        return [
            'owner_user_id' => User::factory(),
            'organization_id' => null,
            'created_by_user_id' => User::factory(),
            'last_edited_by_user_id' => User::factory(),
            'stable_key' => Str::slug($name).'-'.Str::lower((string) Str::ulid()),
            'slug' => Str::slug($name).'-'.Str::lower((string) Str::ulid()),
            'creation_idempotency_key' => 'place-factory-'.Str::lower((string) Str::ulid()),
            'name' => $name,
            'summary' => fake()->sentence(),
            'type' => PlaceType::PublicSpace,
            'visibility' => PlaceVisibility::Public,
            'status' => PlaceStatus::Active,
            'locale' => 'en',
            'public_region' => 'Vilnius',
            'public_address' => fake()->streetAddress().', Vilnius',
            'public_latitude' => '54.687200',
            'public_longitude' => '25.279700',
            'exact_address' => fake()->streetAddress().', Vilnius',
            'exact_latitude' => '54.687234',
            'exact_longitude' => '25.279734',
            'private_instructions' => null,
            'is_indoor' => false,
            'verification_status' => PlaceVerificationStatus::NotAssessed,
            'accessibility_status' => PlaceAccessibilityStatus::NotAssessed,
            'accessibility_facts' => [],
            'transport_information' => null,
            'parking_information' => null,
            'pet_rules' => fake()->sentence(),
            'species_rules' => ['dog'],
            'lock_version' => 0,
            'archived_at' => null,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PlaceVisibility::Public,
            'type' => PlaceType::PublicSpace,
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PlaceVisibility::Unlisted,
            'type' => PlaceType::PublicSpace,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => [
            'visibility' => PlaceVisibility::Private,
            'type' => PlaceType::PrivateHome,
            'public_address' => null,
            'public_latitude' => '54.687000',
            'public_longitude' => '25.279000',
            'private_instructions' => fake()->sentence(),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => PlaceVerificationStatus::Verified,
            'verification_source' => 'verified-place-registry',
            'verified_at' => now(),
            'information_expires_at' => now()->addYear(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceStatus::Archived,
            'archived_at' => now(),
        ]);
    }

    public function forOrganization(?Organization $organization = null): static
    {
        return $this
            ->for($organization ?? Organization::factory())
            ->state(fn (): array => ['owner_user_id' => null]);
    }
}
