<?php

use App\Actions\PerformListingAction;
use App\Enums\ListingStatus;
use App\Enums\ModerationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\ListingReview;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\Reservation;
use Database\Seeders\MarketplaceExpansionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('dangerous products and medical guarantees cannot be published', function () {
    $this->from(route('marketplace.create'))
        ->post(route('marketplace.store'), marketplacePayload([
            'title' => 'Prescription medicine for animals in sealed packs',
            'description' => 'Prescription medicine from a private home that claims a guaranteed cure and replaces a veterinarian for every animal.',
        ]))
        ->assertRedirect(route('marketplace.create'))
        ->assertSessionHasErrors('description');

    expect(Listing::query()->count())->toBe(0);
});

test('adoption profiles require review and never become ordinary sale listings', function () {
    $response = $this->post(route('marketplace.store'), marketplacePayload([
        'type' => 'adoption',
        'category' => 'adoption',
        'condition' => 'not-applicable',
        'price' => null,
        'is_free' => 1,
        'animal_name' => 'Luna',
        'animal_age' => 'Three years',
        'animal_sex' => 'female',
        'temperament' => 'Calm indoors and needs gradual introductions to other cats.',
        'adoption_conditions' => 'Two meetings, identity verification, and a signed transfer agreement.',
        'title' => 'Luna seeks a calm and responsible permanent home',
    ]));

    $listing = Listing::query()->firstOrFail();

    $response->assertRedirect(route('marketplace.show', $listing));
    expect($listing)
        ->status->toBe(ListingStatus::Draft)
        ->moderation_status->toBe(ModerationStatus::Pending)
        ->is_free->toBeTrue()
        ->price->toBeNull()
        ->and($listing->risk_flags)->toContain('adoption-review');

    $this->get(route('marketplace.index'))->assertDontSee($listing->title);
});

test('accepting a rental reserves inventory and captures immutable order terms', function () {
    $listing = Listing::factory()->rental()->create([
        'owner_key' => 'mia-carter',
        'owner_name' => 'Mia Carter',
        'quantity' => 2,
        'price' => 12.50,
        'return_policy' => 'Return clean before 18:00.',
        'attributes' => [
            'deposit_amount' => 20,
            'minimum_days' => 1,
            'maximum_days' => 14,
        ],
    ]);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'noah-williams',
        'requester_name' => 'Noah Williams',
        'request_kind' => 'rental',
        'quantity' => 1,
        'rental_starts_at' => now()->addDays(2)->startOfDay(),
        'rental_ends_at' => now()->addDays(4)->startOfDay(),
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'accept-request',
        'reservation_id' => $reservation->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    $order = Order::query()->where('reservation_id', $reservation->id)->firstOrFail();

    expect($listing->refresh())
        ->quantity->toBe(1)
        ->status->toBe(ListingStatus::Published)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Accepted)
        ->and($order)
        ->order_kind->toBe('rental')
        ->status->toBe(OrderStatus::AwaitingPayment)
        ->payment_status->toBe(PaymentStatus::Pending)
        ->total_amount->toBe('45.00')
        ->and(data_get($order->item_snapshot, 'title'))->toBe($listing->title)
        ->and(data_get($order->terms_snapshot, 'return_policy'))->toBe('Return clean before 18:00.')
        ->and(data_get($order->terms_snapshot, 'rental_days'))->toBe(2);

    $capturedTitle = data_get($order->item_snapshot, 'title');
    $listing->update(['title' => 'Edited public title']);

    expect(data_get($order->fresh()->item_snapshot, 'title'))->toBe($capturedTitle);
});

test('paid marketplace orders cannot be completed before protected payment', function () {
    [$listing, $reservation, $order] = acceptedMarketplaceOrder();

    $this->from(route('marketplace.show', $listing))
        ->post(route('marketplace.actions', $listing), [
            'action' => 'mark-complete',
            'reservation_id' => $reservation->id,
        ])
        ->assertRedirect(route('marketplace.show', $listing))
        ->assertSessionHasErrors('reservation_id');

    expect($reservation->refresh()->status)->toBe(ReservationStatus::Accepted);

    $order->update(['payment_status' => PaymentStatus::Failed]);

    $this->from(route('marketplace.show', $listing))
        ->post(route('marketplace.actions', $listing), [
            'action' => 'mark-complete',
            'reservation_id' => $reservation->id,
        ])
        ->assertRedirect(route('marketplace.show', $listing))
        ->assertSessionHasErrors('reservation_id');

    $order->update([
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'paid_at' => now(),
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'mark-complete',
        'reservation_id' => $reservation->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    expect($order->refresh()->status)->toBe(OrderStatus::Completed)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Completed)
        ->and($listing->refresh()->status)->toBe(ListingStatus::Completed);
});

test('a stale cancellation cannot restore reserved inventory twice', function () {
    [$listing, $reservation] = acceptedMarketplaceOrder();
    $action = app(PerformListingAction::class);
    $staleReservation = $reservation->replicate();
    $staleReservation->id = $reservation->id;
    $staleReservation->exists = true;

    $action->handle($listing, [
        'action' => 'cancel-request',
        '_reservation' => $staleReservation,
    ]);

    expect($listing->refresh()->quantity)->toBe(1)
        ->and(fn () => $action->handle($listing, [
            'action' => 'cancel-request',
            '_reservation' => $staleReservation,
        ]))->toThrow(ValidationException::class)
        ->and($listing->refresh()->quantity)->toBe(1);
});

test('only order participants can view an order or open a dispute', function () {
    [$listing, $reservation, $order] = acceptedMarketplaceOrder([
        'buyer_key' => 'another-buyer',
        'seller_key' => 'another-seller',
    ]);

    $this->get(route('marketplace.orders.show', [$listing, $order]))->assertForbidden();

    $order->update([
        'buyer_key' => 'mia-carter',
        'payment_status' => PaymentStatus::Paid,
    ]);

    $this->get(route('marketplace.orders.show', [$listing, $order]))
        ->assertOk()
        ->assertSee($order->reference)
        ->assertSee('Terms captured at acceptance');

    $this->post(route('marketplace.orders.disputes.store', [$listing, $order]), [
        'reason' => 'counterfeit',
        'details' => 'The serial number and packaging do not match the photographed product.',
    ])->assertRedirect(route('marketplace.orders.show', [$listing, $order]));

    expect(OrderDispute::query()->firstOrFail())
        ->priority->toBe('high')
        ->opened_by_role->toBe('buyer')
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Disputed)
        ->payment_status->toBe(PaymentStatus::Disputed);
});

test('only a completed buyer can publish one verified review', function () {
    [$listing, $reservation, $order] = acceptedMarketplaceOrder();
    $order->update([
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'completed_at' => now(),
    ]);

    $payload = [
        'item_rating' => 5,
        'seller_rating' => 4,
        'delivery_rating' => 5,
        'body' => 'The item matched every measurement and the public handover was punctual.',
    ];

    $this->post(route('marketplace.orders.reviews.store', [$listing, $order]), $payload)
        ->assertRedirect(route('marketplace.orders.show', [$listing, $order]));

    expect(ListingReview::query()->firstOrFail())
        ->is_verified_buyer->toBeTrue()
        ->reviewer_key->toBe('mia-carter');

    $this->from(route('marketplace.orders.show', [$listing, $order]))
        ->post(route('marketplace.orders.reviews.store', [$listing, $order]), $payload)
        ->assertSessionHasErrors('order');

    expect(ListingReview::query()->count())->toBe(1);
});

test('structured filters and uploaded media are persisted without leaking drafts', function () {
    Storage::fake('public');

    $response = $this->post(route('marketplace.store'), marketplacePayload([
        'seller_type' => 'private',
        'condition' => 'like-new',
        'availability' => 'low-stock',
        'brand' => 'Atlas',
        'model' => '40',
        'photos' => [UploadedFile::fake()->image('carrier.webp', 1200, 800)],
        'video' => UploadedFile::fake()->create('locks.mp4', 1024, 'video/mp4'),
    ]));

    $listing = Listing::query()->firstOrFail();
    $response->assertRedirect(route('marketplace.show', $listing));

    expect($listing->gallery)->toHaveCount(1)
        ->and($listing->video_url)->not->toBeNull();

    Storage::disk('public')->assertExists(str($listing->gallery[0])->after('/storage/')->toString());
    Storage::disk('public')->assertExists(str($listing->video_url)->after('/storage/')->toString());

    Listing::factory()->create([
        'title' => 'Different condition listing hidden by filters',
        'condition' => 'fair',
        'availability' => 'in-stock',
    ]);

    $this->get(route('marketplace.index', [
        'condition' => 'like-new',
        'seller_type' => 'private',
        'availability' => 'low-stock',
    ]))
        ->assertOk()
        ->assertSee($listing->title)
        ->assertDontSee('Different condition listing hidden by filters');
});

test('marketplace expansion seeder is idempotent and creates the demo order once', function () {
    $seeder = app(MarketplaceExpansionSeeder::class);

    $seeder->run();
    $seeder->run();

    expect(Listing::query()->whereIn('slug', [
        'rehabilitation-ramp-rental-vilnius',
        'sealed-sensitive-digestion-food-free-handover',
        'vilnius-shelter-needs-twenty-carriers',
        'quiet-cat-sitter-home-visits',
    ])->count())->toBe(4)
        ->and(Order::query()->where('reference', 'ORD-DEMO-RAMP')->count())->toBe(1)
        ->and(Reservation::query()->where('requester_key', 'mia-carter')->count())->toBe(1)
        ->and(Listing::query()
            ->where('slug', 'rehabilitation-ramp-rental-vilnius')
            ->value('quantity'))->toBe(1);
});

test('marketplace schema includes stock order dispute and review indexes', function () {
    $listingIndexes = collect(Schema::getIndexes('listings'))->pluck('name');
    $reservationIndexes = collect(Schema::getIndexes('reservations'))->pluck('name');
    $orderIndexes = collect(Schema::getIndexes('orders'))->pluck('name');
    $disputeIndexes = collect(Schema::getIndexes('order_disputes'))->pluck('name');
    $reviewIndexes = collect(Schema::getIndexes('listing_reviews'))->pluck('name');

    expect($listingIndexes)
        ->toContain('listings_availability_condition_idx')
        ->toContain('listings_seller_verification_idx')
        ->toContain('listings_moderation_status_idx')
        ->and($reservationIndexes)->toContain('reservations_listing_kind_status_idx')
        ->and($orderIndexes)
        ->toContain('orders_buyer_status_idx')
        ->toContain('orders_seller_status_idx')
        ->toContain('orders_listing_status_idx')
        ->and($disputeIndexes)->toContain('order_disputes_order_status_idx')
        ->and($reviewIndexes)->toContain('listing_reviews_listing_status_idx');
});

/** @param array<string, mixed> $overrides */
function marketplacePayload(array $overrides = []): array
{
    return [
        'type' => 'sale',
        'category' => 'carriers-travel',
        'seller_type' => 'private',
        'title' => 'Atlas 40 carrier for a cat in good condition',
        'description' => 'A clean carrier with secure locks, complete measurements, original handle, and a disclosed cosmetic scratch on one side.',
        'condition' => 'good',
        'brand' => 'Atlas',
        'model' => '40',
        'material' => 'Plastic and steel',
        'price' => 35,
        'currency' => 'EUR',
        'quantity' => 1,
        'availability' => 'in-stock',
        'species' => ['cat'],
        'pet_size' => 'small',
        'age_group' => 'all',
        'hygiene_status' => 'cleaned',
        'city' => 'Vilnius',
        'area' => 'Naujamiestis',
        'delivery_options' => ['meetup'],
        'meetup_notes' => 'Meet beside the public library during daylight.',
        'return_policy' => 'Inspect during handover; hidden defects remain reportable.',
        'intent' => 'publish',
        'safety_acknowledged' => 1,
        ...$overrides,
    ];
}

/**
 * @param  array<string, mixed>  $orderOverrides
 * @return array{Listing, Reservation, Order}
 */
function acceptedMarketplaceOrder(array $orderOverrides = []): array
{
    $listing = Listing::factory()->create([
        'owner_key' => 'mia-carter',
        'owner_name' => 'Mia Carter',
        'quantity' => 0,
        'availability' => 'out-of-stock',
        'status' => ListingStatus::Reserved,
    ]);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => $orderOverrides['buyer_key'] ?? 'mia-carter',
        'requester_name' => 'Mia Carter',
        'idempotency_key' => (string) Str::uuid(),
        'status' => ReservationStatus::Accepted,
    ]);
    $order = Order::factory()->create([
        'reservation_id' => $reservation->id,
        'buyer_key' => $orderOverrides['buyer_key'] ?? 'mia-carter',
        'seller_key' => $orderOverrides['seller_key'] ?? 'seller-owner',
        'status' => OrderStatus::AwaitingPayment,
        'payment_status' => PaymentStatus::Pending,
    ]);

    return [$listing, $reservation, $order];
}
