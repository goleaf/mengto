<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\ModerationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Enums\SellerType;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\User;
use App\ValueObjects\MinorUnitAmount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use LogicException;

class MarketplaceExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Marketplace demo data is restricted to explicitly allowed environments.');
        }

        $this->createListing('rehabilitation-ramp-rental-vilnius', [
            'owner_key' => 'mobility-care-vilnius',
            'owner_name' => 'Mobility Care Vilnius',
            'owner_initials' => 'MC',
            'type' => ListingType::Rental,
            'category' => 'rehabilitation',
            'brand' => 'PetSafe',
            'model' => 'Happy Ride',
            'material' => 'Aluminium and non-slip rubber',
            'title' => 'Folding rehabilitation ramp for short-term rental',
            'description' => 'A stable folding ramp for temporary mobility support. Each handover includes current-condition photos, a clean cover, safe-use notes, and an in-platform return record.',
            'condition' => 'good',
            'price' => 8,
            'quantity' => 2,
            'availability' => 'available-for-rent',
            'species' => ['dog'],
            'pet_size' => 'large',
            'age_group' => 'senior',
            'attributes' => [
                'rate_unit' => 'day',
                'deposit_amount' => 40,
                'minimum_days' => 3,
                'maximum_days' => 21,
                'length_cm' => 160,
                'width_cm' => 41,
                'max_weight_kg' => 90,
            ],
            'defects' => 'Light scratches on the frame. Hinges and non-slip surface are intact.',
            'hygiene_status' => 'cleaned',
            'city' => 'Vilnius',
            'area' => 'Žirmūnai',
            'delivery_options' => ['pickup', 'local-delivery'],
            'return_policy' => 'Return in the recorded condition by the agreed time. The deposit is released after inspection.',
            'cover_url' => asset('images/places/pet-store-primary-lg.jpg'),
            'seller_type' => SellerType::Business,
            'is_verified_seller' => true,
            'is_business' => true,
            'business_name' => 'Mobility Care Vilnius',
        ]);

        $this->createListing('sealed-sensitive-digestion-food-free-handover', [
            'owner_key' => 'rasa-v',
            'owner_name' => 'Rasa V.',
            'owner_initials' => 'RV',
            'type' => ListingType::Free,
            'category' => 'food',
            'brand' => 'Nature Balance',
            'model' => 'Sensitive Digestion',
            'material' => null,
            'title' => 'Sealed sensitive-digestion food for free handover',
            'description' => 'An unopened bag with the expiry date and batch label visible in the gallery. This is a free handover, not a medical recommendation, and the recipient should confirm that the food suits their pet.',
            'condition' => 'new',
            'price' => null,
            'is_free' => true,
            'quantity' => 1,
            'availability' => 'in-stock',
            'species' => ['dog'],
            'pet_size' => 'any',
            'age_group' => 'adult',
            'attributes' => [
                'package_weight_kg' => 3,
                'expiry_date' => now()->addMonths(8)->toDateString(),
                'batch_reference' => 'NB-SD-2408',
                'storage' => 'Stored sealed in a dry indoor cupboard.',
            ],
            'defects' => null,
            'hygiene_status' => 'sealed',
            'sealed_package' => true,
            'city' => 'Vilnius',
            'area' => 'Šnipiškės',
            'delivery_options' => ['meetup', 'shelter-delivery'],
            'return_policy' => 'Inspect the seal and expiry date at handover.',
            'cover_url' => asset('images/places/pet-store-secondary-lg.jpg'),
        ]);

        $this->createListing('vilnius-shelter-needs-twenty-carriers', [
            'owner_key' => 'vilnius-animal-aid',
            'owner_name' => 'Vilnius Animal Aid',
            'owner_initials' => 'VA',
            'type' => ListingType::ShelterNeed,
            'category' => 'shelter-supplies',
            'brand' => null,
            'model' => null,
            'material' => 'Washable rigid plastic',
            'title' => 'Shelter needs twenty secure pet carriers',
            'description' => 'The verified shelter needs washable carriers with intact locks for veterinary visits and foster transport. Donors can offer an item or arrange delivery without sharing a home address.',
            'condition' => 'not-applicable',
            'price' => null,
            'is_free' => true,
            'quantity' => 20,
            'availability' => 'needed',
            'species' => ['cat', 'dog'],
            'pet_size' => 'small',
            'age_group' => 'any',
            'attributes' => [
                'urgency' => 'urgent',
                'received_quantity' => 6,
                'needed_by' => now()->addWeeks(2)->toDateString(),
                'accepted_condition' => 'New or like new with intact locks',
            ],
            'defects' => null,
            'hygiene_status' => 'clean-required',
            'city' => 'Vilnius',
            'area' => 'Antakalnis',
            'delivery_options' => ['shelter-delivery', 'pickup'],
            'return_policy' => 'The shelter confirms receipt inside the platform.',
            'cover_url' => asset('images/places/shelter-primary-lg.jpg'),
            'seller_type' => SellerType::Shelter,
            'is_verified_seller' => true,
            'is_business' => true,
            'business_name' => 'Vilnius Animal Aid',
        ]);

        $this->createListing('quiet-cat-sitter-home-visits', [
            'owner_key' => 'quiet-paws-studio',
            'owner_name' => 'Quiet Paws Studio',
            'owner_initials' => 'QP',
            'type' => ListingType::Service,
            'category' => 'pet-sitting',
            'brand' => null,
            'model' => null,
            'material' => null,
            'title' => 'Verified cat sitter for two daily home visits',
            'description' => 'Two structured visits per day with feeding, water, litter care, a photo report, and an expiring access window. Medication is handled only from an existing written instruction.',
            'condition' => 'not-applicable',
            'price' => 36,
            'quantity' => 4,
            'availability' => 'bookable',
            'species' => ['cat'],
            'pet_size' => 'any',
            'age_group' => 'any',
            'attributes' => [
                'duration_minutes' => 35,
                'visits_per_day' => 2,
                'service_radius_km' => 8,
                'photo_report' => true,
                'overnight_stay' => false,
            ],
            'defects' => null,
            'hygiene_status' => 'not-applicable',
            'city' => 'Vilnius',
            'area' => 'Naujamiestis',
            'delivery_options' => ['home-visit'],
            'return_policy' => 'Cancel or reschedule at least 24 hours before the first visit.',
            'cover_url' => asset('images/places/community-primary-lg.jpg'),
            'seller_type' => SellerType::Specialist,
            'is_verified_seller' => true,
            'is_business' => true,
            'business_name' => 'Quiet Paws Studio',
        ]);

        $this->createDemoRentalOrder();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createListing(string $slug, array $attributes): void
    {
        if (Listing::query()->where('slug', $slug)->exists()) {
            return;
        }

        Listing::factory()->create([
            'owner_id' => User::query()
                ->where('actor_key', $attributes['owner_key'])
                ->value('id'),
            ...$attributes,
            'slug' => $slug,
            'currency' => 'EUR',
            'gallery' => [$attributes['cover_url']],
            'status' => ListingStatus::Published,
            'moderation_status' => ModerationStatus::Approved,
            'safety_status' => 'reviewed',
            'risk_flags' => ['demo-listing-reviewed'],
            'contact_policy' => 'platform-only',
            'meetup_notes' => 'Keep addresses and handover details private until the request is accepted.',
            'published_at' => now()->subHours(4),
        ]);
    }

    private function createDemoRentalOrder(): void
    {
        if (Order::query()->where('reference', 'ORD-DEMO-RAMP')->exists()) {
            return;
        }

        $listing = Listing::query()
            ->select([
                'id', 'owner_id', 'owner_key', 'owner_name', 'slug', 'title', 'condition',
                'quantity', 'price', 'currency', 'delivery_options',
                'return_policy', 'contact_policy', 'cover_url', 'attributes',
            ])
            ->where('slug', 'rehabilitation-ramp-rental-vilnius')
            ->firstOrFail();
        $buyer = User::query()->where('actor_key', 'mia-carter')->firstOrFail();

        $startsAt = now()->addDays(3)->startOfDay();
        $endsAt = $startsAt->copy()->addDays(6);
        $reservation = Reservation::factory()->create([
            'listing_id' => $listing->id,
            'requester_id' => $buyer->id,
            'requester_key' => 'mia-carter',
            'requester_name' => 'Mia Carter',
            'idempotency_key' => (string) Str::uuid(),
            'status' => ReservationStatus::Accepted,
            'request_kind' => 'rental',
            'quantity' => 1,
            'message' => 'I need the ramp for one week after a planned procedure.',
            'exchange_method' => 'pickup',
            'proposed_at' => $startsAt->copy()->subDay()->setTime(16, 0),
            'rental_starts_at' => $startsAt,
            'rental_ends_at' => $endsAt,
            'questionnaire' => [
                'pet' => 'Scout',
                'purpose' => 'Temporary mobility support',
            ],
            'responded_at' => now()->subHour(),
            'expires_at' => now()->addDay(),
        ]);

        $rentalDays = 7;
        $unitPrice = MinorUnitAmount::fromDecimal($listing->price ?? 0);
        $depositValue = data_get($listing->attributes, 'deposit_amount', 0);
        $deposit = MinorUnitAmount::fromDecimal(
            is_string($depositValue) || is_int($depositValue) ? $depositValue : 0,
        );
        $total = $unitPrice->multiply($rentalDays)->add($deposit);

        Order::factory()->create([
            'listing_id' => $listing->id,
            'reservation_id' => $reservation->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $listing->owner_id,
            'reference' => 'ORD-DEMO-RAMP',
            'idempotency_key' => (string) Str::uuid(),
            'buyer_key' => 'mia-carter',
            'buyer_name' => 'Mia Carter',
            'seller_key' => $listing->owner_key,
            'seller_name' => $listing->owner_name,
            'order_kind' => 'rental',
            'quantity' => 1,
            'unit_price' => $unitPrice->toDecimal(),
            'delivery_amount' => 0,
            'deposit_amount' => $deposit->toDecimal(),
            'total_amount' => $total->toDecimal(),
            'currency' => $listing->currency,
            'delivery_method' => 'pickup',
            'public_delivery_area' => 'Žirmūnai, Vilnius',
            'status' => OrderStatus::AwaitingPayment,
            'payment_status' => PaymentStatus::Pending,
            'item_snapshot' => [
                'slug' => $listing->slug,
                'title' => $listing->title,
                'condition' => $listing->condition,
                'quantity' => 1,
                'unit_price' => $unitPrice->toDecimal(),
                'currency' => $listing->currency,
                'cover_url' => $listing->cover_url,
                'attributes' => $listing->attributes,
            ],
            'terms_snapshot' => [
                'delivery_method' => 'pickup',
                'public_delivery_area' => 'Žirmūnai, Vilnius',
                'return_policy' => $listing->return_policy,
                'contact_policy' => $listing->contact_policy,
                'rental_starts_at' => $startsAt->toDateString(),
                'rental_ends_at' => $endsAt->toDateString(),
                'rental_days' => $rentalDays,
                'request_message' => $reservation->message,
                'terms_accepted' => true,
                'privacy_accepted' => true,
                'captured_at' => now()->toIso8601String(),
            ],
            'ordered_at' => now()->subHour(),
        ]);

        $listing->update(['quantity' => max(0, $listing->quantity - 1)]);
    }
}
