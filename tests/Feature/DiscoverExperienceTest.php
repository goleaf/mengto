<?php

declare(strict_types=1);

use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use App\Enums\SocialRelationshipType;
use App\Enums\UserStatus;
use App\Models\DiscoveryPreference;
use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\SocialAccountBlock;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Services\SocialActorResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** @return array{event: ForumEvent, group: ForumGroup, place: Place, expert: ExpertProfile, pet: PetProfile} */
function createDiscoveryWorld(): array
{
    $eventOwner = User::factory()->create();
    $groupOwner = User::factory()->create();
    $placeOwner = User::factory()->create();
    $expertOwner = User::factory()->create();
    $petOwner = User::factory()->create();

    return [
        'event' => ForumEvent::factory()->for($eventOwner, 'organizer')->create([
            'owner_user_id' => $eventOwner->id,
            'title' => 'Discovery river walk',
            'summary' => 'A calm public walk with water and planned rest points.',
            'location_scope' => 'Vilnius Riverside',
            'exact_location' => 'Private gate code 4412',
        ]),
        'group' => ForumGroup::factory()->for($groupOwner, 'owner')->create([
            'name' => 'Discovery quiet walk community',
            'description' => 'A public community for low-pressure local walks.',
            'location_scope' => 'Vilnius',
        ]),
        'place' => Place::factory()->for($placeOwner, 'owner')->public()->create([
            'name' => 'Discovery pet care park',
            'summary' => 'A public park with shade and water access.',
            'public_region' => 'Vilnius',
            'exact_address' => 'Private service entrance 19',
        ]),
        'expert' => ExpertProfile::factory()->create([
            'owner_id' => $expertOwner->id,
            'owner_key' => $expertOwner->actor_key,
            'public_name' => 'Discovery Care Specialist',
            'slug' => 'discovery-care-specialist',
            'headline' => 'Calm handling and preventive care support',
        ]),
        'pet' => PetProfile::factory()->for($petOwner)->create([
            'name' => 'Discovery Maple',
            'species' => 'dog',
            'breed' => 'Mixed breed',
            'published_at' => now(),
        ]),
    ];
}

test('discover is a database backed recommendation hub with canonical destinations', function () {
    $world = createDiscoveryWorld();

    $response = $this->get(route('discover.index'));
    $xpath = responseXPath($response);

    $response
        ->assertOk()
        ->assertSee('Find what matters for life with your pets')
        ->assertSee('Why it appears:')
        ->assertSee($world['event']->title)
        ->assertSee($world['group']->name)
        ->assertSee($world['place']->name)
        ->assertSee($world['expert']->public_name)
        ->assertSee($world['pet']->name)
        ->assertSee(route('meetups.show', $world['event']), false)
        ->assertSee(route('forum.groups.show', $world['group']), false)
        ->assertSee(route('places.show', $world['place']), false)
        ->assertSee(route('experts.show', $world['expert']), false)
        ->assertSee(route('pets.profile', $world['pet']), false)
        ->assertDontSee('Calm weekend walks')
        ->assertDontSee('Trending nearby')
        ->assertDontSee('Private gate code 4412')
        ->assertDontSee('Private service entrance 19');

    expect($xpath->query('//main')->length)->toBe(1)
        ->and($xpath->query('//main//h1')->length)->toBe(1)
        ->and($xpath->query('//*[@data-section="discover-directions"]')->length)->toBe(1)
        ->and($xpath->query('//*[@data-discovery-section]')->length)->toBe(5)
        ->and($xpath->query('//article[@data-discover-result]')->length)->toBe(5);
});

test('discover search and categories use validated url state', function () {
    $world = createDiscoveryWorld();

    $this->get(route('discover.index', ['q' => 'river walk']))
        ->assertOk()
        ->assertSee($world['event']->title)
        ->assertDontSee($world['place']->name);

    $this->get(route('discover.index', ['category' => DiscoveryCategory::Groups->value]))
        ->assertOk()
        ->assertSee($world['group']->name)
        ->assertDontSee($world['event']->title)
        ->assertViewHas('activeCategory', DiscoveryCategory::Groups->value);

    $this->from(route('discover.index'))
        ->get(route('discover.index', ['category' => 'people-nearby']))
        ->assertRedirect(route('discover.index'))
        ->assertSessionHasErrors('category');
});

test('discover excludes private unlisted blocked and non recommendable records', function () {
    $world = createDiscoveryWorld();
    ForumEvent::factory()->invitationOnly()->create(['title' => 'Private discovery event']);
    ForumEvent::factory()->unlisted()->create(['title' => 'Unlisted discovery event']);
    ForumGroup::factory()->private()->create(['name' => 'Private discovery group']);
    Place::factory()->private()->create(['name' => 'Private discovery place']);
    PetProfile::factory()->privateProfile()->create(['name' => 'Private discovery pet']);

    $blockedOwner = User::factory()->create();
    ForumEvent::factory()->for($blockedOwner, 'organizer')->create([
        'owner_user_id' => $blockedOwner->id,
        'title' => 'Blocked owner discovery event',
    ]);
    ForumGroup::factory()->for($blockedOwner, 'owner')->create([
        'name' => 'Blocked owner discovery group',
    ]);
    Place::factory()->for($blockedOwner, 'owner')->public()->create([
        'name' => 'Blocked owner discovery place',
    ]);
    ExpertProfile::factory()->create([
        'owner_id' => $blockedOwner->id,
        'owner_key' => $blockedOwner->actor_key,
        'slug' => 'blocked-owner-discovery-expert',
        'public_name' => 'Blocked Owner Discovery Expert',
    ]);
    PetProfile::factory()->for($blockedOwner)->create([
        'name' => 'Blocked owner discovery pet',
        'published_at' => now(),
    ]);
    SocialAccountBlock::factory()->create([
        'blocker_user_id' => $this->authenticatedUser->id,
        'blocked_user_id' => $blockedOwner->id,
        'created_by_user_id' => $this->authenticatedUser->id,
    ]);

    $mutedPet = PetProfile::factory()->create([
        'name' => 'Recommendation disabled pet',
        'published_at' => now(),
    ]);
    app(SocialActorResolver::class)
        ->forPet($mutedPet)
        ->settings()
        ->update(['is_recommendable' => false]);

    $actorBlockedPet = PetProfile::factory()->create([
        'name' => 'Actor blocked discovery pet',
        'published_at' => now(),
    ]);
    $viewerActor = app(SocialActorResolver::class)->forUser($this->authenticatedUser);
    $blockedPetActor = app(SocialActorResolver::class)->forPet($actorBlockedPet);
    SocialRelationship::factory()->create([
        'source_actor_id' => $viewerActor->id,
        'target_actor_id' => $blockedPetActor->id,
        'relationship_type' => SocialRelationshipType::Block,
        'created_by_user_id' => $this->authenticatedUser->id,
    ]);

    $response = $this->get(route('discover.index'));

    $response
        ->assertOk()
        ->assertSee($world['event']->title)
        ->assertDontSee('Private discovery event')
        ->assertDontSee('Unlisted discovery event')
        ->assertDontSee('Private discovery group')
        ->assertDontSee('Private discovery place')
        ->assertDontSee('Private discovery pet')
        ->assertDontSee('Blocked owner discovery event')
        ->assertDontSee('Blocked owner discovery group')
        ->assertDontSee('Blocked owner discovery place')
        ->assertDontSee('Blocked Owner Discovery Expert')
        ->assertDontSee('Blocked owner discovery pet')
        ->assertDontSee('Recommendation disabled pet')
        ->assertDontSee('Actor blocked discovery pet');
});

test('users can hide recommendations and categories then reset their choices', function () {
    $event = ForumEvent::factory()->create(['title' => 'Hideable discovery event']);

    $this->post(route('discover.preferences.store'), [
        'action' => 'hide',
        'scope' => DiscoveryPreferenceScope::Item->value,
        'category' => DiscoveryCategory::Events->value,
        'target_key' => $event->stable_key,
        'reason_code' => 'not_relevant',
        'return_category' => DiscoveryCategory::Events->value,
    ])->assertRedirect(route('discover.index', ['category' => DiscoveryCategory::Events->value]));

    $this->assertDatabaseHas('discovery_preferences', [
        'user_id' => $this->authenticatedUser->id,
        'scope' => DiscoveryPreferenceScope::Item->value,
        'category' => DiscoveryCategory::Events->value,
        'target_key' => $event->stable_key,
    ]);
    $this->get(route('discover.index'))->assertDontSee($event->title);

    $this->post(route('discover.preferences.store'), [
        'action' => 'hide',
        'scope' => DiscoveryPreferenceScope::Category->value,
        'category' => DiscoveryCategory::Events->value,
        'reason_code' => 'not_interested',
        'return_category' => DiscoveryCategory::Events->value,
    ])->assertRedirect(route('discover.index', ['category' => DiscoveryCategory::Events->value]));

    $this->get(route('discover.index', ['category' => DiscoveryCategory::Events->value]))
        ->assertOk()
        ->assertSee('This category is hidden');

    $this->post(route('discover.preferences.store'), [
        'action' => 'reset',
        'return_category' => DiscoveryCategory::Events->value,
    ])->assertRedirect(route('discover.index', ['category' => DiscoveryCategory::Events->value]));

    expect(DiscoveryPreference::query()->forUser($this->authenticatedUser)->count())->toBe(0);
    $this->get(route('discover.index'))->assertSee($event->title);
});

test('discovery preference policies isolate users and reject inactive accounts', function () {
    $preference = DiscoveryPreference::factory()->for($this->authenticatedUser)->create();
    $otherUser = User::factory()->create();

    expect(Gate::forUser($this->authenticatedUser)->allows('delete', $preference))->toBeTrue()
        ->and(Gate::forUser($otherUser)->allows('delete', $preference))->toBeFalse();

    $inactiveUser = User::factory()->create(['status' => UserStatus::Suspended]);
    expect(Gate::forUser($inactiveUser)->allows('create', DiscoveryPreference::class))->toBeFalse();

    $this->actingAs($inactiveUser)
        ->post(route('discover.preferences.store'), [
            'action' => 'reset',
        ])
        ->assertRedirect();

    expect(DiscoveryPreference::query()->forUser($inactiveUser)->count())->toBe(0);
});

test('discover renders its critical workflow in every supported locale', function (string $locale, string $heading) {
    $this->authenticatedUser->update(['locale' => $locale]);

    $this->get(route('discover.index'))
        ->assertOk()
        ->assertSee($heading)
        ->assertDontSee('discovery.page.', false)
        ->assertDontSee('discovery.actions.', false);
})->with([
    'English' => ['en', 'Find what matters for life with your pets'],
    'Lithuanian' => ['lt', 'Raskite tai, kas svarbu gyvenant su augintiniais'],
    'Russian' => ['ru', 'Находите важное для жизни с питомцами'],
]);

test('discover query count remains bounded as every catalog grows', function () {
    createDiscoveryWorld();

    $renderQueryCount = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('discover.index'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $baseline = $renderQueryCount();

    ForumEvent::factory()->count(12)->create();
    ForumGroup::factory()->count(12)->create();
    Place::factory()->count(12)->public()->create();
    ExpertProfile::factory()->count(12)->create();
    PetProfile::factory()->count(12)->create(['published_at' => now()]);

    $grown = $renderQueryCount();

    expect($baseline)->toBeLessThanOrEqual(14)
        ->and($grown)->toBeLessThanOrEqual($baseline + 1);
});
