<?php

declare(strict_types=1);

use App\Actions\CreateForumEvent;
use App\Actions\CreatePlace;
use App\Actions\GrantPlaceAccess;
use App\Actions\RevealPlaceExactLocation;
use App\Actions\UpdatePlaceLocation;
use App\Data\CreateForumEventData;
use App\Data\CreatePlaceData;
use App\Data\UpdatePlaceLocationData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\OrganizationRole;
use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceAccessPurpose;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Enums\PlaceVisibility;
use App\Livewire\Forum\ForumEventDirectory;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventVersion;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\PlaceLocationVersion;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueArea;
use App\Services\PlaceCatalog;
use App\Services\PlacePublicProjection;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PlaceAuthoritySeeder;
use Database\Seeders\PlaceDemoSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('place authority schema is reversible indexed and encrypts exact location data', function () {
    expect(Schema::hasTable('places'))->toBeTrue()
        ->and(Schema::hasTable('venues'))->toBeTrue()
        ->and(Schema::hasTable('venue_areas'))->toBeTrue()
        ->and(Schema::hasTable('place_access_grants'))->toBeTrue()
        ->and(Schema::hasTable('place_access_audits'))->toBeTrue()
        ->and(Schema::hasTable('place_location_versions'))->toBeTrue()
        ->and(Schema::hasColumns('forum_events', ['place_id', 'venue_id']))->toBeTrue()
        ->and(Schema::hasColumns('forum_event_occurrences', ['place_id', 'venue_id']))->toBeTrue()
        ->and(Schema::hasColumn('forum_event_rooms', 'venue_area_id'))->toBeTrue();

    $place = Place::factory()->private()->create([
        'exact_address' => 'Private foster address 42',
        'exact_latitude' => '54.701234',
        'exact_longitude' => '25.301234',
    ]);
    $raw = DB::table('places')->where('id', $place->id)->first();

    expect((string) $raw->exact_address)->not->toContain('Private foster address 42')
        ->and((string) $raw->exact_latitude)->not->toBe('54.701234')
        ->and($place->toArray())->not->toHaveKeys([
            'exact_address',
            'exact_latitude',
            'exact_longitude',
            'private_instructions',
        ]);
});

test('place creation is idempotent and private facts never enter public projection', function () {
    $data = privatePlaceData('place-create-private-0001');
    $action = app(CreatePlace::class);

    $place = $action->handle($this->authenticatedUser, $data);
    $same = $action->handle($this->authenticatedUser, $data);
    $projection = app(PlacePublicProjection::class)->for($place);

    expect($same->is($place))->toBeTrue()
        ->and(Place::query()->count())->toBe(1)
        ->and($place->normalized_name)->toBe('quiet foster introduction space')
        ->and($projection)->toMatchArray([
            'stable_key' => $place->stable_key,
            'name' => 'Quiet foster introduction space',
            'public_region' => 'Vilnius',
            'visibility' => PlaceVisibility::Private->value,
        ])
        ->and($projection)->not->toHaveKeys([
            'exact_address',
            'exact_latitude',
            'exact_longitude',
            'private_instructions',
        ])
        ->and($projection)->toMatchArray([
            'summary' => null,
            'public_address' => null,
            'public_latitude' => null,
            'public_longitude' => null,
            'transport_information' => null,
            'parking_information' => null,
            'pet_rules' => null,
            'species_rules' => [],
        ]);
});

test('public place scopes exclude private places and preserve factual verification states', function () {
    $public = Place::factory()->public()->verified()->create();
    Place::factory()->private()->create();
    Place::factory()->archived()->create();

    $visible = Place::query()->publiclyDiscoverable()->get();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->is($public))->toBeTrue()
        ->and($public->verification_status)->toBe(PlaceVerificationStatus::Verified)
        ->and($public->accessibility_status)->toBe(PlaceAccessibilityStatus::NotAssessed);
});

test('legacy place presentation consumes canonical public facts without private location leakage', function () {
    Place::factory()->public()->verified()->create([
        'stable_key' => 'vingis-quiet-loop',
        'public_region' => 'Canonical Vilnius region',
        'public_address' => 'Canonical public entrance',
        'public_latitude' => '54.700001',
        'public_longitude' => '25.300001',
        'public_location_precision' => \App\Enums\PlacePublicLocationPrecision::ApproximatePoint,
        'exact_address' => 'Internal operations entrance',
    ]);
    Place::factory()->private()->create([
        'stable_key' => 'bernardine-evening-park',
        'public_region' => 'Approximate private region',
        'exact_address' => 'Protected private address',
    ]);

    $catalog = app(PlaceCatalog::class);
    $public = $catalog->find('vingis-quiet-loop');
    $privateStatic = $catalog->find('bernardine-evening-park');

    expect($public)->toMatchArray([
        'city' => 'Canonical Vilnius region',
        'general_location' => 'Canonical Vilnius region',
        'address' => 'Canonical public entrance',
        'latitude' => 54.700001,
        'longitude' => 25.300001,
    ])
        ->and($public['verification']['label'])->toBe(__('places.verification_statuses.verified'))
        ->and(json_encode($public, JSON_THROW_ON_ERROR))->not->toContain('Internal operations entrance')
        ->and(json_encode($privateStatic, JSON_THROW_ON_ERROR))->not->toContain('Protected private address')
        ->and(json_encode($catalog->all(), JSON_THROW_ON_ERROR))->not->toContain('Protected private address');
});

test('canonical place catalog queries stay bounded as accessible volume grows', function () {
    $organization = Organization::factory()->forOwner($this->authenticatedUser)->create();
    Place::factory()->forOrganization($organization)->create([
        'visibility' => PlaceVisibility::Organization,
    ]);

    $catalogQueryCount = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        app(PlaceCatalog::class)->all();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queryCount;
    };

    $singlePlaceQueries = $catalogQueryCount();

    Place::factory()->count(11)->forOrganization($organization)->create([
        'visibility' => PlaceVisibility::Organization,
    ]);
    $twelvePlaceQueries = $catalogQueryCount();

    expect($singlePlaceQueries)->toBeLessThanOrEqual(4)
        ->and($twelvePlaceQueries)->toBeLessThanOrEqual($singlePlaceQueries + 1);
});

test('venues and areas reuse one place identity with separate people and animal capacities', function () {
    $place = Place::factory()->public()->create();
    $venue = Venue::factory()->for($place)->create([
        'human_capacity' => 120,
        'animal_capacity' => 35,
    ]);
    $area = VenueArea::factory()->for($venue)->quietArea()->create([
        'human_capacity' => 12,
        'animal_capacity' => 6,
    ]);

    expect($venue->place->is($place))->toBeTrue()
        ->and($area->venue->is($venue))->toBeTrue()
        ->and($venue->human_capacity)->toBe(120)
        ->and($venue->animal_capacity)->toBe(35)
        ->and($area->animal_capacity)->toBe(6);
});

test('exact location grants are account bound expiring revocable and every reveal is audited', function () {
    CarbonImmutable::setTestNow('2026-08-03 12:00:00');
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create([
        'exact_address' => 'Approved participant entrance',
        'exact_latitude' => '54.700001',
        'exact_longitude' => '25.300001',
        'private_instructions' => 'Use the side gate after confirmation.',
    ]);
    $recipient = User::factory()->create();
    $stranger = User::factory()->create();
    $event = ForumEvent::factory()->forPlace($place)->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($recipient, 'user')
        ->confirmed()
        ->create();

    $grant = app(GrantPlaceAccess::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        recipient: $recipient,
        purpose: PlaceAccessPurpose::EventAttendance,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: 'place-access-grant-event-0001',
        event: $event,
    );

    $revealed = app(RevealPlaceExactLocation::class)->handle(
        actor: $recipient,
        place: $place,
        channel: 'event-workspace',
    );

    expect($revealed)->toMatchArray([
        'address' => 'Approved participant entrance',
        'latitude' => '54.700001',
        'longitude' => '25.300001',
        'instructions' => 'Use the side gate after confirmation.',
    ])
        ->and(PlaceAccessAudit::query()->where('place_access_grant_id', $grant->id)->count())->toBe(1)
        ->and(fn () => app(RevealPlaceExactLocation::class)->handle(
            actor: $stranger,
            place: $place,
            channel: 'direct-url',
        ))->toThrow(AuthorizationException::class);

    $grant->forceFill(['valid_until' => now()->subSecond()])->save();

    expect(fn () => app(RevealPlaceExactLocation::class)->handle(
        actor: $recipient,
        place: $place,
        channel: 'stale-ticket',
    ))->toThrow(AuthorizationException::class);
});

test('place access idempotency cannot cross place recipient or purpose boundaries', function () {
    $firstPlace = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $secondPlace = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $firstRecipient = User::factory()->create();
    $secondRecipient = User::factory()->create();
    $action = app(GrantPlaceAccess::class);
    $key = 'place-access-boundary-0001';

    $first = $action->handle(
        actor: $this->authenticatedUser,
        place: $firstPlace,
        recipient: $firstRecipient,
        purpose: PlaceAccessPurpose::ProfessionalVisit,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: $key,
    );
    $same = $action->handle(
        actor: $this->authenticatedUser,
        place: $firstPlace,
        recipient: $firstRecipient,
        purpose: PlaceAccessPurpose::ProfessionalVisit,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: $key,
    );

    expect($same->is($first))->toBeTrue()
        ->and(fn () => $action->handle(
            actor: $this->authenticatedUser,
            place: $secondPlace,
            recipient: $secondRecipient,
            purpose: PlaceAccessPurpose::AdoptionMeeting,
            validFrom: now()->subMinute()->toImmutable(),
            validUntil: now()->addHour()->toImmutable(),
            idempotencyKey: $key,
        ))->toThrow(ValidationException::class);
});

test('attendance access cannot publish a private place as an event venue', function () {
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $venue = Venue::factory()->for($place)->create();
    $operator = User::factory()->create();
    $event = ForumEvent::factory()->forPlace($place)->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($operator, 'user')
        ->confirmed()
        ->create();
    $grant = app(GrantPlaceAccess::class);

    $grant->handle(
        actor: $this->authenticatedUser,
        place: $place,
        recipient: $operator,
        purpose: PlaceAccessPurpose::EventAttendance,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: 'place-attendance-cannot-publish-0001',
        event: $event,
    );

    expect($operator->can('viewExactLocation', $place))->toBeTrue()
        ->and($operator->cannot('useForEvent', $place))->toBeTrue()
        ->and(Place::query()->usableForEventsBy($operator)->whereKey($place)->exists())->toBeFalse()
        ->and(fn () => app(CreateForumEvent::class)->handle(
            $operator,
            placeEventData($place, $venue),
        ))->toThrow(AuthorizationException::class);

    $grant->handle(
        actor: $this->authenticatedUser,
        place: $place,
        recipient: $operator,
        purpose: PlaceAccessPurpose::EventOperations,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: 'place-operations-can-publish-0001',
        event: $event,
    );

    expect($operator->can('useForEvent', $place))->toBeTrue()
        ->and(Place::query()->usableForEventsBy($operator)->whereKey($place)->exists())->toBeTrue();
});

test('event attendance access requires the matching confirmed registration', function () {
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create();
    $event = ForumEvent::factory()->forPlace($place)->create();
    $pendingParticipant = User::factory()->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($pendingParticipant, 'user')
        ->pending()
        ->create();

    expect(fn () => app(GrantPlaceAccess::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        recipient: $pendingParticipant,
        purpose: PlaceAccessPurpose::EventAttendance,
        validFrom: now()->subMinute()->toImmutable(),
        validUntil: now()->addHour()->toImmutable(),
        idempotencyKey: 'place-attendance-pending-blocked-0001',
        event: $event,
    ))->toThrow(ValidationException::class)
        ->and(PlaceAccessGrant::query()->count())->toBe(0);
});

test('material location changes preserve encrypted history and revoke stale grants', function () {
    $place = Place::factory()->private()->for($this->authenticatedUser, 'owner')->create([
        'public_region' => 'Vilnius North',
        'exact_address' => 'Old private entrance',
    ]);
    $recipient = User::factory()->create();
    $grant = PlaceAccessGrant::factory()->for($place)->for($recipient, 'user')->active()->create();
    $futureGrant = PlaceAccessGrant::factory()->for($place)->create([
        'valid_from' => now()->addDay(),
        'valid_until' => now()->addDays(2),
    ]);

    app(UpdatePlaceLocation::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        data: new UpdatePlaceLocationData(
            publicRegion: 'Vilnius East',
            publicAddress: null,
            publicLatitude: '54.701000',
            publicLongitude: '25.401000',
            exactAddress: 'New private entrance',
            exactLatitude: '54.701234',
            exactLongitude: '25.401234',
            privateInstructions: 'Use the staffed entrance.',
            reasonCode: 'venue-moved',
        ),
    );

    $grant->refresh();
    $futureGrant->refresh();
    $place->refresh();
    $version = PlaceLocationVersion::query()->whereBelongsTo($place)->sole();

    expect($place->public_region)->toBe('Vilnius East')
        ->and($place->exact_address)->toBe('New private entrance')
        ->and($grant->revoked_at)->not->toBeNull()
        ->and($grant->revocation_reason_code)->toBe('location-changed')
        ->and($futureGrant->revoked_at)->not->toBeNull()
        ->and($version->exact_address)->toBe('Old private entrance')
        ->and((string) DB::table('place_location_versions')->where('id', $version->id)->value('exact_address'))
        ->not->toContain('Old private entrance');
});

test('an event cannot attach a venue from another canonical place', function () {
    $place = Place::factory()->public()->create();
    $otherVenue = Venue::factory()->create();

    expect(fn () => app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        placeEventData($place, $otherVenue),
    ))->toThrow(ValidationException::class);
});

test('event builder lists only accessible places and never carries exact location state', function () {
    $public = Place::factory()->public()->create(['name' => 'Public riverside venue']);
    Venue::factory()->for($public)->create(['timezone' => 'Europe/Vilnius']);
    Place::factory()->unlisted()->create(['name' => 'Foreign review candidate']);
    Place::factory()->private()->create([
        'name' => 'Foreign private foster address',
        'exact_address' => 'Never render this address',
    ]);

    Livewire::actingAs($this->authenticatedUser)
        ->test(ForumEventDirectory::class)
        ->assertSee('Public riverside venue')
        ->assertDontSee('Foreign review candidate')
        ->assertDontSee('Foreign private foster address')
        ->assertDontSee('Never render this address')
        ->assertDontSeeHtml('wire:model="form.exactLocation"')
        ->set('form.placeId', $public->id)
        ->assertSet('form.locationScope', '')
        ->assertSee('Europe/Vilnius');
});

test('unlisted place candidates remain scoped to their owner until publication', function () {
    $owner = User::factory()->create();
    $candidate = Place::factory()->unlisted()->for($owner, 'owner')->create();

    expect(Place::query()->accessibleTo($this->authenticatedUser)->whereKey($candidate)->exists())
        ->toBeFalse()
        ->and(Place::query()->usableForEventsBy($this->authenticatedUser)->whereKey($candidate)->exists())
        ->toBeFalse()
        ->and($this->authenticatedUser->cannot('view', $candidate))->toBeTrue()
        ->and($this->authenticatedUser->cannot('useForEvent', $candidate))->toBeTrue()
        ->and(Place::query()->accessibleTo($owner)->whereKey($candidate)->exists())->toBeTrue()
        ->and(Place::query()->usableForEventsBy($owner)->whereKey($candidate)->exists())->toBeTrue()
        ->and($owner->can('useForEvent', $candidate))->toBeTrue();
});

test('the existing add place flow cannot bypass the reviewed submission aggregate', function () {
    $payload = [
        'action' => 'create-place',
        'title' => 'Riverside safety park',
        'body' => 'A public park with water and a clearly marked quiet walking loop.',
        'category' => 'park',
        'city' => 'Vilnius Riverside',
        'place_address' => 'Public entrance, River Street 10',
        'rules' => 'Leashes are required beside the cycle path.',
        'place_relationship' => 'visitor',
    ];

    $this->actingAs($this->authenticatedUser)
        ->post(route('actions.perform'), $payload)
        ->assertRedirect(route('places.submissions.create'));

    expect(Place::query()->count())->toBe(0);

    $this->actingAs($this->authenticatedUser)
        ->post(route('actions.perform'), $payload)
        ->assertRedirect();

    expect(Place::query()->count())->toBe(0);
});

test('place demo seeding is integrated production guarded and idempotent', function () {
    $this->seed(DatabaseSeeder::class);
    $firstCounts = [
        'places' => Place::query()->count(),
        'venues' => Venue::query()->count(),
        'areas' => VenueArea::query()->count(),
        'grants' => PlaceAccessGrant::query()->count(),
        'audits' => PlaceAccessAudit::query()->count(),
        'versions' => PlaceLocationVersion::query()->count(),
    ];

    $this->seed(PlaceAuthoritySeeder::class);

    expect($firstCounts['places'])->toBeGreaterThanOrEqual(14)
        ->and($firstCounts['venues'])->toBeGreaterThanOrEqual(10)
        ->and($firstCounts['areas'])->toBeGreaterThanOrEqual(10)
        ->and($firstCounts['grants'])->toBeGreaterThanOrEqual(10)
        ->and($firstCounts['audits'])->toBeGreaterThanOrEqual(10)
        ->and($firstCounts['versions'])->toBeGreaterThanOrEqual(10);

    expect([
        'places' => Place::query()->count(),
        'venues' => Venue::query()->count(),
        'areas' => VenueArea::query()->count(),
        'grants' => PlaceAccessGrant::query()->count(),
        'audits' => PlaceAccessAudit::query()->count(),
        'versions' => PlaceLocationVersion::query()->count(),
    ])->toBe($firstCounts);

    $private = Place::query()->where('stable_key', 'demo-place-protected-foster')->sole();
    $event = ForumEvent::query()
        ->where('stable_key', 'demo-point13-controlled-introduction')
        ->sole();
    $version = ForumEventVersion::query()
        ->whereBelongsTo($event, 'event')
        ->where('reason_code', 'canonical-place-linked')
        ->sole();

    expect($private->visibility)->toBe(PlaceVisibility::Private)
        ->and((string) DB::table('places')->where('id', $private->id)->value('exact_address'))
        ->not->toContain('Protected foster entrance')
        ->and($event->place_id)->toBe($private->id)
        ->and($event->exact_location)->toBeNull()
        ->and($event->occurrences()->where('place_id', $private->id)->exists())->toBeTrue()
        ->and($version->snapshot['place_id'])->toBe($private->id);

    config(['platform.demo_seed_environments' => []]);

    expect(fn () => $this->seed(PlaceAuthoritySeeder::class))
        ->toThrow(LogicException::class)
        ->and(fn () => $this->seed(PlaceDemoSeeder::class))
        ->toThrow(LogicException::class);
});

test('events reference canonical places and venues without copying an exact address', function () {
    $place = Place::factory()->public()->for($this->authenticatedUser, 'owner')->create([
        'public_region' => 'Vilnius Riverside',
        'exact_address' => 'Staff entrance, Gate 3',
    ]);
    $venue = Venue::factory()->for($place)->create();

    $event = app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        placeEventData($place, $venue),
    );
    $event->load('occurrences');

    expect($event->place_id)->toBe($place->id)
        ->and($event->venue_id)->toBe($venue->id)
        ->and($event->location_scope)->toBe('Vilnius Riverside')
        ->and($event->exact_location)->toBeNull()
        ->and($event->occurrences)->toHaveCount(1)
        ->and($event->occurrences->first()->place_id)->toBe($place->id)
        ->and($event->occurrences->first()->venue_id)->toBe($venue->id);
});

test('organization place authority does not survive membership removal', function () {
    $organization = Organization::factory()->for($this->authenticatedUser, 'owner')->create();
    $manager = User::factory()->create();
    $membership = OrganizationMembership::factory()
        ->for($organization)
        ->for($manager, 'user')
        ->create(['role' => OrganizationRole::EventManager]);
    $place = Place::factory()->private()->for($organization)->create();

    expect($manager->can('update', $place))->toBeTrue();

    $membership->forceFill([
        'status' => 'removed',
        'removed_at' => now(),
    ])->save();

    expect($manager->fresh()->cannot('update', $place))->toBeTrue()
        ->and(fn () => app(RevealPlaceExactLocation::class)->handle(
            actor: $manager->fresh(),
            place: $place,
            channel: 'former-staff-session',
        ))->toThrow(AuthorizationException::class);
});

test('organization place visibility includes current members without granting management access', function () {
    $organization = Organization::factory()->create();
    $member = User::factory()->create();
    OrganizationMembership::factory()
        ->for($organization)
        ->for($member, 'user')
        ->create(['role' => OrganizationRole::Member]);
    $organizationPlace = Place::factory()->for($organization)->create([
        'visibility' => PlaceVisibility::Organization,
    ]);
    $privatePlace = Place::factory()->private()->for($organization)->create();

    $visibleIds = Place::query()->accessibleTo($member)->pluck('id');

    expect($visibleIds)->toContain($organizationPlace->id)
        ->not->toContain($privatePlace->id)
        ->and($member->can('view', $organizationPlace))->toBeTrue()
        ->and($member->cannot('update', $organizationPlace))->toBeTrue();
});

test('place authority factories expose explicit privacy verification and relationship states', function () {
    $private = Place::factory()->private()->create();
    $organization = Organization::factory()->create();
    $venue = Venue::factory()->forOrganization($organization)->create();
    $grant = PlaceAccessGrant::factory()->for($private)->active()->create();

    expect($private->visibility)->toBe(PlaceVisibility::Private)
        ->and($venue->organization->is($organization))->toBeTrue()
        ->and($grant->isActive())->toBeTrue();
});

function privatePlaceData(string $idempotencyKey): CreatePlaceData
{
    return new CreatePlaceData(
        name: 'Quiet foster introduction space',
        type: PlaceType::PrivateTrainingSpace,
        visibility: PlaceVisibility::Private,
        publicRegion: 'Vilnius',
        publicAddress: null,
        exactAddress: 'Private foster address 42',
        publicLatitude: '54.700000',
        publicLongitude: '25.300000',
        exactLatitude: '54.700123',
        exactLongitude: '25.300123',
        locale: 'en',
        idempotencyKey: $idempotencyKey,
        summary: 'A controlled private location for approved introductions.',
        privateInstructions: 'Reveal only to approved handlers.',
        petRules: 'Controlled introductions only.',
    );
}

function placeEventData(Place $place, Venue $venue): CreateForumEventData
{
    return new CreateForumEventData(
        title: 'Canonical venue workshop',
        summary: 'A safe workshop using one canonical venue identity.',
        type: ForumEventType::Workshop,
        visibility: ForumEventVisibility::Public,
        format: ForumEventFormat::Physical,
        startsAt: now()->addWeek()->toImmutable(),
        endsAt: now()->addWeek()->addHours(2)->toImmutable(),
        timezone: 'Europe/Vilnius',
        capacity: 20,
        registrationPolicy: ForumEventRegistrationPolicy::Open,
        waitlistEnabled: true,
        locationScope: null,
        exactLocation: null,
        onlineUrl: null,
        attendanceRequirements: null,
        vaccinationRequirements: null,
        vaccinationJurisdiction: null,
        minimumAnimalAgeMonths: null,
        maximumAnimalAgeMonths: null,
        accessibilityInformation: 'Step-free entrance information supplied by the venue.',
        costMinor: 0,
        currency: 'EUR',
        refundPolicy: null,
        photoConsentMode: ForumEventPhotoConsent::AskFirst,
        animalWelfareRules: 'Handlers may stop participation whenever animal welfare requires it.',
        emergencyContactPlan: 'The safety lead coordinates urgent action and evacuation.',
        groupId: null,
        taxonIds: [],
        locale: 'en',
        idempotencyKey: 'canonical-place-event-create-0001',
        placeId: $place->id,
        venueId: $venue->id,
    );
}
