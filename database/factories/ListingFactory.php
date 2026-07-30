<?php

namespace Database\Factories;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'owner_key' => fake()->unique()->userName(),
            'owner_name' => fake()->name(),
            'owner_initials' => fake()->lexify('??'),
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'type' => ListingType::Sale,
            'category' => 'walking-gear',
            'title' => $title,
            'description' => fake()->paragraphs(2, true),
            'condition' => 'good',
            'price' => fake()->randomFloat(2, 5, 120),
            'currency' => 'EUR',
            'is_free' => false,
            'exchange_preferences' => null,
            'species' => ['dog'],
            'pet_size' => 'medium',
            'city' => 'Vilnius',
            'area' => 'Naujamiestis',
            'delivery_options' => ['meetup', 'pickup'],
            'meetup_notes' => 'Agree on a public meeting point in platform messages.',
            'cover_url' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=1200&q=85',
            'gallery' => [],
            'status' => ListingStatus::Published,
            'safety_status' => 'community',
            'is_business' => false,
            'business_name' => null,
            'contact_policy' => 'platform-only',
            'view_count' => fake()->numberBetween(2, 240),
            'published_at' => now(),
        ];
    }

    public function adoption(): static
    {
        return $this->state(fn (): array => [
            'type' => ListingType::Adoption,
            'category' => 'adoption',
            'condition' => null,
            'price' => null,
            'is_free' => true,
            'species' => ['cat'],
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => ListingStatus::Draft,
            'published_at' => null,
        ]);
    }
}
