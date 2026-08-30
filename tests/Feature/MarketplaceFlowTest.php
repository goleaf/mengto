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
use App\Models\User;
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

test('listing money inputs require canonical decimal precision', function (string $field, string $value) {
    $this->from(route('marketplace.create'))
        ->post(route('marketplace.store'), marketplacePayload([
            'type' => 'rental',
            'price' => '12.37',
            'deposit_amount' => '20.00',
            'rental_rate_unit' => 'day',
            'available_from' => now()->addDay()->toDateString(),
            'available_until' => now()->addWeek()->toDateString(),
            'minimum_days' => 1,
            'maximum_days' => 7,
            $field => $value,
        ]))
        ->assertRedirect(route('marketplace.create'))
        ->assertSessionHasErrors($field);

    expect(Listing::query()->count())->toBe(0);
})->with([
    'deposit precision' => ['deposit_amount', '1.234'],
    'deposit exponent' => ['deposit_amount', '1e2'],
    'price precision' => ['price', '1.234'],
    'price exponent' => ['price', '1e2'],
]);

test('request offers require canonical decimal precision', function (string $offeredPrice) {
    $listing = Listing::factory()->create([
        'owner_key' => 'different-marketplace-owner',
        'quantity' => 2,
    ]);

    $this->from(route('marketplace.show', $listing))
        ->post(route('marketplace.actions', $listing), [
            'action' => 'request',
            'idempotency_key' => (string) Str::uuid(),
            'message' => 'I can collect this item at the agreed public meeting place tomorrow.',
            'quantity' => 1,
            'offered_price' => $offeredPrice,
            'exchange_method' => 'meetup',
            'terms_accepted' => 1,
            'privacy_accepted' => 1,
        ])
        ->assertRedirect(route('marketplace.show', $listing))
        ->assertSessionHasErrors('offered_price');

    expect(Reservation::query()->count())->toBe(0);
})->with(['1.234', '1e2']);

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
        'quantity' => 5,
        'price' => '12.37',
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
        'quantity' => 2,
        'rental_starts_at' => now()->addDays(2)->startOfDay(),
        'rental_ends_at' => now()->addDays(4)->startOfDay(),
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'accept-request',
        'reservation_id' => $reservation->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    $order = Order::query()->where('reservation_id', $reservation->id)->firstOrFail();

    expect($listing->refresh())
        ->quantity->toBe(3)
        ->status->toBe(ListingStatus::Published)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Accepted)
        ->and($order)
        ->order_kind->toBe('rental')
        ->status->toBe(OrderStatus::AwaitingPayment)
        ->payment_status->toBe(PaymentStatus::Pending)
        ->total_amount->toBe('69.48')
        ->and(data_get($order->item_snapshot, 'title'))->toBe($listing->title)
        ->and(data_get($order->terms_snapshot, 'return_policy'))->toBe('Return clean before 18:00.')
        ->and(data_get($order->terms_snapshot, 'rental_days'))->toBe(2);

    $capturedTitle = data_get($order->item_snapshot, 'title');
    $listing->update(['title' => 'Edited public title']);

    expect(data_get($order->fresh()->item_snapshot, 'title'))->toBe($capturedTitle);
});

test('validated rental money is normalized before exact order creation', function () {
    $this->post(route('marketplace.store'), marketplacePayload([
        'type' => 'rental',
        'title' => 'Adjustable rehabilitation ramp for a two-day rental',
        'price' => '12.37',
        'quantity' => 5,
        'deposit_amount' => '20.5',
        'rental_rate_unit' => 'day',
        'available_from' => now()->addDay()->toDateString(),
        'available_until' => now()->addMonth()->toDateString(),
        'minimum_days' => 1,
        'maximum_days' => 14,
    ]))->assertRedirect();

    $listing = Listing::query()->firstOrFail();
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'noah-williams',
        'requester_name' => 'Noah Williams',
        'request_kind' => 'rental',
        'quantity' => 2,
        'rental_starts_at' => now()->addDays(2)->startOfDay(),
        'rental_ends_at' => now()->addDays(4)->startOfDay(),
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'accept-request',
        'reservation_id' => $reservation->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    expect($listing->price)->toBe('12.37')
        ->and(data_get($listing->attributes, 'deposit_amount'))->toBe('20.50')
        ->and(Order::query()->where('reservation_id', $reservation->id)->firstOrFail()->total_amount)
        ->toBe('69.98');
});

test('accepting a request rejects totals wider than the order decimal boundary', function () {
    $listing = Listing::factory()->create([
        'owner_key' => 'mia-carter',
        'quantity' => 100000,
        'price' => '999999.99',
    ]);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'noah-williams',
        'quantity' => 100000,
    ]);

    $this->from(route('marketplace.show', $listing))
        ->post(route('marketplace.actions', $listing), [
            'action' => 'accept-request',
            'reservation_id' => $reservation->id,
        ])
        ->assertRedirect(route('marketplace.show', $listing))
        ->assertSessionHasErrors('quantity');

    expect($listing->refresh()->quantity)->toBe(100000)
        ->and($reservation->refresh()->status)->toBe(ReservationStatus::Requested)
        ->and(Order::query()->where('reservation_id', $reservation->id)->exists())->toBeFalse();
});

test('accepting a request permits an exact total immediately below the order boundary', function () {
    $listing = Listing::factory()->create([
        'owner_key' => 'mia-carter',
        'quantity' => 100,
        'price' => '999999.99',
    ]);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'noah-williams',
        'quantity' => 100,
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'accept-request',
        'reservation_id' => $reservation->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    expect(Order::query()->where('reservation_id', $reservation->id)->firstOrFail()->total_amount)
        ->toBe('99999999.00');
});

test('paid marketplace orders cannot be completed before protected payment', function () {
    [$listing, $reservation, $order] = acceptedMarketplaceOrder(
        buyerKey: 'marketplace-payment-buyer',
        sellerKey: 'mia-carter',
    );

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
    [$listing, $reservation] = acceptedMarketplaceOrder(
        buyerKey: 'mia-carter',
        sellerKey: 'marketplace-cancellation-seller',
    );
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
    [$listing, $reservation, $order] = acceptedMarketplaceOrder(
        buyerKey: 'another-buyer',
        sellerKey: 'another-seller',
    );

    $this->get(route('marketplace.orders.show', [$listing, $order]))->assertForbidden();

    $this->actingAs(User::query()->where('actor_key', 'another-buyer')->firstOrFail());
    $order->update(['payment_status' => PaymentStatus::Paid]);

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
    [$listing, $reservation, $order] = acceptedMarketplaceOrder(
        buyerKey: 'mia-carter',
        sellerKey: 'marketplace-review-seller',
    );
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
        'photos' => [UploadedFile::fake()->image('carrier.png', 3200, 2000)],
        'video' => UploadedFile::fake()->create('locks.mp4', 1024, 'video/mp4'),
    ]));

    $listing = Listing::query()->firstOrFail();
    $response->assertRedirect(route('marketplace.show', $listing));

    expect($listing->gallery)->toHaveCount(1)
        ->and($listing->video_url)->not->toBeNull();

    $photoPath = portalMediaPath($listing->gallery[0]);
    $photoSize = getimagesizefromstring(Storage::disk('public')->get($photoPath));

    Storage::disk('public')->assertExists($photoPath);
    Storage::disk('public')->assertExists(portalMediaPath((string) $listing->video_url));
    expect($photoPath)->toEndWith('.webp')
        ->and($photoSize)->not->toBeFalse()
        ->and($photoSize[0])->toBe(2560)
        ->and($photoSize[1])->toBe(1600)
        ->and($photoSize['mime'])->toBe('image/webp');

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
 * @return array{Listing, Reservation, Order}
 */
function acceptedMarketplaceOrder(string $buyerKey, string $sellerKey): array
{
    if ($buyerKey === $sellerKey) {
        throw new LogicException('Marketplace order fixtures require distinct buyer and seller identities.');
    }

    $buyer = User::query()->where('actor_key', $buyerKey)->first()
        ?? User::factory()->create(['actor_key' => $buyerKey]);
    $seller = User::query()->where('actor_key', $sellerKey)->first()
        ?? User::factory()->create(['actor_key' => $sellerKey]);
    $listing = Listing::factory()->create([
        'owner_id' => $seller->id,
        'owner_key' => $seller->actor_key,
        'owner_name' => $seller->name,
        'quantity' => 0,
        'availability' => 'out-of-stock',
        'status' => ListingStatus::Reserved,
    ]);
    $reservation = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_id' => $buyer->id,
        'requester_key' => $buyer->actor_key,
        'requester_name' => $buyer->name,
        'idempotency_key' => (string) Str::uuid(),
        'status' => ReservationStatus::Accepted,
    ]);
    $order = Order::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => OrderStatus::AwaitingPayment,
        'payment_status' => PaymentStatus::Pending,
    ]);

    expect($buyer->is($seller))->toBeFalse()
        ->and($reservation->requester_id)->toBe($buyer->id)
        ->and($reservation->requester_key)->toBe($buyer->actor_key)
        ->and($order->buyer_id)->toBe($reservation->requester_id)
        ->and($order->buyer_key)->toBe($reservation->requester_key)
        ->and($order->seller_id)->toBe($listing->owner_id)
        ->and($order->seller_key)->toBe($listing->owner_key);

    return [$listing, $reservation, $order];
}
