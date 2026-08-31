<?php

use App\Enums\MedicalKnowledgeStatus;
use App\Enums\MedicationStatus;
use App\Enums\PetManagerRole;
use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use App\Models\MedicalEvent;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\MedicationDose;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\WeightEntry;
use Database\Seeders\MedicalRecordSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('an owner can create one private medical record for a managed pet', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'profile_key' => 'pet-scout',
        'slug' => 'scout',
        'name' => 'Birch',
        'species' => 'dog',
        'breed' => 'Border Collie mix',
    ]);

    $this->post(route('medical-records.store'), medicalRecordPayload())
        ->assertRedirect();

    $record = MedicalRecord::query()->firstOrFail();

    expect($record)
        ->owner_key->toBe('test-member')
        ->owner_id->toBe($this->authenticatedUser->id)
        ->pet_profile_id->toBe($pet->id)
        ->pet_profile_key->toBe('scout')
        ->privacy->toBe('private')
        ->current_weight_grams->toBe(18700)
        ->and($record->weightEntries()->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-record.created')->count())->toBe(1);

    $this->from(route('medical-records.create'))
        ->post(route('medical-records.store'), medicalRecordPayload())
        ->assertRedirect(route('medical-records.create'))
        ->assertSessionHasErrors('pet_profile_key');

    expect(MedicalRecord::query()->where('pet_profile_key', 'scout')->count())->toBe(1);
});

test('a canonical medical record follows the pet when the primary owner changes', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'slug' => 'transfer-pet',
        'name' => 'Transfer Pet',
    ]);
    $record = MedicalRecord::factory()->forPetProfile($pet)->create();
    $newOwner = User::factory()->create();

    $pet->update(['user_id' => $newOwner->id]);

    $this->get(route('medical-records.show', $record))->assertForbidden();
    $this->get(route('medical-records.index'))->assertDontSee('Transfer Pet');

    $this->actingAs($newOwner);

    $this->get(route('medical-records.show', $record))
        ->assertOk()
        ->assertSee('Transfer Pet');
    $this->get(route('medical-records.index'))
        ->assertOk()
        ->assertSee('Transfer Pet');

    expect($record->refresh())
        ->pet_profile_id->toBe($pet->id)
        ->owner_id->toBe($this->authenticatedUser->id)
        ->owner_key->toBe('test-member');
});

test('co-owners can manage medical data while view-only carers cannot', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create();
    $record = MedicalRecord::factory()->forPetProfile($pet)->create();
    $coOwner = User::factory()->create();
    $foster = User::factory()->create();

    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($coOwner)
        ->create([
            'actor_key_snapshot' => $coOwner->actor_key,
            'role' => PetManagerRole::CoOwner,
        ]);
    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($foster)
        ->create([
            'actor_key_snapshot' => $foster->actor_key,
            'role' => PetManagerRole::FosterCarer,
        ]);

    $this->actingAs($coOwner);
    $this->get(route('medical-records.manage', $record))->assertOk();

    $this->actingAs($foster);
    $this->get(route('medical-records.show', $record))->assertOk();
    $this->get(route('medical-records.manage', $record))->assertForbidden();
});

test('expired professional access cannot reveal a canonical medical record', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create();
    $record = MedicalRecord::factory()->forPetProfile($pet)->create();
    $specialist = User::factory()->create();

    PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($specialist)
        ->expired()
        ->create([
            'actor_key_snapshot' => $specialist->actor_key,
            'role' => PetManagerRole::Specialist,
        ]);

    $this->actingAs($specialist);

    $this->get(route('medical-records.show', $record))->assertForbidden();
    $this->get(route('medical-records.index'))->assertDontSee($record->pet_name);
});

test('unknown and confirmed empty medical knowledge remain distinct', function () {
    $record = MedicalRecord::factory()->create([
        'owner_key' => 'test-member',
        'allergy_knowledge_status' => MedicalKnowledgeStatus::Unknown,
        'critical_allergies' => [],
        'medication_knowledge_status' => MedicalKnowledgeStatus::NoneKnown,
    ]);

    $this->get(route('medical-records.emergency', $record))
        ->assertOk()
        ->assertSee(__('medical.knowledge_statuses.unknown'))
        ->assertSee(__('medical.knowledge_statuses.none-known'));
});

test('sensitive medical values are encrypted and never shown to another owner', function () {
    $record = MedicalRecord::factory()->create([
        'owner_key' => 'another-owner',
        'microchip_number' => '981020009999999',
        'critical_allergies' => ['Private allergy detail'],
        'emergency_notes' => 'Private emergency instruction',
    ]);

    expect((string) $record->getRawOriginal('microchip_number'))
        ->not->toContain('981020009999999')
        ->and((string) $record->getRawOriginal('critical_allergies'))
        ->not->toContain('Private allergy detail')
        ->and((string) $record->getRawOriginal('emergency_notes'))
        ->not->toContain('Private emergency instruction');

    $this->get(route('medical-records.show', $record))->assertForbidden();
    $this->get(route('medical-records.emergency', $record))->assertForbidden();
    $this->get(route('medical-records.manage', $record))->assertForbidden();
});

test('medical events preserve their source and owner observations remain unverified', function () {
    $record = MedicalRecord::factory()->create(['owner_key' => 'test-member']);

    $this->post(route('medical-records.entries.store', $record), [
        'entry_type' => 'event',
        'title' => 'Reduced appetite after breakfast',
        'source_type' => 'owner',
        'source_name' => 'Test Member',
        'event_type' => 'symptom',
        'occurred_at' => now()->subHour()->format('Y-m-d H:i:s'),
        'event_status' => 'active',
        'summary' => 'Birch ate less than usual but remained alert.',
        'severity' => 'mild',
        'next_step' => 'Continue observation and contact the clinic if symptoms worsen.',
    ])->assertRedirect(route('medical-records.manage', $record));

    expect(MedicalEvent::query()->firstOrFail())
        ->source_type->value->toBe('owner')
        ->verification_status->value->toBe('owner-reported')
        ->and(AuditLog::query()->where('action', 'medical-entry.created')->count())->toBe(1);
});

test('weight entries retain gram precision and update the current summary', function () {
    $record = MedicalRecord::factory()->create([
        'owner_key' => 'test-member',
        'species' => 'bird',
        'current_weight_grams' => 92,
    ]);

    $this->post(route('medical-records.entries.store', $record), [
        'entry_type' => 'weight',
        'weight' => '89',
        'weight_unit' => 'g',
        'measured_at' => now()->format('Y-m-d H:i:s'),
        'source_type' => 'owner',
        'source_name' => 'Home gram scale',
        'measurement_context' => 'Before breakfast without carrier',
    ])->assertRedirect(route('medical-records.manage', $record));

    expect(WeightEntry::query()->firstOrFail())
        ->weight_grams->toBe(89)
        ->and($record->refresh()->current_weight_grams)->toBe(89);
});

test('a medication dose is idempotent and a handled slot cannot be marked twice', function () {
    $record = MedicalRecord::factory()->create(['owner_key' => 'test-member']);
    $medication = Medication::factory()->for($record)->create([
        'status' => MedicationStatus::Active,
    ]);
    $slot = now()->startOfMinute();
    $key = (string) Str::uuid();
    $payload = [
        'medication_id' => $medication->id,
        'idempotency_key' => $key,
        'scheduled_for' => $slot->format('Y-m-d H:i:s'),
        'administered_at' => $slot->addMinutes(2)->format('Y-m-d H:i:s'),
        'status' => 'given',
        'dose_given' => '1 tablet',
        'notes' => 'Given with food.',
    ];

    $this->post(route('medical-records.doses.store', $record), $payload)->assertRedirect();
    $this->post(route('medical-records.doses.store', $record), $payload)->assertRedirect();

    expect(MedicationDose::query()->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'medication-dose.recorded')->count())->toBe(1);

    $this->from(route('medical-records.show', $record))
        ->post(route('medical-records.doses.store', $record), [
            ...$payload,
            'idempotency_key' => (string) Str::uuid(),
        ])
        ->assertRedirect(route('medical-records.show', $record))
        ->assertSessionHasErrors('scheduled_for');

    expect(MedicationDose::query()->count())->toBe(1);
});

test('temporary access reveals only selected sections and can be revoked', function () {
    $record = MedicalRecord::factory()->create([
        'owner_key' => 'test-member',
        'microchip_number' => '981020001112223',
        'critical_allergies' => ['Do not expose without emergency permission'],
    ]);
    Medication::factory()->for($record)->create(['name' => 'Selected medication']);
    Vaccination::factory()->for($record)->create(['name' => 'Hidden vaccination']);

    $response = $this->post(route('medical-records.access.store', $record), [
        'recipient_name' => 'Dr. Temporary',
        'recipient_role' => 'veterinarian',
        'label' => 'Medication review only',
        'sections' => ['medications'],
        'max_views' => 2,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ])->assertRedirect(route('medical-records.manage', $record));

    $accessUrl = $response->getSession()->get('medical_access_url');
    $grant = MedicalAccessGrant::query()->firstOrFail();

    expect($accessUrl)->toBeString()
        ->and($grant->token_hash)->not->toContain(str($accessUrl)->afterLast('/')->toString());

    $this->get($accessUrl)
        ->assertOk()
        ->assertSee('Selected medication')
        ->assertDontSee('Hidden vaccination')
        ->assertDontSee('981020001112223')
        ->assertDontSee('Do not expose without emergency permission');

    expect($grant->refresh()->views_used)->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-access.opened')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-access.opened')->firstOrFail()->actor_key)
        ->toBe($this->authenticatedUser->actor_key);

    $this->delete(route('medical-records.access.revoke', [$record, $grant]))
        ->assertRedirect(route('medical-records.manage', $record));

    $this->get($accessUrl)->assertNotFound();
});

test('account-bound medical access rejects the wrong bearer without consuming a view', function () {
    $record = MedicalRecord::factory()->create(['owner_key' => $this->authenticatedUser->actor_key]);
    $recipient = User::factory()->create();
    $response = $this->post(route('medical-records.access.store', $record), [
        'recipient_key' => $recipient->actor_key,
        'recipient_name' => $recipient->name,
        'recipient_role' => 'caregiver',
        'label' => 'Account-bound review',
        'sections' => ['summary'],
        'max_views' => 2,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ]);
    $accessUrl = $response->getSession()->get('medical_access_url');
    $grant = MedicalAccessGrant::query()->firstOrFail();

    $this->get($accessUrl)->assertNotFound();
    expect($grant->refresh()->views_used)->toBe(0)
        ->and(AuditLog::query()->where('action', 'medical-access.opened')->count())->toBe(0);

    $this->actingAs($recipient)->get($accessUrl)->assertOk();
    expect($grant->refresh()->views_used)->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-access.opened')->firstOrFail()->actor_key)
        ->toBe($recipient->actor_key);
});

test('unbound medical access attributes the view to a different authenticated bearer', function () {
    $record = MedicalRecord::factory()->create(['owner_key' => $this->authenticatedUser->actor_key]);
    $recipient = User::factory()->create();
    $response = $this->post(route('medical-records.access.store', $record), [
        'recipient_name' => 'Any authenticated clinician',
        'recipient_role' => 'veterinarian',
        'label' => 'Unbound medical review',
        'sections' => ['summary'],
        'max_views' => 2,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ]);
    $accessUrl = $response->getSession()->get('medical_access_url');
    $grant = MedicalAccessGrant::query()->firstOrFail();

    $this->actingAs($recipient)->get($accessUrl)->assertOk();

    expect($grant->refresh()->views_used)->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-access.opened')->firstOrFail()->actor_key)
        ->toBe($recipient->actor_key);
});

test('medical documents stay on private storage and downloads are audited', function () {
    Storage::fake('local');
    $record = MedicalRecord::factory()->create(['owner_key' => 'test-member']);

    $this->post(route('medical-records.documents.store', $record), [
        'title' => 'Clinic visit summary',
        'document_type' => 'visit-summary',
        'source_type' => 'clinic',
        'source_name' => 'Paws 24',
        'document' => UploadedFile::fake()->create('visit-summary.pdf', 120, 'application/pdf'),
    ])->assertRedirect(route('medical-records.manage', $record));

    $document = MedicalDocument::query()->firstOrFail();

    Storage::disk('local')->assertExists($document->file_path);
    expect($document)
        ->verification_status->value->toBe('needs-review')
        ->and($document->file_path)->toStartWith('medical-records/'.$record->id.'/');

    $this->get(route('medical-records.documents.download', [$record, $document]))
        ->assertOk()
        ->assertDownload('visit-summary.pdf');

    expect($document->refresh()->download_count)->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-document.downloaded')->count())->toBe(1);

    $restrictedResponse = $this->post(route('medical-records.access.store', $record), [
        'recipient_name' => 'Restricted reviewer',
        'recipient_role' => 'caregiver',
        'label' => 'Read-only document review',
        'sections' => ['documents'],
        'max_views' => 3,
        'expires_in_hours' => 8,
        'privacy_acknowledged' => 1,
    ]);
    $restrictedToken = str($restrictedResponse->getSession()->get('medical_access_url'))
        ->afterLast('/')
        ->toString();
    $restrictedGrant = MedicalAccessGrant::query()->latest('id')->firstOrFail();

    $this->get(route('medical-access.documents.download', [$restrictedToken, $document]))
        ->assertForbidden();

    expect($restrictedGrant->refresh()->views_used)->toBe(0)
        ->and($document->refresh()->download_count)->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-access.document-opened')->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'medical-document.downloaded')->count())->toBe(1);

    $allowedResponse = $this->post(route('medical-records.access.store', $record), [
        'recipient_name' => 'Allowed reviewer',
        'recipient_role' => 'caregiver',
        'label' => 'Downloadable document review',
        'sections' => ['documents'],
        'allow_download' => 1,
        'max_views' => 3,
        'expires_in_hours' => 8,
        'privacy_acknowledged' => 1,
    ]);
    $allowedToken = str($allowedResponse->getSession()->get('medical_access_url'))
        ->afterLast('/')
        ->toString();
    $allowedGrant = MedicalAccessGrant::query()->latest('id')->firstOrFail();

    $this->get(route('medical-access.documents.download', [$allowedToken, $document]))
        ->assertOk()
        ->assertDownload('visit-summary.pdf');

    expect($allowedGrant->refresh()->views_used)->toBe(1)
        ->and($document->refresh()->download_count)->toBe(2)
        ->and(AuditLog::query()->where('action', 'medical-access.document-opened')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'medical-document.downloaded')->count())->toBe(2);
});

test('directory detail manage emergency and create screens render', function () {
    $pet = PetProfile::factory()->for($this->authenticatedUser)->create([
        'name' => 'Birch Health Test',
    ]);
    $record = MedicalRecord::factory()->forPetProfile($pet)->create();
    Medication::factory()->for($record)->create();
    Vaccination::factory()->for($record)->create();
    WeightEntry::factory()->for($record)->create();
    MedicalEvent::factory()->for($record)->create();

    $this->get(route('medical-records.index'))
        ->assertOk()
        ->assertSee('Pet health records')
        ->assertSee('Birch Health Test');
    $this->get(route('medical-records.create'))
        ->assertOk()
        ->assertSee('Create a medical record');
    $this->get(route('medical-records.show', $record))
        ->assertOk()
        ->assertSee('Before treatment or emergency care')
        ->assertSee('Medical timeline')
        ->assertSee('Today');
    $this->get(route('medical-records.manage', $record))
        ->assertOk()
        ->assertSee('Update health record')
        ->assertSee('Temporary access');
    $this->get(route('medical-records.emergency', $record))
        ->assertOk()
        ->assertSee('Emergency health card')
        ->assertSee('Last updated');
});

test('medical responses cannot be cached indexed or leak temporary links through referrers', function () {
    $record = MedicalRecord::factory()->create(['owner_key' => 'test-member']);

    $this->get(route('medical-records.show', $record))
        ->assertOk()
        ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
});

test('the medical record seeder is idempotent and creates a useful demo timeline', function () {
    $seeder = app(MedicalRecordSeeder::class);

    $seeder->run();
    $seeder->run();

    $scout = MedicalRecord::query()->where('slug', 'scout-health')->firstOrFail();
    $nori = MedicalRecord::query()->where('slug', 'nori-health')->firstOrFail();

    expect(MedicalRecord::query()->where('owner_key', 'test-member')->count())->toBe(2)
        ->and($scout->events()->count())->toBe(3)
        ->and($scout->vaccinations()->count())->toBe(2)
        ->and($scout->weightEntries()->count())->toBe(4)
        ->and($scout->medications()->count())->toBe(1)
        ->and($scout->reminders()->count())->toBe(2)
        ->and($scout->allergy_knowledge_status)->toBe(MedicalKnowledgeStatus::Known)
        ->and($nori->allergy_knowledge_status)->toBe(MedicalKnowledgeStatus::NoneKnown)
        ->and($nori->medication_knowledge_status)->toBe(MedicalKnowledgeStatus::NoneKnown);
});

test('medical schema includes owner timeline schedule and access indexes', function () {
    $recordIndexes = collect(Schema::getIndexes('medical_records'))->pluck('name');
    $eventIndexes = collect(Schema::getIndexes('medical_events'))->pluck('name');
    $medicationIndexes = collect(Schema::getIndexes('medications'))->pluck('name');
    $doseIndexes = collect(Schema::getIndexes('medication_doses'))->pluck('name');
    $accessIndexes = collect(Schema::getIndexes('medical_access_grants'))->pluck('name');

    expect($recordIndexes)
        ->toContain('medical_records_owner_pet_unique')
        ->toContain('medical_records_owner_status_idx')
        ->toContain('medical_records_pet_profile_unique')
        ->toContain('medical_records_pet_status_idx')
        ->and($eventIndexes)->toContain('medical_events_record_occurred_idx')
        ->and($medicationIndexes)->toContain('medications_record_status_dose_idx')
        ->and($doseIndexes)
        ->toContain('medication_doses_medication_slot_unique')
        ->toContain('medication_doses_record_schedule_idx')
        ->and($accessIndexes)->toContain('medical_access_record_active_idx');
});

/** @param array<string, mixed> $overrides */
function medicalRecordPayload(array $overrides = []): array
{
    return [
        'pet_profile_key' => 'scout',
        'birth_date' => now()->subYears(4)->toDateString(),
        'birth_date_estimated' => 0,
        'sex' => 'male',
        'reproductive_status' => 'neutered',
        'weight' => '18.7',
        'weight_unit' => 'kg',
        'timezone' => 'Europe/Vilnius',
        'microchip_status' => 'registered',
        'microchip_number' => '981020001234567',
        'microchip_checked_on' => now()->subMonth()->toDateString(),
        'blood_group' => 'DEA 1 negative',
        'allergy_knowledge_status' => 'known',
        'critical_allergies' => "Chicken protein\nBee stings",
        'medication_knowledge_status' => 'none-known',
        'chronic_conditions' => 'Seasonal skin sensitivity',
        'emergency_notes' => 'Approach calmly and call the owner.',
        'primary_clinic_name' => 'Paws 24',
        'primary_clinic_contact' => '+370 600 00001',
        'emergency_contact_name' => 'Test Member',
        'emergency_contact_phone' => '+370 600 00002',
        'emergency_contact_relationship' => 'Owner',
        'privacy_acknowledged' => 1,
        ...$overrides,
    ];
}
