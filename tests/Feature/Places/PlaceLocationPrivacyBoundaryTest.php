<?php

declare(strict_types=1);

use App\Actions\RevealPlaceExactLocation;
use App\Actions\RevokePlaceAccess;
use App\Actions\UpdatePlaceLocation;
use App\Data\PlaceExactLocationRevealContext;
use App\Data\UpdatePlaceLocationData;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\PlaceAccessPurpose;
use App\Enums\PlacePublicLocationPrecision;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use App\Services\PlacePublicProjection;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;

test('place location updates persist only a deliberate three decimal public approximation', function (): void {
    $place = Place::factory()->for($this->authenticatedUser, 'owner')->create();

    $updated = app(UpdatePlaceLocation::class)->handle(
        $this->authenticatedUser,
        $place,
        new UpdatePlaceLocationData(
            publicRegion: 'Vilnius region',
            publicAddress: 'Approximate public entrance',
            publicLatitude: '54.701234',
            publicLongitude: '25.301234',
            exactAddress: 'Private operations entrance',
            exactLatitude: '54.701987',
            exactLongitude: '25.301987',
            privateInstructions: 'Use the locked side gate.',
            reasonCode: 'manager-location-update',
            publicLocationPrecision: PlacePublicLocationPrecision::ApproximatePoint,
        ),
    );

    expect($updated->public_location_precision)->toBe(PlacePublicLocationPrecision::ApproximatePoint)
        ->and($updated->public_latitude)->toBe('54.701000')
        ->and($updated->public_longitude)->toBe('25.301000');

    $projection = app(PlacePublicProjection::class)->for($updated);
    $serialized = json_encode($projection, JSON_THROW_ON_ERROR);

    expect($serialized)
        ->not->toContain('54.701987')
        ->not->toContain('25.301987')
        ->not->toContain('Private operations entrance')
        ->not->toContain('locked side gate');
});

test('region only places do not serialize a coordinate fallback into member HTML', function (): void {
    $place = Place::factory()->public()->create([
        'stable_key' => 'region-only-place',
        'slug' => 'region-only-place',
        'public_location_precision' => PlacePublicLocationPrecision::Region,
        'public_latitude' => null,
        'public_longitude' => null,
        'exact_latitude' => '54.777777',
        'exact_longitude' => '25.888888',
        'exact_address' => 'Never render this exact address',
    ]);

    $response = $this->actingAs($this->authenticatedUser)
        ->get(route('places.show', ['place' => $place->slug, 'tab' => 'map']));

    $response->assertSuccessful()
        ->assertDontSee('54.777777', false)
        ->assertDontSee('25.888888', false)
        ->assertDontSee('Never render this exact address', false)
        ->assertDontSee('54.6872', false)
        ->assertDontSee('openstreetmap.org/directions', false);
});

test('event attendance exact location reveal fails after registration eligibility is lost', function (): void {
    $recipient = User::factory()->create();
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $event = ForumEvent::factory()->for($this->authenticatedUser, 'organizer')->create([
        'place_id' => $place->id,
    ]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($recipient, 'user')
        ->create([
        'status' => ForumEventRegistrationStatus::Cancelled,
    ]);
    $grant = PlaceAccessGrant::factory()->for($place)->for($recipient)->create([
        'event_id' => $event->id,
        'purpose' => PlaceAccessPurpose::EventAttendance,
        'valid_from' => now()->subMinute(),
        'valid_until' => now()->addHour(),
    ]);

    $context = new PlaceExactLocationRevealContext(
        PlaceAccessPurpose::EventAttendance,
        $event->id,
        'event-attendance-screen',
    );

    expect(fn () => app(RevealPlaceExactLocation::class)->handle($recipient, $place, $context))
        ->toThrow(AuthorizationException::class)
        ->and($registration->fresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled)
        ->and(PlaceAccessAudit::query()->where('place_access_grant_id', $grant->id)->count())->toBe(0);
});

test('place access revocation is idempotent and immediately prevents exact reveal', function (): void {
    $recipient = User::factory()->create();
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $grant = PlaceAccessGrant::factory()->for($place)->for($recipient)->active()->create();
    $key = 'place-grant-revoke-000000000001';

    $action = app(RevokePlaceAccess::class);
    $revoked = $action->handle($this->authenticatedUser, $place, $grant, 'manager-revoked', $key);
    $replayed = $action->handle($this->authenticatedUser, $place, $grant, 'manager-revoked', $key);

    expect($replayed->is($revoked))->toBeTrue()
        ->and($revoked->revoked_at)->not->toBeNull()
        ->and(PlaceAccessAudit::query()
            ->where('place_access_grant_id', $grant->id)
            ->where('event_type', 'exact-location-access-revoked')
            ->count())->toBe(1)
        ->and(fn () => app(RevealPlaceExactLocation::class)->handle(
            $recipient,
            $place,
            new PlaceExactLocationRevealContext($grant->purpose, $grant->event_id, 'place-detail'),
        ))->toThrow(AuthorizationException::class);
});
