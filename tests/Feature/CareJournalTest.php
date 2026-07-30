<?php

use App\Enums\CareEntryType;
use App\Enums\CareTaskStatus;
use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareMedia;
use App\Models\CareTask;
use App\Models\MedicalRecord;
use App\Models\Medication;
use Database\Seeders\CareJournalSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('an owner can create one private care journal for a managed pet', function () {
    $this->post(route('care-journals.store'), careJournalPayload())
        ->assertRedirect();

    $journal = CareJournal::query()->firstOrFail();

    expect($journal)
        ->owner_key->toBe('mia-carter')
        ->pet_profile_key->toBe('scout')
        ->privacy->toBe('private')
        ->current_caregiver_name->toBe('Mia Carter')
        ->and(AuditLog::query()->where('action', 'care-journal.created')->count())->toBe(1);

    $this->from(route('care-journals.create'))
        ->post(route('care-journals.store'), careJournalPayload())
        ->assertRedirect(route('care-journals.create'))
        ->assertSessionHasErrors('pet_profile_key');

    expect(CareJournal::query()->where('pet_profile_key', 'scout')->count())->toBe(1);
});

test('private care notes and measurements are encrypted and other owners are forbidden', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'another-owner']);
    $entry = CareEntry::factory()->for($journal)->create([
        'notes' => 'Private feeding and behavior detail',
        'measurements' => ['amount_consumed' => '82 g'],
        'context' => ['location_label' => 'Private home route'],
    ]);

    expect((string) $entry->getRawOriginal('notes'))
        ->not->toContain('Private feeding and behavior detail')
        ->and((string) $entry->getRawOriginal('measurements'))
        ->not->toContain('82 g')
        ->and((string) $entry->getRawOriginal('context'))
        ->not->toContain('Private home route');

    $this->get(route('care-journals.show', $journal))->assertForbidden();
    $this->get(route('care-journals.manage', $journal))->assertForbidden();
    $this->get(route('care-journals.report', $journal))->assertForbidden();
});

test('care entries are structured idempotent and guard against accidental double feeding', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'mia-carter']);
    $key = (string) Str::uuid();
    $payload = careEntryPayload($key, [
        'entry_type' => 'feeding',
        'subtype' => 'breakfast',
        'title' => 'Breakfast',
        'quantity_value' => '150',
        'quantity_unit' => 'g',
        'product_name' => 'Sensitive food',
        'amount_offered' => '150 g',
        'amount_consumed' => '100 g',
        'appetite' => 'reduced',
        'notes' => 'Ate less than planned.',
    ]);

    $this->post(route('care-journals.entries.store', $journal), $payload)->assertRedirect();
    $this->post(route('care-journals.entries.store', $journal), $payload)->assertRedirect();

    $entry = CareEntry::query()->firstOrFail();

    expect(CareEntry::query()->count())->toBe(1)
        ->and($entry->type)->toBe(CareEntryType::Feeding)
        ->and($entry->measurements)->toMatchArray([
            'product_name' => 'Sensitive food',
            'amount_offered' => '150 g',
            'amount_consumed' => '100 g',
        ])
        ->and($journal->refresh()->last_feeding_at)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'care-entry.created')->count())->toBe(1);

    $this->from(route('care-journals.show', $journal))
        ->post(route('care-journals.entries.store', $journal), [
            ...$payload,
            'idempotency_key' => (string) Str::uuid(),
            'title' => 'Second breakfast by mistake',
        ])
        ->assertRedirect(route('care-journals.show', $journal))
        ->assertSessionHasErrors('confirm_duplicate');

    expect(CareEntry::query()->count())->toBe(1);
});

test('completing a care task creates exactly one timeline entry', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'mia-carter']);
    $task = CareTask::factory()->for($journal)->create([
        'title' => 'Refresh water',
        'type' => CareEntryType::Water,
    ]);
    $payload = [
        'idempotency_key' => (string) Str::uuid(),
        'status' => 'completed',
        'completion_note' => 'Kitchen bowl washed and refilled.',
    ];

    $this->post(route('care-journals.tasks.complete', [$journal, $task]), $payload)
        ->assertRedirect(route('care-journals.show', $journal));
    $this->post(route('care-journals.tasks.complete', [$journal, $task]), $payload)
        ->assertRedirect();

    expect($task->refresh())
        ->status->toBe(CareTaskStatus::Completed)
        ->completed_by_name->toBe('Mia Carter')
        ->and(CareEntry::query()->where('care_task_id', $task->id)->count())->toBe(1)
        ->and(CareEntry::query()->firstOrFail()->notes)->toBe('Kitchen bowl washed and refilled.');
});

test('temporary care access reveals selected sections and records contributor identity', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'mia-carter']);
    CareEntry::factory()->for($journal)->create([
        'type' => CareEntryType::Feeding,
        'title' => 'Visible breakfast',
        'started_at' => now()->subHours(3),
        'context' => ['location_label' => 'Private home address'],
    ]);
    CareEntry::factory()->for($journal)->create([
        'type' => CareEntryType::Behavior,
        'title' => 'Hidden behavior note',
    ]);

    $response = $this->post(route('care-journals.access.store', $journal), [
        'recipient_name' => 'Sam Sitter',
        'recipient_role' => 'sitter',
        'label' => 'Weekend feeding',
        'sections' => ['feeding'],
        'allow_add' => 1,
        'max_views' => 5,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ])->assertRedirect(route('care-journals.manage', $journal));

    $accessUrl = $response->getSession()->get('care_access_url');
    $token = str($accessUrl)->afterLast('/')->toString();
    $grant = CareAccessGrant::query()->firstOrFail();

    expect($grant->token_hash)->not->toContain($token);

    $this->get($accessUrl)
        ->assertOk()
        ->assertSee('Visible breakfast')
        ->assertDontSee('Hidden behavior note')
        ->assertDontSee('Private home address')
        ->assertDontSee('Private route summary');

    $this->post(route('care-access.entries.store', ['token' => $token]), careEntryPayload(
        (string) Str::uuid(),
        [
            'entry_type' => 'feeding',
            'title' => 'Sitter dinner report',
            'started_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
            'source_type' => 'sitter',
            'source_name' => 'Sam Sitter',
        ],
    ))->assertRedirect(route('care-access.show', ['token' => $token]));

    $sitterEntry = CareEntry::query()
        ->where('title', 'Sitter dinner report')
        ->firstOrFail();

    expect($sitterEntry)
        ->author_name->toBe('Sam Sitter')
        ->source_type->value->toBe('sitter')
        ->verification_status->toBe('contributor-reported');

    $this->delete(route('care-journals.access.revoke', [$journal, $grant]))
        ->assertRedirect(route('care-journals.manage', $journal));
    $this->get($accessUrl)->assertNotFound();
});

test('care media stays private and owner and allowed shared downloads are audited', function () {
    Storage::fake('local');
    $journal = CareJournal::factory()->create(['owner_key' => 'mia-carter']);

    $this->post(route('care-journals.entries.store', $journal), careEntryPayload(
        (string) Str::uuid(),
        [
            'entry_type' => 'toilet',
            'title' => 'Private toilet observation',
            'media' => UploadedFile::fake()->image('private-observation.jpg'),
            'media_alt' => 'Private toilet observation photo',
        ],
    ))->assertRedirect();

    $media = CareMedia::query()->firstOrFail();

    Storage::disk('local')->assertExists($media->path);
    expect($media)
        ->sensitivity->toBe('sensitive')
        ->and((string) $media->getRawOriginal('original_name'))
        ->not->toContain('private-observation.jpg');

    $this->get(route('care-journals.media.download', [$journal, $media]))
        ->assertOk()
        ->assertDownload('private-observation.jpg');

    $response = $this->post(route('care-journals.access.store', $journal), [
        'recipient_name' => 'Dr. Review',
        'recipient_role' => 'veterinarian',
        'label' => 'Toilet review',
        'sections' => ['toilet'],
        'allow_media' => 1,
        'max_views' => 5,
        'expires_in_hours' => 8,
        'privacy_acknowledged' => 1,
    ]);
    $token = str($response->getSession()->get('care_access_url'))->afterLast('/')->toString();

    $this->get(route('care-access.show', ['token' => $token]))
        ->assertOk()
        ->assertSee('Private toilet observation photo');
    $this->get(route('care-access.media.download', [$token, $media]))
        ->assertOk()
        ->assertDownload('private-observation.jpg');

    $restrictedResponse = $this->post(route('care-journals.access.store', $journal), [
        'recipient_name' => 'Restricted reviewer',
        'recipient_role' => 'veterinarian',
        'label' => 'Feeding review only',
        'sections' => ['feeding'],
        'allow_media' => 1,
        'max_views' => 5,
        'expires_in_hours' => 8,
        'privacy_acknowledged' => 1,
    ]);
    $restrictedToken = str($restrictedResponse->getSession()->get('care_access_url'))
        ->afterLast('/')
        ->toString();

    $this->get(route('care-access.media.download', [$restrictedToken, $media]))
        ->assertForbidden();

    expect(AuditLog::query()->where('action', 'care-media.downloaded')->count())->toBe(2);
});

test('care media factory keeps the attachment and entry in one journal', function () {
    $media = CareMedia::factory()->create();

    expect($media->care_journal_id)->toBe($media->entry->care_journal_id);
});

test('the care journal reflects medication from the medical record without duplicating doses', function () {
    $journal = CareJournal::factory()->create([
        'owner_key' => 'mia-carter',
        'pet_profile_key' => 'scout',
        'pet_name' => 'Scout',
    ]);
    $medicalRecord = MedicalRecord::factory()->create([
        'owner_key' => 'mia-carter',
        'pet_profile_key' => 'scout',
    ]);
    Medication::factory()->for($medicalRecord)->create([
        'name' => 'Single source medication',
        'status' => 'active',
    ]);

    $this->get(route('care-journals.show', $journal))
        ->assertOk()
        ->assertSee('Single source medication')
        ->assertSee('The care journal never creates a second dose history');

    expect(CareEntry::query()->count())->toBe(0);
});

test('directory detail manage shared report and create screens render with private headers', function () {
    $journal = CareJournal::factory()->create([
        'owner_key' => 'mia-carter',
        'pet_name' => 'Scout Care Test',
    ]);
    CareEntry::factory()->for($journal)->create(['title' => 'Timeline test entry']);
    CareTask::factory()->for($journal)->create(['title' => 'Task test entry']);

    $this->get(route('care-journals.index'))
        ->assertOk()
        ->assertSee('Private care journals')
        ->assertSee('Scout Care Test');
    $this->get(route('care-journals.create'))
        ->assertOk()
        ->assertSee('Create a private care journal');
    $this->get(route('care-journals.show', $journal))
        ->assertOk()
        ->assertSee('Recent care timeline')
        ->assertSee('Timeline test entry')
        ->assertSee('Task test entry');
    $this->get(route('care-journals.manage', $journal))
        ->assertOk()
        ->assertSee('Plan and share care')
        ->assertSee('Temporary access');
    $this->get(route('care-journals.report', $journal))
        ->assertOk()
        ->assertSee('Private care report')
        ->assertSee('recorded facts only')
        ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
});

test('care journal screens keep query counts bounded as timeline data grows', function () {
    $journal = CareJournal::factory()->create(['owner_key' => 'mia-carter']);
    CareEntry::factory()->count(60)->for($journal)->create();
    CareTask::factory()->count(12)->for($journal)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $response = $this->get(route('care-journals.show', $journal));
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    $response->assertOk();
    expect($queryCount)->toBeLessThanOrEqual(12);
});

test('the care journal seeder is idempotent and creates useful family care data', function () {
    $seeder = app(CareJournalSeeder::class);

    $seeder->run();
    $seeder->run();

    $scout = CareJournal::query()->where('slug', 'scout-care')->firstOrFail();
    $nori = CareJournal::query()->where('slug', 'nori-care')->firstOrFail();

    expect(CareJournal::query()->where('owner_key', 'mia-carter')->count())->toBe(2)
        ->and($scout->entries()->count())->toBe(41)
        ->and($scout->routines()->count())->toBe(2)
        ->and($scout->tasks()->count())->toBe(3)
        ->and($nori->entries()->count())->toBe(4)
        ->and($nori->routines()->count())->toBe(1)
        ->and($nori->tasks()->count())->toBe(1);
});

test('care schema includes owner timeline task and temporary access indexes', function () {
    $journalIndexes = collect(Schema::getIndexes('care_journals'))->pluck('name');
    $entryIndexes = collect(Schema::getIndexes('care_entries'))->pluck('name');
    $taskIndexes = collect(Schema::getIndexes('care_tasks'))->pluck('name');
    $accessIndexes = collect(Schema::getIndexes('care_access_grants'))->pluck('name');

    expect($journalIndexes)
        ->toContain('care_journals_owner_key_pet_profile_key_unique')
        ->toContain('care_journals_owner_key_status_updated_at_index')
        ->and($entryIndexes)
        ->toContain('care_entries_idempotency_key_unique')
        ->toContain('care_entries_care_task_id_unique')
        ->toContain('care_entries_care_journal_id_type_started_at_index')
        ->and($taskIndexes)->toContain('care_tasks_care_journal_id_status_due_at_index')
        ->and($accessIndexes)
        ->toContain('care_access_grants_token_hash_unique')
        ->toContain('care_access_grants_care_journal_id_revoked_at_expires_at_index');
});

/** @param array<string, mixed> $overrides */
function careJournalPayload(array $overrides = []): array
{
    return [
        'pet_profile_key' => 'scout',
        'timezone' => 'Europe/Vilnius',
        'current_caregiver_name' => 'Mia Carter',
        'privacy_acknowledged' => 1,
        ...$overrides,
    ];
}

/** @param array<string, mixed> $overrides */
function careEntryPayload(string $key, array $overrides = []): array
{
    return [
        'idempotency_key' => $key,
        'entry_type' => 'observation',
        'title' => 'Care observation',
        'started_at' => now()->startOfMinute()->format('Y-m-d H:i:s'),
        'status' => 'completed',
        'source_type' => 'owner',
        'source_name' => 'Mia Carter',
        ...$overrides,
    ];
}
