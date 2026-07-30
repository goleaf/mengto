<?php

namespace Database\Factories;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\ModerationStatus;
use App\Enums\SellerType;
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
            'brand' => fake()->randomElement(['Ruffwear', 'Trixie', 'Hunter', null]),
            'model' => null,
            'material' => 'Nylon webbing',
            'title' => $title,
            'description' => fake()->paragraphs(2, true),
            'condition' => 'good',
            'price' => fake()->randomFloat(2, 5, 120),
            'currency' => 'EUR',
            'is_free' => false,
            'quantity' => 1,
            'availability' => 'in-stock',
            'exchange_preferences' => null,
            'species' => ['dog'],
            'pet_size' => 'medium',
            'age_group' => 'adult',
            'attributes' => [
                'length_cm' => 42,
                'width_cm' => 28,
                'height_cm' => 30,
                'max_weight_kg' => 12,
            ],
            'defects' => 'Light cosmetic wear only. Buckles and stitching are intact.',
            'hygiene_status' => 'cleaned',
            'sealed_package' => false,
            'city' => 'Vilnius',
            'area' => 'Naujamiestis',
            'delivery_options' => ['meetup', 'pickup'],
            'meetup_notes' => 'Agree on a public meeting point in platform messages.',
            'return_policy' => 'Inspect during handover. Hidden defects can be reported through the platform.',
            'cover_url' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?auto=format&fit=crop&w=1200&q=85',
            'gallery' => [],
            'video_url' => null,
            'status' => ListingStatus::Published,
            'safety_status' => 'community',
            'moderation_status' => ModerationStatus::Approved,
            'risk_flags' => [],
            'is_business' => false,
            'business_name' => null,
            'seller_type' => SellerType::PrivateSeller,
            'is_verified_seller' => false,
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
            'seller_type' => SellerType::Shelter,
            'is_verified_seller' => true,
            'attributes' => [
                'animal_name' => 'Luna',
                'animal_age' => 'Adult',
                'animal_sex' => 'Female',
                'temperament' => 'Calm indoors and cautious with new people.',
                'adoption_conditions' => 'Two meetings and an adoption agreement are required.',
            ],
        ]);
    }

    public function rental(): static
    {
        return $this->state(fn (): array => [
            'type' => ListingType::Rental,
            'category' => 'rehabilitation',
            'price' => 8,
            'is_free' => false,
            'availability' => 'available-for-rent',
            'attributes' => [
                'rate_unit' => 'day',
                'deposit_amount' => 40,
                'minimum_days' => 3,
                'maximum_days' => 21,
                'max_weight_kg' => 45,
            ],
        ]);
    }

    public function shelterNeed(): static
    {
        return $this->state(fn (): array => [
            'type' => ListingType::ShelterNeed,
            'category' => 'shelter-supplies',
            'price' => null,
            'is_free' => true,
            'quantity' => 20,
            'seller_type' => SellerType::Shelter,
            'is_verified_seller' => true,
            'business_name' => 'Vilnius Safe Paws',
            'is_business' => true,
            'attributes' => [
                'urgency' => 'urgent',
                'received_quantity' => 6,
                'needed_by' => now()->addWeeks(2)->toDateString(),
                'accepted_condition' => 'New or like new',
            ],
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
