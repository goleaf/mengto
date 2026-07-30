<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        if (Listing::query()->exists()) {
            return;
        }

        $listings = collect([
            [
                'owner_key' => 'lena-petrauskaite',
                'owner_name' => 'Lena Petrauskaitė',
                'owner_initials' => 'LP',
                'type' => ListingType::Sale,
                'category' => 'walking-gear',
                'title' => 'Reflective Ruffwear harness for medium dogs',
                'description' => 'A clean, lightly used reflective harness with secure buckles and no damaged stitching. Scout outgrew this size. Happy to show every strap in platform messages before a public meetup.',
                'condition' => 'like-new',
                'price' => 28,
                'species' => ['dog'],
                'pet_size' => 'medium',
                'city' => 'Vilnius',
                'area' => 'Žvėrynas',
                'delivery_options' => ['meetup', 'shipping'],
                'cover_url' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=1400&q=85',
            ],
            [
                'owner_key' => 'quiet-paws-studio',
                'owner_name' => 'Quiet Paws Studio',
                'owner_initials' => 'QP',
                'type' => ListingType::Service,
                'category' => 'pet-service',
                'title' => 'Quiet one-to-one grooming session for cats',
                'description' => 'Individual grooming appointment with no dogs in the room and no forced high-power drying. We pause when a cat shows sustained stress and explain every optional step before confirming the booking.',
                'condition' => 'not-applicable',
                'price' => 45,
                'species' => ['cat'],
                'pet_size' => 'any',
                'city' => 'Vilnius',
                'area' => 'Naujamiestis',
                'delivery_options' => ['meetup'],
                'cover_url' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1400&q=85',
                'is_business' => true,
                'business_name' => 'Quiet Paws Studio',
            ],
            [
                'owner_key' => 'vilnius-animal-aid',
                'owner_name' => 'Vilnius Animal Aid',
                'owner_initials' => 'VA',
                'type' => ListingType::Adoption,
                'category' => 'adoption',
                'title' => 'Gentle adult cat Mėta is ready for adoption',
                'description' => 'Mėta is a calm adult cat looking for an indoor home. Adoption includes a structured application, a meeting, verified veterinary records, and post-adoption support. No payment is requested in private messages.',
                'condition' => 'not-applicable',
                'price' => null,
                'is_free' => true,
                'species' => ['cat'],
                'pet_size' => 'small',
                'city' => 'Vilnius',
                'area' => 'Antakalnis',
                'delivery_options' => ['meetup'],
                'cover_url' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1400&q=85',
                'is_business' => true,
                'business_name' => 'Vilnius Animal Aid',
            ],
            [
                'owner_key' => 'tomas-jankauskas',
                'owner_name' => 'Tomas Jankauskas',
                'owner_initials' => 'TJ',
                'type' => ListingType::Exchange,
                'category' => 'carriers-travel',
                'title' => 'Exchange airline carrier for a larger model',
                'description' => 'I have a sturdy cabin carrier in excellent condition and need the next size up. I can share measurements and current photos here. Exchange only at a public place after both sides confirm dimensions.',
                'condition' => 'good',
                'price' => null,
                'exchange_preferences' => 'Looking for a ventilated carrier at least 48 cm long.',
                'species' => ['cat', 'dog'],
                'pet_size' => 'small',
                'city' => 'Kaunas',
                'area' => 'Centras',
                'delivery_options' => ['meetup'],
                'cover_url' => 'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?auto=format&fit=crop&w=1400&q=85',
            ],
            [
                'owner_key' => 'rasa-v',
                'owner_name' => 'Rasa V.',
                'owner_initials' => 'RV',
                'type' => ListingType::Sale,
                'category' => 'beds-home',
                'title' => 'Free washable orthopedic dog bed',
                'description' => 'Clean orthopedic bed with a removable washable cover. The foam remains supportive, although the cover has a small repaired seam. Free to a household or foster volunteer who can use it.',
                'condition' => 'fair',
                'price' => null,
                'is_free' => true,
                'species' => ['dog'],
                'pet_size' => 'large',
                'city' => 'Vilnius',
                'area' => 'Šnipiškės',
                'delivery_options' => ['pickup', 'meetup'],
                'cover_url' => 'https://images.unsplash.com/photo-1522276498395-f4f68f7f8454?auto=format&fit=crop&w=1400&q=85',
            ],
            [
                'owner_key' => 'mia-carter',
                'owner_name' => 'Mia Carter',
                'owner_initials' => 'MC',
                'type' => ListingType::Sale,
                'category' => 'training-enrichment',
                'title' => 'Adjustable enrichment puzzle in excellent condition',
                'description' => 'A washable enrichment puzzle with three difficulty settings. All pieces are intact and shown in the gallery. I prefer a daytime public meetup and will not ask for payment details in chat.',
                'condition' => 'like-new',
                'price' => 18,
                'species' => ['dog', 'cat'],
                'pet_size' => 'any',
                'city' => 'Vilnius',
                'area' => 'Naujamiestis',
                'delivery_options' => ['meetup', 'pickup'],
                'cover_url' => 'https://images.unsplash.com/photo-1591946614720-90a587da4a36?auto=format&fit=crop&w=1400&q=85',
            ],
            [
                'owner_key' => 'ari-jensen',
                'owner_name' => 'Ari Jensen',
                'owner_initials' => 'AJ',
                'type' => ListingType::Service,
                'category' => 'pet-service',
                'title' => 'Online travel checklist review for pet owners',
                'description' => 'A practical online review of your travel checklist, carrier preparation, and questions to verify with official authorities. This is organizational guidance, not veterinary or legal certification.',
                'condition' => 'not-applicable',
                'price' => 22,
                'species' => ['dog', 'cat', 'bird'],
                'pet_size' => 'any',
                'city' => 'Vilnius',
                'area' => null,
                'delivery_options' => ['online'],
                'cover_url' => 'https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=1400&q=85',
            ],
            [
                'owner_key' => 'monika-k',
                'owner_name' => 'Monika K.',
                'owner_initials' => 'MK',
                'type' => ListingType::Sale,
                'category' => 'carriers-travel',
                'title' => 'Crash-tested travel crate with divider',
                'description' => 'Travel crate with divider, intact door hardware, and clear dimensions. It has normal cosmetic marks from use but no cracks. Collection is arranged only after a request is accepted.',
                'condition' => 'good',
                'price' => 84,
                'species' => ['dog'],
                'pet_size' => 'large',
                'city' => 'Klaipėda',
                'area' => 'Centras',
                'delivery_options' => ['pickup'],
                'cover_url' => 'https://images.unsplash.com/photo-1507146426996-ef05306b995a?auto=format&fit=crop&w=1400&q=85',
            ],
        ])->map(function (array $attributes): Listing {
            $title = $attributes['title'];

            return Listing::factory()->create([
                ...$attributes,
                'slug' => Str::slug($title),
                'currency' => 'EUR',
                'meetup_notes' => 'Use platform messages, confirm the item or service first, and choose a safe public location.',
                'gallery' => [],
                'status' => ListingStatus::Published,
                'safety_status' => 'community',
                'contact_policy' => 'platform-only',
                'published_at' => now()->subDays(fake()->numberBetween(0, 12)),
            ]);
        });

        $firstListing = $listings->first();
        $ownerListing = $listings->firstWhere('owner_key', 'mia-carter');

        if ($firstListing instanceof Listing) {
            ListingEngagement::factory()->create([
                'listing_id' => $firstListing->id,
                'user_key' => 'mia-carter',
                'is_saved' => true,
            ]);
        }

        if ($ownerListing instanceof Listing) {
            Reservation::factory()->create([
                'listing_id' => $ownerListing->id,
                'requester_key' => 'noah-williams',
                'requester_name' => 'Noah Williams',
                'idempotency_key' => (string) Str::uuid(),
                'message' => 'Could we meet near the library on Saturday afternoon? I would like to check that every piece moves smoothly.',
                'exchange_method' => 'meetup',
                'status' => 'requested',
                'proposed_at' => now()->addDays(3)->setTime(15, 0),
                'expires_at' => now()->addDays(4),
            ]);
        }
    }
}
