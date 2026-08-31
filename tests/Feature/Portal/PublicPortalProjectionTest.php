<?php

declare(strict_types=1);

use App\Enums\OrganizationStatus;
use App\Enums\PlaceStatus;
use App\Models\ForumEvent;
use App\Models\Organization;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('the explicit guest product allowlist renders only public projection routes', function (
    string $routeName,
    Closure $parameters,
): void {
    auth()->logout();

    $this->get(route($routeName, $parameters()))
        ->assertSuccessful()
        ->assertSee('data-public-shell', false);
})->with([
    'member profile' => ['members.show', function (): array {
        $member = User::factory()->create();
        $actor = SocialActor::factory()->forUser($member)->create();

        return ['socialActor' => $actor->actor_key];
    }],
    'pet profile' => ['pets.profile', function (): array {
        $pet = PetProfile::factory()->discoverable()->create();

        return ['petProfile' => $pet->profile_key];
    }],
    'organization profile' => ['organizations.show', function (): array {
        $organization = Organization::factory()->verified()->create();

        return ['organization' => $organization->slug];
    }],
    'place profile' => ['places.show', function (): array {
        $place = Place::factory()->public()->create();

        return ['place' => $place->slug];
    }],
    'event profile' => ['meetups.show', function (): array {
        $event = ForumEvent::factory()->published()->create();

        return ['event' => $event->stable_key];
    }],
]);

test('guest projections omit private person pet organization place and event fields', function (): void {
    auth()->logout();
    $member = User::factory()->create([
        'name' => 'Public Member',
        'email' => 'private-member@example.test',
    ]);
    $actor = SocialActor::factory()->forUser($member)->create([
        'actor_key' => 'public-member',
    ]);
    $pet = PetProfile::factory()->discoverable()->create([
        'name' => 'Public Pet',
        'profile_data' => [
            'story' => 'A public story.',
            'location' => 'Exact private home location',
            'medical_note' => 'Private medical fact',
        ],
    ]);
    $organization = Organization::factory()->verified()->create([
        'name' => 'Public Organization',
        'summary' => 'A public organization summary.',
        'metadata' => ['internal_note' => 'Private organization note'],
    ]);
    $place = Place::factory()->public()->create([
        'name' => 'Public Place',
        'public_region' => 'Vilnius region',
        'public_address' => 'Public exact address that guests must not receive',
        'exact_address' => 'Private operational entrance',
        'private_instructions' => 'Private place instructions',
    ]);
    $event = ForumEvent::factory()->published()->paid()->create([
        'title' => 'Public Event',
        'location_scope' => 'Vilnius region',
        'exact_location' => 'Private event meeting point',
        'online_url' => 'https://private.example.test/event',
        'attendance_requirements' => 'Private participant requirement',
        'vaccination_requirements' => 'Private vaccination fact',
        'emergency_contact_plan' => 'Private incident plan',
    ]);

    $this->get(route('members.show', $actor))
        ->assertSee('Public Member')
        ->assertDontSee('private-member@example.test');
    $this->get(route('pets.profile', $pet))
        ->assertSee('Public Pet')
        ->assertDontSee('Exact private home location')
        ->assertDontSee('Private medical fact');
    $this->get(route('organizations.show', $organization))
        ->assertSee('Public Organization')
        ->assertDontSee('Private organization note');
    $this->get(route('places.show', $place))
        ->assertSee('Public Place')
        ->assertSee('Vilnius region')
        ->assertDontSee('Public exact address that guests must not receive')
        ->assertDontSee('Private operational entrance')
        ->assertDontSee('Private place instructions');
    $this->get(route('meetups.show', $event))
        ->assertSee('Public Event')
        ->assertSee('Vilnius region')
        ->assertDontSee('Private event meeting point')
        ->assertDontSee('https://private.example.test/event')
        ->assertDontSee('Private participant requirement')
        ->assertDontSee('Private vaccination fact')
        ->assertDontSee('Private incident plan')
        ->assertDontSee('ticket', escape: false);
});

test('public archive states are truthful and never reopen private actions', function (): void {
    auth()->logout();
    $completed = ForumEvent::factory()->completed()->create([
        'title' => 'Completed Public Event',
        'location_scope' => 'Vilnius region',
    ]);
    $archivedEvent = ForumEvent::factory()->archived()->create();
    $archivedOrganization = Organization::factory()->create([
        'status' => OrganizationStatus::Archived,
        'archived_at' => now(),
    ]);
    $archivedPlace = Place::factory()->archived()->create([
        'status' => PlaceStatus::Archived,
    ]);

    $this->get(route('meetups.show', $completed))
        ->assertSuccessful()
        ->assertSee('Completed Public Event')
        ->assertSee('data-public-state="archived"', false)
        ->assertDontSee('data-public-action="register"', false);
    $this->get(route('meetups.show', $archivedEvent))->assertNotFound();
    $this->get(route('organizations.show', $archivedOrganization))->assertNotFound();
    $this->get(route('places.show', $archivedPlace))->assertNotFound();
});

test('public projections have bounded queries and expose canonical metadata', function (): void {
    auth()->logout();
    $organization = Organization::factory()->verified()->create();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = $this->get(route('organizations.show', $organization));
    $queryCount = count(DB::getQueryLog());

    $response
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="'.route('organizations.show', $organization).'">', false)
        ->assertSee('name="robots" content="index,follow"', false);
    expect($queryCount)->toBeLessThanOrEqual(4);
});

test('unsafe and non get compatibility requests remain outside the guest allowlist', function (): void {
    auth()->logout();

    $this->post(route('actions.perform'), [])->assertRedirect(route('login'));
});
