<?php

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\ForumReport;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\ListingReport;
use App\Models\Order;
use App\Models\Reservation;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('marketplace filters published listings and never exposes drafts from another owner', function () {
    Listing::factory()->adoption()->create([
        'title' => 'Calm adult cat ready for adoption in Vilnius',
        'city' => 'Vilnius',
        'species' => ['cat'],
    ]);
    Listing::factory()->create([
        'title' => 'Dog walking harness available in Kaunas',
        'city' => 'Kaunas',
        'species' => ['dog'],
    ]);
    Listing::factory()->adoption()->draft()->create([
        'owner_key' => 'another-owner',
        'title' => 'Private draft cat adoption profile',
        'city' => 'Vilnius',
        'species' => ['cat'],
    ]);

    $this->get(route('marketplace.index', [
        'type' => 'adoption',
        'species' => 'cat',
        'city' => 'Vilnius',
    ]))
        ->assertOk()
        ->assertSee('Calm adult cat ready for adoption in Vilnius')
        ->assertDontSee('Dog walking harness available in Kaunas')
        ->assertDontSee('Private draft cat adoption profile')
        ->assertSee('Platform-only contact');
});

test('listing detail explains safety boundaries without exposing an exact home address', function () {
    $listing = Listing::factory()->create([
        'title' => 'Secure travel carrier with complete measurements',
        'city' => 'Vilnius',
        'area' => 'Naujamiestis',
        'meetup_notes' => 'Meet beside the public library during daylight.',
    ]);

    $this->get(route('marketplace.show', $listing))
        ->assertOk()
        ->assertSee('Keep the exchange inside the platform')
        ->assertSee('Never share verification codes')
        ->assertSee('Vilnius')
        ->assertSee('Naujamiestis')
        ->assertDontSee('Home address')
        ->assertDontSee('Phone number');
});

test('listing creation rejects a sale without a price and a paid adoption', function () {
    $this->from(route('marketplace.create'))
        ->post(route('marketplace.store'), listingPayload([
            'type' => 'sale',
            'price' => null,
            'is_free' => 0,
        ]))
        ->assertRedirect(route('marketplace.create'))
        ->assertSessionHasErrors('price');

    $this->from(route('marketplace.create'))
        ->post(route('marketplace.store'), listingPayload([
            'type' => 'adoption',
            'category' => 'adoption',
            'condition' => 'not-applicable',
            'price' => 150,
        ]))
        ->assertRedirect(route('marketplace.create'))
        ->assertSessionHasErrors('price');

    expect(Listing::query()->count())->toBe(0);
});

test('owner can publish a structured listing with an attributable audit entry', function () {
    $response = $this->post(route('marketplace.store'), listingPayload([
        'type' => 'exchange',
        'title' => 'Exchange compact carrier for a larger travel crate',
        'price' => null,
        'exchange_preferences' => 'Looking for a ventilated carrier at least 48 cm long.',
        'species' => ['cat', 'dog'],
        'delivery_options' => ['meetup'],
    ]));

    $listing = Listing::query()->firstOrFail();

    $response->assertRedirect(route('marketplace.show', $listing));

    expect($listing)
        ->owner_key->toBe('mia-carter')
        ->type->toBe(ListingType::Exchange)
        ->status->toBe(ListingStatus::Published)
        ->species->toBe(['cat', 'dog'])
        ->contact_policy->toBe('platform-only')
        ->and($listing->published_at)->not->toBeNull();

    expect(AuditLog::query()
        ->where('action', 'listing.created')
        ->where('target_id', (string) $listing->id)
        ->exists())->toBeTrue();
});

test('reservation requests are idempotent and an owner cannot request their own listing', function () {
    $listing = Listing::factory()->create(['owner_key' => 'another-owner']);
    $key = (string) Str::uuid();
    $payload = listingRequestPayload($key);

    $this->post(route('marketplace.actions', $listing), $payload)->assertRedirect();
    $this->post(route('marketplace.actions', $listing), $payload)->assertRedirect();

    expect(Reservation::query()->count())->toBe(1);
    expect(Reservation::query()->firstOrFail())
        ->requester_key->toBe('mia-carter')
        ->status->toBe(ReservationStatus::Requested);

    $ownListing = Listing::factory()->create(['owner_key' => 'mia-carter']);

    $this->post(route('marketplace.actions', $ownListing), listingRequestPayload((string) Str::uuid()))
        ->assertForbidden();
});

test('accepting one request reserves the listing declines competitors and supports completion', function () {
    $listing = Listing::factory()->create(['owner_key' => 'mia-carter']);
    $accepted = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'noah-williams',
        'requester_name' => 'Noah Williams',
    ]);
    $declined = Reservation::factory()->create([
        'listing_id' => $listing->id,
        'requester_key' => 'lena-petrauskaite',
        'requester_name' => 'Lena Petrauskaite',
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'accept-request',
        'reservation_id' => $accepted->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    expect($listing->refresh())
        ->status->toBe(ListingStatus::Reserved)
        ->and($accepted->refresh()->status)->toBe(ReservationStatus::Accepted)
        ->and($declined->refresh()->status)->toBe(ReservationStatus::Declined);

    $order = Order::query()->where('reservation_id', $accepted->id)->firstOrFail();
    expect($order)
        ->status->toBe(OrderStatus::AwaitingPayment)
        ->payment_status->toBe(PaymentStatus::Pending);

    $order->update([
        'status' => OrderStatus::Confirmed,
        'payment_status' => PaymentStatus::Paid,
        'paid_at' => now(),
    ]);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'mark-complete',
        'reservation_id' => $accepted->id,
    ])->assertRedirect(route('marketplace.show', $listing));

    expect($listing->refresh())
        ->status->toBe(ListingStatus::Completed)
        ->and($accepted->refresh()->status)->toBe(ReservationStatus::Completed)
        ->and($listing->completed_at)->not->toBeNull();
});

test('save is idempotent and animal welfare reports enter the high priority queue', function () {
    $listing = Listing::factory()->create();
    $this->seed(ForumModerationDefinitionSeeder::class);

    $this->post(route('marketplace.actions', $listing), ['action' => 'toggle-save'])->assertRedirect();
    $this->post(route('marketplace.actions', $listing), ['action' => 'toggle-save'])->assertRedirect();

    expect(ListingEngagement::query()->count())->toBe(1)
        ->and(ListingEngagement::query()->firstOrFail()->is_saved)->toBeFalse();

    $this->from(route('marketplace.show', $listing))
        ->post(route('marketplace.actions', $listing), [
            'action' => 'report',
            'reason' => 'animal-welfare',
            'details' => 'The listing appears to show unsafe living conditions.',
        ])
        ->assertRedirect(route('marketplace.show', $listing))
        ->assertSessionHasErrors('truthfulness_confirmed');

    expect(ListingReport::query()->count())->toBe(0)
        ->and(ForumReport::query()->count())->toBe(0);

    $this->post(route('marketplace.actions', $listing), [
        'action' => 'report',
        'reason' => 'animal-welfare',
        'details' => 'The listing appears to show unsafe living conditions.',
        'truthfulness_confirmed' => 1,
    ])->assertRedirect();

    expect(ListingReport::query()->firstOrFail())
        ->priority->toBe('high')
        ->status->toBe('submitted')
        ->and(ForumReport::query()->firstOrFail())
        ->reason->toBe('animal-neglect')
        ->subject_type->toBe(Listing::class)
        ->subject_id->toBe((string) $listing->id)
        ->truthfulness_confirmed->toBeTrue();
});

test('marketplace migrations include directory and reservation indexes', function () {
    $listingIndexes = collect(Schema::getIndexes('listings'))->pluck('name');
    $reservationIndexes = collect(Schema::getIndexes('reservations'))->pluck('name');

    expect($listingIndexes)
        ->toContain('listings_directory_idx')
        ->toContain('listings_category_status_idx')
        ->toContain('listings_owner_status_idx')
        ->and($reservationIndexes)
        ->toContain('reservations_listing_status_idx')
        ->toContain('reservations_requester_status_idx');
});

/** @param array<string, mixed> $overrides */
function listingPayload(array $overrides = []): array
{
    return [
        'type' => 'sale',
        'category' => 'walking-gear',
        'title' => 'Reflective harness for a medium active dog',
        'description' => 'Clean reflective harness with secure buckles, clear measurements, and no damaged stitching. I can share more photos before a public meetup.',
        'condition' => 'good',
        'price' => 24,
        'currency' => 'EUR',
        'is_free' => 0,
        'exchange_preferences' => null,
        'species' => ['dog'],
        'pet_size' => 'medium',
        'city' => 'Vilnius',
        'area' => 'Naujamiestis',
        'delivery_options' => ['meetup', 'shipping'],
        'meetup_notes' => 'Meet beside the public library during daylight.',
        'cover_url' => null,
        'is_business' => 0,
        'business_name' => null,
        'intent' => 'publish',
        'safety_acknowledged' => 1,
        ...$overrides,
    ];
}

/** @return array<string, mixed> */
function listingRequestPayload(string $key): array
{
    return [
        'action' => 'request',
        'idempotency_key' => $key,
        'message' => 'Could we meet near the public library on Saturday afternoon so I can inspect the item?',
        'quantity' => 1,
        'exchange_method' => 'meetup',
        'proposed_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'terms_accepted' => 1,
        'privacy_accepted' => 1,
    ];
}
