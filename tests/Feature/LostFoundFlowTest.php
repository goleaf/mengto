<?php

use App\Actions\PerformSearchAction;
use App\Enums\SearchStatus;
use App\Enums\SearchTaskStatus;
use App\Enums\SearchVolunteerStatus;
use App\Models\AuditLog;
use App\Models\PetProfile;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchReport;
use App\Models\SearchTask;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use App\Services\QrCodeGenerator;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Database\Seeders\SearchSeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('a missing pet report stores only generalized public coordinates', function () {
    Storage::fake('public');
    PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
    ]);

    $this->post(route('lost-found.store'), searchCasePayload())
        ->assertRedirect();

    $searchCase = SearchCase::query()->firstOrFail();
    $rawExactLocation = (string) $searchCase->getRawOriginal('exact_location');

    expect($searchCase)
        ->owner_key->toBe('mia-carter')
        ->active_key->toBe('mia-carter:pet-scout')
        ->public_latitude->toBe('54.683000')
        ->public_longitude->toBe('25.237000')
        ->alerts_active->toBeTrue()
        ->and($searchCase->exact_location['latitude'])->toBe(54.683412)
        ->and($rawExactLocation)->not->toContain('54.683412')
        ->and(SearchAlert::query()->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'search-case.created')->count())->toBe(1);
});

test('public report never exposes exact location hidden marks or contact', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'another-owner',
        'coordinator_key' => 'another-owner',
        'hidden_marks' => 'Private crescent scar',
        'exact_location' => [
            'latitude' => 54.612345,
            'longitude' => 25.298765,
            'note' => 'Private courtyard address',
        ],
        'contact_details' => ['channel' => 'phone', 'value' => '+37060000000'],
    ]);

    $this->get(route('lost-found.show', $searchCase))
        ->assertOk()
        ->assertSee($searchCase->pet_name)
        ->assertSee('Generalized locations')
        ->assertDontSee('Private crescent scar')
        ->assertDontSee('Private courtyard address')
        ->assertDontSee('+37060000000')
        ->assertDontSee('54.612345');

    $this->get(route('lost-found.coordinate', $searchCase))->assertForbidden();
});

test('one pet cannot have two active searches', function () {
    PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
    ]);

    $this->post(route('lost-found.store'), searchCasePayload())
        ->assertRedirect();

    $this->from(route('lost-found.create'))
        ->post(route('lost-found.store'), searchCasePayload([
            'last_seen_at' => now()->subHour()->format('Y-m-d H:i:s'),
        ]))
        ->assertRedirect(route('lost-found.create'))
        ->assertSessionHasErrors('pet_profile_key');

    expect(SearchCase::query()->where('active_key', 'mia-carter:pet-scout')->count())->toBe(1);
});

test('sighting submissions are idempotent and retain actual observation time', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'another-owner',
        'coordinator_key' => 'another-owner',
    ]);
    $key = (string) Str::uuid();
    $observedAt = now()->subMinutes(42)->startOfSecond();
    $payload = sightingPayload($key, [
        'observed_at' => $observedAt->format('Y-m-d H:i:s'),
    ]);

    $this->post(route('lost-found.sightings.store', $searchCase), $payload)->assertRedirect();
    $this->post(route('lost-found.sightings.store', $searchCase), $payload)->assertRedirect();

    $sighting = Sighting::query()->firstOrFail();

    expect(Sighting::query()->count())->toBe(1)
        ->and($sighting->observed_at->equalTo($observedAt))->toBeTrue()
        ->and($sighting->submitted_at->greaterThan($sighting->observed_at))->toBeTrue()
        ->and($sighting->public_latitude)->toBe('54.684000')
        ->and((string) $sighting->getRawOriginal('exact_location'))->not->toContain('54.683812')
        ->and($searchCase->refresh()->status)->toBe(SearchStatus::PossibleSighting);
});

test('closed searches reject new sightings', function () {
    $searchCase = SearchCase::factory()->returned()->create();

    $this->post(
        route('lost-found.sightings.store', $searchCase),
        sightingPayload((string) Str::uuid()),
    )->assertForbidden();

    expect(Sighting::query()->count())->toBe(0);
});

test('only the owner or coordinator can operate the private workspace', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'another-owner',
        'coordinator_key' => 'another-coordinator',
    ]);

    $this->get(route('lost-found.coordinate', $searchCase))->assertForbidden();
    $this->post(route('lost-found.actions', $searchCase), [
        'action' => 'publish-update',
        'update_title' => 'Unauthorized update',
    ])->assertForbidden();
});

test('a volunteer task can only be claimed once', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'another-owner',
        'coordinator_key' => 'another-owner',
    ]);
    $task = SearchTask::factory()->create(['search_case_id' => $searchCase->id]);

    $this->post(route('lost-found.actions', $searchCase), [
        'action' => 'claim-task',
        'task_id' => $task->id,
    ])->assertRedirect();

    $this->from(route('lost-found.show', $searchCase))
        ->post(route('lost-found.actions', $searchCase), [
            'action' => 'claim-task',
            'task_id' => $task->id,
        ])
        ->assertRedirect(route('lost-found.show', $searchCase))
        ->assertSessionHasErrors('task_id');

    expect($task->refresh())
        ->status->toBe(SearchTaskStatus::Claimed)
        ->assignee_key->toBe('mia-carter')
        ->version->toBe(2);
});

test('returning a pet stops alerts tasks and temporary volunteer access', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'mia-carter',
        'coordinator_key' => 'mia-carter',
    ]);
    $alert = SearchAlert::factory()->create(['search_case_id' => $searchCase->id]);
    $task = SearchTask::factory()->create(['search_case_id' => $searchCase->id]);
    $volunteer = SearchVolunteer::factory()->create(['search_case_id' => $searchCase->id]);

    $this->post(route('lost-found.actions', $searchCase), [
        'action' => 'update-status',
        'status' => 'returned',
        'status_note' => 'Chip and hidden mark confirmed at the clinic.',
        'return_confirmed' => 1,
    ])->assertRedirect(route('lost-found.show', $searchCase));

    expect($searchCase->refresh())
        ->status->toBe(SearchStatus::Returned)
        ->active_key->toBeNull()
        ->alerts_active->toBeFalse()
        ->volunteer_join_open->toBeFalse()
        ->returned_at->not->toBeNull()
        ->and($alert->refresh()->status)->toBe('stopped')
        ->and($task->refresh()->status)->toBe(SearchTaskStatus::Cancelled)
        ->and($volunteer->refresh()->status)->toBe(SearchVolunteerStatus::Left)
        ->and(AuditLog::query()->where('action', 'search-case.status-changed')->count())->toBe(1);

    $this->get(route('lost-found.poster', $searchCase))
        ->assertOk()
        ->assertSee('Found')
        ->assertSee('Current status');
});

test('reactivating a closed search restores the unique active pet key', function () {
    $first = SearchCase::factory()->returned()->create([
        'owner_key' => 'mia-carter',
        'coordinator_key' => 'mia-carter',
        'pet_profile_key' => 'scout',
    ]);
    $second = SearchCase::factory()->returned()->create([
        'owner_key' => 'mia-carter',
        'coordinator_key' => 'mia-carter',
        'pet_profile_key' => 'scout',
    ]);

    $this->post(route('lost-found.actions', $first), [
        'action' => 'update-status',
        'status' => 'active',
        'status_note' => 'A new verified sighting reopened the search.',
    ])->assertRedirect(route('lost-found.show', $first));

    expect($first->refresh())
        ->active_key->toBe('mia-carter:scout')
        ->alerts_active->toBeTrue()
        ->volunteer_join_open->toBeTrue()
        ->and($first->alerts()->where('kind', 'search-reactivated')->exists())->toBeTrue();

    expect(fn () => app(PerformSearchAction::class)->handle($second, [
        'action' => 'update-status',
        'status' => 'active',
    ]))->toThrow(ValidationException::class, 'This pet already has another active search.');

    expect(SearchCase::query()->where('active_key', 'mia-carter:scout')->count())->toBe(1);
});

test('danger and extortion reports receive high priority', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $searchCase = SearchCase::factory()->create();

    $this->post(route('lost-found.reports.store', $searchCase), [
        'reason' => 'threat',
        'details' => 'The sender is demanding payment and threatening the animal.',
        'truthfulness_confirmed' => 1,
    ])->assertRedirect(route('lost-found.show', $searchCase));

    expect(SearchReport::query()->firstOrFail())
        ->priority->toBe('high')
        ->reporter_key->toBe('mia-carter');
});

test('directory filters use structured report fields', function () {
    $dog = SearchCase::factory()->create([
        'pet_name' => 'Scout Directory Match',
        'species' => 'dog',
        'city' => 'Vilnius',
    ]);
    SearchCase::factory()->found()->create([
        'pet_name' => 'Found Cat Outside Filter',
        'species' => 'cat',
        'city' => 'Kaunas',
    ]);

    $this->get(route('lost-found.index', [
        'type' => 'lost',
        'species' => 'dog',
        'city' => 'Vilnius',
    ]))
        ->assertOk()
        ->assertSee($dog->pet_name)
        ->assertDontSee('Found Cat Outside Filter');
});

test('catalog create public coordination and poster screens render', function () {
    $searchCase = SearchCase::factory()->create([
        'owner_key' => 'mia-carter',
        'coordinator_key' => 'mia-carter',
        'hidden_marks' => 'Coordinator-only mark',
    ]);
    Sighting::factory()->create(['search_case_id' => $searchCase->id]);
    SearchTask::factory()->create(['search_case_id' => $searchCase->id]);
    SearchVolunteer::factory()->create(['search_case_id' => $searchCase->id]);
    SearchAlert::factory()->create(['search_case_id' => $searchCase->id]);

    $this->get(route('lost-found.index'))
        ->assertOk()
        ->assertSee('Active local searches');
    $this->get(route('lost-found.create'))
        ->assertOk()
        ->assertSee('Report a missing or found animal');
    $this->get(route('lost-found.show', $searchCase))
        ->assertOk()
        ->assertSee($searchCase->pet_name);
    $this->get(route('lost-found.coordinate', $searchCase))
        ->assertOk()
        ->assertSee('Private coordination workspace')
        ->assertSee('Coordinator-only mark');
    $this->get(route('lost-found.poster', $searchCase))
        ->assertOk()
        ->assertSee('Scan for current status');
});

test('search seeder is idempotent and builds coordinated demo cases', function () {
    $seeder = app(SearchSeeder::class);

    $seeder->run();
    $seeder->run();

    $scout = SearchCase::query()->where('slug', 'scout-missing-vingis-park')->firstOrFail();

    expect(SearchCase::query()->whereIn('slug', [
        'scout-missing-vingis-park',
        'found-tabby-naujamiestis',
        'kesha-long-term-search',
    ])->count())->toBe(3)
        ->and($scout->sightings()->count())->toBe(2)
        ->and($scout->sectors()->count())->toBe(2)
        ->and($scout->tasks()->count())->toBe(2)
        ->and($scout->volunteers()->count())->toBe(2)
        ->and($scout->alerts()->count())->toBe(1);
});

test('poster qr code is rendered as an svg data uri', function () {
    $dataUri = app(QrCodeGenerator::class)->dataUri('https://mengto.test/lost-found/example');
    $svg = base64_decode(str($dataUri)->after(',')->toString(), true);

    expect($dataUri)->toStartWith('data:image/svg+xml;base64,')
        ->and($svg)->toContain('<svg')
        ->toContain('viewBox');
});

test('lost and found schema includes lookup and coordination indexes', function () {
    $caseIndexes = collect(Schema::getIndexes('search_cases'))->pluck('name');
    $sightingIndexes = collect(Schema::getIndexes('sightings'))->pluck('name');
    $sectorIndexes = collect(Schema::getIndexes('search_sectors'))->pluck('name');
    $taskIndexes = collect(Schema::getIndexes('search_tasks'))->pluck('name');
    $volunteerIndexes = collect(Schema::getIndexes('search_volunteers'))->pluck('name');
    $reportIndexes = collect(Schema::getIndexes('search_reports'))->pluck('name');

    expect($caseIndexes)
        ->toContain('search_cases_status_city_seen_idx')
        ->toContain('search_cases_type_species_status_idx')
        ->toContain('search_cases_alerts_city_idx')
        ->and($sightingIndexes)->toContain('sightings_case_status_observed_idx')
        ->and($sectorIndexes)->toContain('search_sectors_case_status_priority_idx')
        ->and($taskIndexes)
        ->toContain('search_tasks_case_status_due_idx')
        ->toContain('search_tasks_assignee_status_idx')
        ->and($volunteerIndexes)->toContain('search_volunteers_case_status_idx')
        ->and($reportIndexes)->toContain('search_reports_priority_status_idx');
});

/** @param array<string, mixed> $overrides */
function searchCasePayload(array $overrides = []): array
{
    return [
        'type' => 'lost',
        'intent' => 'publish',
        'pet_profile_key' => 'scout',
        'pet_name' => 'Scout',
        'species' => 'dog',
        'breed' => 'Labrador mix',
        'sex' => 'male',
        'age_label' => '4 years',
        'size' => 'large',
        'primary_color' => 'Black with a white chest',
        'coat' => 'short',
        'distinctive_marks' => 'White chest patch and one folded ear.',
        'hidden_marks' => 'Crescent scar below the left shoulder.',
        'description' => 'Scout slipped out of his harness after a loud sound and may keep distance from strangers.',
        'health_notice' => 'Needs regular medication; contact the owner promptly.',
        'approach_instructions' => 'Stay sideways, speak quietly, and send the location.',
        'avoid_instructions' => 'Do not chase, surround, shout, or enter unsafe areas.',
        'accessories' => ['blue collar'],
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

/** @param array<string, mixed> $overrides */
function sightingPayload(string $idempotencyKey, array $overrides = []): array
{
    return [
        'idempotency_key' => $idempotencyKey,
        'observed_at' => now()->subMinutes(20)->format('Y-m-d H:i:s'),
        'time_accuracy' => 'exact',
        'public_area' => 'Vingis Park east river path',
        'latitude' => 54.683812,
        'longitude' => 25.243774,
        'location_note' => 'Quiet gravel junction',
        'direction' => 'East',
        'distance' => 'About 30 metres',
        'confidence' => 'very-similar',
        'contact_status' => 'seen-only',
        'animal_condition' => 'Moving normally but frightened',
        'danger' => null,
        'notes' => 'Kept distance and did not chase.',
        'safety_acknowledged' => 1,
        ...$overrides,
    ];
}
