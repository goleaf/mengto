<?php

declare(strict_types=1);

use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Models\DomesticClassification;
use App\Models\ForumReport;
use App\Models\PetProfile;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchContactRelay;
use App\Models\SearchReport;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\FindSearchCaseDuplicates;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Database\Seeders\SearchCaseIntegritySeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

test('a new search case links only owned animal and valid taxonomy context', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
        'name' => 'Birch',
    ]);
    $taxon = Taxon::factory()->create();
    TaxonVersion::factory()->for($taxon)->create([
        'is_active_version' => true,
        'scientific_name' => 'Canis lupus familiaris',
        'canonical_name' => 'Canis lupus familiaris',
    ]);
    $classification = DomesticClassification::factory()->for($taxon)->create([
        'canonical_name' => 'Labrador mix',
    ]);

    $this->post(route('lost-found.store'), integritySearchCasePayload([
        'pet_profile_id' => $pet->id,
        'taxon_id' => $taxon->id,
        'domestic_classification_id' => $classification->id,
        'reward_offered' => 1,
        'reward_summary' => 'A documented EUR 250 reward is available through the protected relay.',
    ]))->assertRedirect();

    $searchCase = SearchCase::query()->firstOrFail();

    expect($searchCase)
        ->owner_id->toBe($this->authenticatedUser->id)
        ->pet_profile_id->toBe($pet->id)
        ->pet_profile_key->toBe('pet-scout')
        ->taxon_id->toBe($taxon->id)
        ->domestic_classification_id->toBe($classification->id)
        ->reward_offered->toBeTrue()
        ->requires_taxonomy_review->toBeFalse()
        ->temperament->toBe('Usually friendly but may hide after loud noises.')
        ->and($searchCase->animal_snapshot['pet_profile_key'])->toBe('pet-scout')
        ->and($searchCase->animal_snapshot['temperament'])->toBe('Usually friendly but may hide after loud noises.')
        ->and($searchCase->accessories)->toBe(['blue collar'])
        ->and((string) $searchCase->getRawOriginal('animal_snapshot'))->not->toContain('Birch')
        ->and($searchCase->events()->where('event_type', 'case-created')->count())->toBe(1);

    $this->get(route('lost-found.show', $searchCase))
        ->assertOk()
        ->assertSee('Canis lupus familiaris')
        ->assertSee('Labrador mix')
        ->assertSee('Usually friendly but may hide after loud noises.')
        ->assertSee('blue collar')
        ->assertSee('EUR 250');
});

test('another users animal profile and mismatched classification are rejected', function () {
    $otherPet = PetProfile::factory()->create();
    $taxon = Taxon::factory()->create();
    $otherTaxon = Taxon::factory()->create();
    $classification = DomesticClassification::factory()->for($otherTaxon)->create();

    $this->from(route('lost-found.create'))
        ->post(route('lost-found.store'), integritySearchCasePayload([
            'pet_profile_id' => $otherPet->id,
            'taxon_id' => $taxon->id,
            'domestic_classification_id' => $classification->id,
        ]))
        ->assertRedirect(route('lost-found.create'))
        ->assertSessionHasErrors(['pet_profile_id', 'domestic_classification_id']);

    expect(SearchCase::query()->count())->toBe(0);
});

test('search case creation rejects an unowned legacy animal profile key', function () {
    PetProfile::factory()->create([
        'profile_key' => 'pet-foreign',
        'slug' => 'foreign-profile',
    ]);

    $this->from(route('lost-found.create'))
        ->post(route('lost-found.store'), integritySearchCasePayload([
            'pet_profile_key' => 'foreign-profile',
            'pet_profile_id' => null,
        ]))
        ->assertRedirect(route('lost-found.create'))
        ->assertSessionHasErrors('pet_profile_key');

    expect(SearchCase::query()->count())->toBe(0);
});

test('search case creation rejects reward payment instructions', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
    ]);

    $this->from(route('lost-found.create'))
        ->post(route('lost-found.store'), integritySearchCasePayload([
            'pet_profile_id' => $pet->id,
            'reward_offered' => 1,
            'reward_summary' => 'Transfer money to a gift card before receiving the location.',
        ]))
        ->assertRedirect(route('lost-found.create'))
        ->assertSessionHasErrors('reward_summary');

    expect(SearchCase::query()->count())->toBe(0);
});

test('search case editor uses owned profiles and a singular taxonomy selector', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
        'name' => 'Birch',
        'species' => 'Dog',
        'breed' => 'Labrador mix',
    ]);
    $otherPet = PetProfile::factory()->create([
        'name' => 'Private foreign profile',
    ]);

    $this->get(route('lost-found.create', ['pet' => $pet->slug]))
        ->assertOk()
        ->assertSeeHtml('name="pet_profile_id"')
        ->assertSeeHtml('value="'.$pet->id.'" selected')
        ->assertSee('Birch')
        ->assertDontSee($otherPet->name)
        ->assertSeeHtml('wire:name="forum.animal-taxonomy-selector"')
        ->assertSee('Animal taxonomy')
        ->assertSee('A reward is available')
        ->assertDontSee('54.683400');
});

test('protected contact relay is encrypted idempotent and private', function () {
    $owner = User::factory()->create();
    $searchCase = SearchCase::factory()
        ->for($owner, 'owner')
        ->create([
            'owner_key' => $owner->actor_key,
            'coordinator_key' => $owner->actor_key,
        ]);
    $key = (string) Str::uuid();
    $message = 'I saw the animal beside the private clinic entrance at 18:20.';
    $payload = [
        'idempotency_key' => $key,
        'purpose' => 'sighting',
        'message' => $message,
    ];

    $this->post(route('lost-found.contact.store', $searchCase), $payload)->assertRedirect();
    $this->post(route('lost-found.contact.store', $searchCase), $payload)->assertRedirect();

    $relay = SearchContactRelay::query()->firstOrFail();

    expect(SearchContactRelay::query()->count())->toBe(1)
        ->and($relay->recipient_user_id)->toBe($owner->id)
        ->and($relay->sender_user_id)->toBe($this->authenticatedUser->id)
        ->and($relay->message)->toBe($message)
        ->and((string) $relay->getRawOriginal('message'))->not->toContain('private clinic')
        ->and($searchCase->events()->where('event_type', 'contact-relay-submitted')->count())->toBe(1);

    $this->get(route('lost-found.show', $searchCase))
        ->assertOk()
        ->assertDontSee('private clinic');

    $this->actingAs($owner)
        ->get(route('lost-found.coordinate', $searchCase))
        ->assertOk()
        ->assertSee('private clinic');
});

test('case owner cannot use the protected relay to contact themselves', function () {
    $searchCase = SearchCase::factory()
        ->for($this->authenticatedUser, 'owner')
        ->create([
            'owner_key' => $this->authenticatedUser->actor_key,
            'coordinator_key' => $this->authenticatedUser->actor_key,
        ]);

    $this->post(route('lost-found.contact.store', $searchCase), [
        'idempotency_key' => (string) Str::uuid(),
        'purpose' => 'other',
        'message' => 'This message should never be accepted as a self-contact relay.',
    ])->assertForbidden();

    expect(SearchContactRelay::query()->count())->toBe(0);
});

test('reunion is version checked and records append only history', function () {
    $searchCase = SearchCase::factory()
        ->for($this->authenticatedUser, 'owner')
        ->create([
            'owner_key' => $this->authenticatedUser->actor_key,
            'coordinator_key' => $this->authenticatedUser->actor_key,
        ]);

    $this->post(route('lost-found.actions', $searchCase), [
        'action' => 'update-status',
        'status' => SearchStatus::Reunited->value,
        'status_note' => 'The microchip and private identifying mark were confirmed.',
        'return_confirmed' => 1,
        'lock_version' => 1,
    ])->assertRedirect(route('lost-found.show', $searchCase));

    $searchCase->refresh();
    $event = $searchCase->events()->where('event_type', 'status-changed')->firstOrFail();

    expect($searchCase)
        ->status->toBe(SearchStatus::Reunited)
        ->lock_version->toBe(2)
        ->reunited_confirmed_by_user_id->toBe($this->authenticatedUser->id)
        ->reunited_at->not->toBeNull()
        ->closed_at->not->toBeNull()
        ->and($event->previous_status)->toBe(SearchStatus::Active->value)
        ->and($event->current_status)->toBe(SearchStatus::Reunited->value);

    $this->from(route('lost-found.coordinate', $searchCase))
        ->post(route('lost-found.actions', $searchCase), [
            'action' => 'update-status',
            'status' => SearchStatus::Active->value,
            'lock_version' => 1,
        ])
        ->assertSessionHasErrors('lock_version');

    expect(fn () => $event->update(['current_status' => SearchStatus::Active->value]))
        ->toThrow(LogicException::class, 'append-only');
});

test('a closed case can be archived privately without deleting its operational history', function () {
    $owner = $this->authenticatedUser;
    $searchCase = SearchCase::factory()
        ->returned()
        ->for($owner, 'owner')
        ->create([
            'owner_key' => $owner->actor_key,
            'coordinator_key' => $owner->actor_key,
        ]);
    $alert = SearchAlert::factory()->for($searchCase)->create(['status' => 'sent']);
    $task = SearchTask::factory()->for($searchCase)->create();
    $volunteer = SearchVolunteer::factory()->for($searchCase)->create();
    $sighting = Sighting::factory()->for($searchCase)->create();
    $update = SearchUpdate::factory()->for($searchCase)->create();

    $this->post(route('lost-found.actions', $searchCase), [
        'action' => 'archive-case',
        'archive_confirmed' => 1,
        'lock_version' => 1,
    ])->assertRedirect(route('lost-found.coordinate', $searchCase));

    $searchCase->refresh();

    expect($searchCase)
        ->archived_at->not->toBeNull()
        ->alerts_active->toBeFalse()
        ->volunteer_join_open->toBeFalse()
        ->lock_version->toBe(2)
        ->and($searchCase->events()->where('event_type', 'case-archived')->count())->toBe(1)
        ->and($alert->refresh()->status)->toBe('stopped')
        ->and($task->refresh()->status->value)->toBe('cancelled')
        ->and($volunteer->refresh()->status->value)->toBe('left')
        ->and($sighting->fresh())->not->toBeNull()
        ->and($update->fresh())->not->toBeNull();

    $this->get(route('lost-found.coordinate', $searchCase))
        ->assertOk()
        ->assertSee(__('lost_found.events.case_archived'));

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('lost-found.show', $searchCase))
        ->assertForbidden();
    $this->get(route('lost-found.poster', $searchCase))
        ->assertForbidden();
    $this->get(route('lost-found.index'))
        ->assertOk()
        ->assertDontSee($searchCase->public_code);
});

test('an active case cannot be archived', function () {
    $searchCase = SearchCase::factory()
        ->for($this->authenticatedUser, 'owner')
        ->create([
            'owner_key' => $this->authenticatedUser->actor_key,
            'coordinator_key' => $this->authenticatedUser->actor_key,
        ]);

    $this->from(route('lost-found.coordinate', $searchCase))
        ->post(route('lost-found.actions', $searchCase), [
            'action' => 'archive-case',
            'archive_confirmed' => 1,
            'lock_version' => 1,
        ])
        ->assertRedirect(route('lost-found.coordinate', $searchCase))
        ->assertSessionHasErrors('action');

    expect($searchCase->refresh()->archived_at)->toBeNull();
});

test('lost animal reports bridge to unified moderation without exposing evidence', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $owner = User::factory()->create();
    $searchCase = SearchCase::factory()
        ->for($owner, 'owner')
        ->create(['owner_key' => $owner->actor_key]);

    $this->post(route('lost-found.reports.store', $searchCase), [
        'reason' => 'reward-scam',
        'details' => 'The sender requested a gift card before sharing a claimed location.',
        'truthfulness_confirmed' => 1,
        'immediate_safety' => 1,
    ])->assertRedirect(route('lost-found.show', $searchCase));

    $searchReport = SearchReport::query()->firstOrFail();
    $forumReport = ForumReport::query()->firstOrFail();

    expect($searchReport)
        ->forum_report_id->toBe($forumReport->id)
        ->reason->toBe('reward-scam')
        ->priority->toBe('high')
        ->and($forumReport)
        ->subject_type->toBe(SearchCase::class)
        ->subject_id->toBe((string) $searchCase->id)
        ->priority->toBe('critical')
        ->truthfulness_confirmed->toBeTrue();

    $this->get(route('lost-found.show', $searchCase))
        ->assertOk()
        ->assertDontSee('gift card');
});

test('duplicate matching remains bounded advisory and does not merge cases', function () {
    $matching = SearchCase::factory()->create([
        'species' => 'dog',
        'city' => 'Vilnius',
        'breed' => 'Labrador mix',
        'primary_color' => 'Black with a white chest',
        'last_seen_area' => 'Vingis Park western path',
        'distinctive_marks' => 'White chest patch and folded ear',
    ]);
    SearchCase::factory()->create([
        'species' => 'cat',
        'city' => 'Kaunas',
        'breed' => 'Domestic shorthair',
    ]);

    $matches = app(FindSearchCaseDuplicates::class)->handle([
        'species' => 'dog',
        'city' => 'Vilnius',
        'breed' => 'Labrador mix',
        'primary_color' => 'Black with a white chest',
        'last_seen_area' => 'Vingis Park western path',
        'distinctive_marks' => 'White chest patch and folded ear',
        'last_seen_at' => now()->toDateTimeString(),
    ]);

    expect($matches)->toHaveCount(1)
        ->and($matches->first()->is($matching))->toBeTrue()
        ->and($matching->refresh()->duplicate_of_search_case_id)->toBeNull();
});

test('integrity seeder is repeatable and only links exact owner and pet matches', function () {
    $owner = User::factory()->create(['actor_key' => 'legacy-owner']);
    $pet = PetProfile::factory()->for($owner)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
    ]);
    $searchCase = SearchCase::factory()->create([
        'owner_id' => null,
        'owner_key' => 'legacy-owner',
        'pet_profile_id' => null,
        'pet_profile_key' => 'scout',
        'animal_snapshot' => null,
    ]);
    $seeder = app(SearchCaseIntegritySeeder::class);

    $seeder->run();
    $seeder->run();

    expect($searchCase->refresh())
        ->owner_id->toBe($owner->id)
        ->pet_profile_id->toBe($pet->id)
        ->animal_snapshot->not->toBeNull()
        ->and($searchCase->events()->where('event_type', 'case-created')->count())->toBe(1);
});

test('lost and found factories cover sighted stolen and reunited states', function () {
    $sighted = SearchCase::factory()->sighted()->create();
    $stolen = SearchCase::factory()->stolen()->create();
    $reunited = SearchCase::factory()->reunited()->create();
    $relay = SearchContactRelay::factory()->create();

    expect($sighted)
        ->type->toBe(SearchCaseType::Sighted)
        ->requires_taxonomy_review->toBeTrue()
        ->and($stolen->type)->toBe(SearchCaseType::Stolen)
        ->and($reunited)
        ->status->toBe(SearchStatus::Reunited)
        ->reunited_at->not->toBeNull()
        ->and($relay->recipient_user_id)->toBe($relay->searchCase->owner_id);
});

test('lost and found integrity schema has ownership history and relay indexes', function () {
    $caseIndexes = collect(Schema::getIndexes('search_cases'))->pluck('name');
    $eventIndexes = collect(Schema::getIndexes('search_case_events'))->pluck('name');
    $relayIndexes = collect(Schema::getIndexes('search_contact_relays'))->pluck('name');

    expect($caseIndexes)
        ->toContain('search_cases_pet_status_idx')
        ->toContain('search_cases_taxon_status_seen_idx')
        ->toContain('search_cases_duplicate_status_idx')
        ->toContain('search_cases_reunion_user_status_idx')
        ->and($eventIndexes)
        ->toContain('search_case_events_case_created_idx')
        ->toContain('search_case_events_actor_created_idx')
        ->and($relayIndexes)
        ->toContain('search_contact_relays_case_status_idx')
        ->toContain('search_contact_relays_recipient_status_idx')
        ->toContain('search_contact_relays_sender_status_idx');
});

test('lost and found public detail query count stays bounded as history grows', function () {
    $searchCase = SearchCase::factory()->create();
    Sighting::factory()->count(40)->confirmed()->for($searchCase)->create();
    SearchUpdate::factory()->count(40)->for($searchCase)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = $this->get(route('lost-found.show', $searchCase));
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();
    expect($queryCount)->toBeLessThanOrEqual(13);
});

test('guest cannot submit protected contact or moderation reports', function () {
    $searchCase = SearchCase::factory()->for(User::factory(), 'owner')->create();
    $this->app['auth']->guard()->logout();

    $this->post(route('lost-found.contact.store', $searchCase), [
        'idempotency_key' => (string) Str::uuid(),
        'purpose' => 'other',
        'message' => 'A guest must authenticate before sending a protected message.',
    ])->assertRedirect(route('login'));

    $this->post(route('lost-found.reports.store', $searchCase), [
        'reason' => 'other',
        'truthfulness_confirmed' => 1,
    ])->assertRedirect(route('login'));
});

/** @param array<string, mixed> $overrides */
function integritySearchCasePayload(array $overrides = []): array
{
    return [
        'type' => SearchCaseType::Lost->value,
        'intent' => 'publish',
        'pet_profile_key' => 'scout',
        'pet_name' => 'Birch',
        'species' => 'dog',
        'breed' => 'Labrador mix',
        'sex' => 'male',
        'age_label' => '4 years',
        'size' => 'large',
        'primary_color' => 'Black with a white chest',
        'coat' => 'short',
        'distinctive_marks' => 'White chest patch and one folded ear.',
        'hidden_marks' => 'Crescent scar below the left shoulder.',
        'description' => 'Birch slipped out of his harness after a loud sound and may keep distance from strangers.',
        'health_notice' => 'Needs regular medication; contact the owner promptly.',
        'approach_instructions' => 'Stay sideways, speak quietly, and send the location.',
        'avoid_instructions' => 'Do not chase, surround, shout, or enter unsafe areas.',
        'accessories' => ['blue collar'],
        'temperament' => 'Usually friendly but may hide after loud noises.',
        'microchip_status' => 'present',
        'last_seen_area' => 'Vingis Park, western river path',
        'city' => 'Vilnius',
        'country' => 'LT',
        'latitude' => 54.683412,
        'longitude' => 25.237481,
        'location_note' => 'Bench beside the southern gate',
        'direction' => 'East toward the river',
        'last_seen_at' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
        'notification_radius_km' => 5,
        'visibility' => 'public',
        'contact_channel' => 'platform',
        'contact_value' => null,
        'safety_acknowledged' => 1,
        ...$overrides,
    ];
}
