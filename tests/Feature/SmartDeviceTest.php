<?php

use App\Actions\IssueDeviceCommand;
use App\Enums\CareEntryStatus;
use App\Enums\DeviceAutomationStatus;
use App\Enums\DeviceCommandStatus;
use App\Enums\DeviceConfidence;
use App\Enums\DeviceType;
use App\Enums\MedicalVerificationStatus;
use App\Models\AuditLog;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\DeviceAccessGrant;
use App\Models\DeviceAutomation;
use App\Models\DeviceAutomationRun;
use App\Models\DeviceCommand;
use App\Models\DeviceEvent;
use App\Models\DeviceLifecycleRecord;
use App\Models\DevicePetAssignment;
use App\Models\DeviceReading;
use App\Models\DeviceSafeZone;
use App\Models\MedicalRecord;
use App\Models\SmartDevice;
use App\Models\WeightEntry;
use Database\Seeders\SmartDeviceSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    session()->passwordConfirmed();
});

test('an owner can connect a private multi-pet device with encrypted identifiers', function () {
    $this->post(route('devices.store'), smartDevicePayload())
        ->assertRedirect();

    $device = SmartDevice::query()->firstOrFail();

    expect($device)
        ->owner_key->toBe('mia-carter')
        ->privacy->toBe('private')
        ->type->toBe(DeviceType::Waterer)
        ->serial_number->toBe('PRIVATE-SERIAL-1204')
        ->private_location_label->toBe('Kitchen corner near the private entrance')
        ->and($device->assignments()->count())->toBe(2)
        ->and((string) $device->getRawOriginal('serial_number'))
        ->not->toContain('PRIVATE-SERIAL-1204')
        ->and((string) $device->getRawOriginal('private_location_label'))
        ->not->toContain('private entrance')
        ->and(AuditLog::query()->where('action', 'smart-device.created')->count())
        ->toBe(1);
});

test('precise device pages and commands require recent password confirmation', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
    ]);
    session()->forget('auth.password_confirmed_at');

    $this->get(route('devices.show', $device))
        ->assertRedirect(route('password.confirm'));
    $this->get(route('devices.manage', $device))
        ->assertRedirect(route('password.confirm'));
    $this->post(route('devices.commands.store', $device), [
        'idempotency_key' => (string) Str::uuid(),
        'command_type' => 'refresh-status',
    ])->assertRedirect(route('password.confirm'));

    expect(DeviceCommand::query()->doesntExist())->toBeTrue();
});

test('device pages are owner-only and return private response headers', function () {
    $owned = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'name' => 'Private GPS',
        'type' => DeviceType::GpsTracker,
    ]);
    $other = SmartDevice::factory()->create(['owner_key' => 'another-owner']);

    $this->get(route('devices.index'))
        ->assertOk()
        ->assertSee('Private GPS')
        ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    $this->get(route('devices.show', $owned))->assertOk();
    $this->get(route('devices.manage', $owned))->assertOk();
    $this->get(route('devices.show', $other))->assertForbidden();
    $this->get(route('devices.manage', $other))->assertForbidden();
});

test('readings are encrypted idempotent and shared devices do not invent a pet', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::Waterer,
    ]);
    DevicePetAssignment::factory()->for($device)->create([
        'pet_profile_key' => 'scout',
        'pet_name' => 'Scout',
        'is_primary' => true,
    ]);
    DevicePetAssignment::factory()->for($device)->create([
        'pet_profile_key' => 'nori',
        'pet_name' => 'Nori',
        'is_primary' => false,
    ]);
    $payload = deviceReadingPayload([
        'external_event_id' => 'shared-water-reading',
        'metric_type' => 'water-use',
        'numeric_value' => 210,
        'unit' => 'ml',
        'original_payload' => ['private_sensor_id' => 'SENSOR-SECRET'],
        'processed_payload' => ['possible_spill' => true],
    ]);

    $this->post(route('devices.readings.store', $device), $payload)->assertRedirect();
    $this->post(route('devices.readings.store', $device), $payload)->assertRedirect();

    $reading = DeviceReading::query()->firstOrFail();

    expect(DeviceReading::query()->count())->toBe(1)
        ->and($reading->pet_profile_key)->toBeNull()
        ->and($reading->pet_name)->toBeNull()
        ->and((string) $reading->getRawOriginal('original_payload'))
        ->not->toContain('SENSOR-SECRET')
        ->and((string) $reading->getRawOriginal('processed_payload'))
        ->not->toContain('possible_spill')
        ->and(DeviceEvent::query()->where('type', 'water-use')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'device-reading.recorded')->count())
        ->toBe(1);
});

test('stale reconnect telemetry is grouped without discarding source readings', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::Waterer,
    ]);
    $recordedAt = now()->subMinutes(20)->startOfMinute();

    foreach ([1, 2] as $sequence) {
        $this->post(route('devices.readings.store', $device), deviceReadingPayload([
            'external_event_id' => 'reconnect-water-'.$sequence,
            'metric_type' => 'water-use',
            'numeric_value' => 40 + $sequence,
            'unit' => 'ml',
            'recorded_at' => $recordedAt->copy()->addMinutes($sequence)->format('Y-m-d H:i:s'),
            'is_stale' => 1,
        ]))->assertRedirect();
    }

    $event = DeviceEvent::query()->firstOrFail();

    expect(DeviceReading::query()->count())->toBe(2)
        ->and(DeviceEvent::query()->count())->toBe(1)
        ->and($event->occurrence_count)->toBe(2)
        ->and($event->details['grouped_after_reconnect'])->toBeTrue()
        ->and($event->details['reading_ids'])->toHaveCount(2);
});

test('remote commands are idempotent and feeder duplication requires an override', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::Feeder,
    ]);
    DevicePetAssignment::factory()->for($device)->create([
        'pet_profile_key' => 'nori',
        'pet_name' => 'Nori',
        'is_primary' => true,
    ]);
    $key = (string) Str::uuid();
    $payload = [
        'idempotency_key' => $key,
        'command_type' => 'dispense-food',
        'portion_grams' => 20,
        'reason' => 'Manual feeding test',
    ];

    $this->post(route('devices.commands.store', $device), $payload)->assertRedirect();
    $this->post(route('devices.commands.store', $device), $payload)->assertRedirect();

    expect(DeviceCommand::query()->count())->toBe(1)
        ->and(DeviceEvent::query()->where('type', 'dispense-food')->count())->toBe(1)
        ->and(DeviceCommand::query()->firstOrFail()->status)
        ->toBe(DeviceCommandStatus::Accepted)
        ->and(DeviceCommand::query()->firstOrFail()->delivered_at)->toBeNull()
        ->and(DeviceCommand::query()->firstOrFail()->completed_at)->toBeNull()
        ->and(DeviceCommand::query()->firstOrFail()->result)
        ->toMatchArray([
            'platform_state_updated' => false,
            'device_execution_confirmed' => false,
        ]);

    $this->from(route('devices.show', $device))
        ->post(route('devices.commands.store', $device), [
            ...$payload,
            'idempotency_key' => (string) Str::uuid(),
        ])
        ->assertRedirect(route('devices.show', $device))
        ->assertSessionHasErrors('confirm_duplicate');

    expect(DeviceCommand::query()->count())->toBe(1);
});

test('high-impact and prohibited commands are guarded server-side', function () {
    $gps = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
    ]);

    $this->from(route('devices.show', $gps))
        ->post(route('devices.commands.store', $gps), [
            'idempotency_key' => (string) Str::uuid(),
            'command_type' => 'enable-lost-mode',
        ])
        ->assertRedirect(route('devices.show', $gps))
        ->assertSessionHasErrors('confirmed');

    expect(DeviceCommand::query()->count())->toBe(0);

    app(IssueDeviceCommand::class)->handle($gps, [
        'idempotency_key' => (string) Str::uuid(),
        'command_type' => 'electroshock',
    ]);
})->throws(ValidationException::class, 'not allowed');

test('physical movement commands fail closed without fresh clear interlocks', function () {
    $door = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::SmartDoor,
    ]);

    $this->from(route('devices.show', $door))
        ->post(route('devices.commands.store', $door), [
            'idempotency_key' => (string) Str::uuid(),
            'command_type' => 'unlock-door',
            'confirmed' => 1,
        ])
        ->assertRedirect(route('devices.show', $door))
        ->assertSessionHasErrors('command_type');

    $door->update([
        'safety_state' => [
            'pet_in_doorway' => false,
            'obstruction_detected' => false,
        ],
        'safety_state_recorded_at' => now(),
    ]);

    $this->post(route('devices.commands.store', $door), [
        'idempotency_key' => (string) Str::uuid(),
        'command_type' => 'unlock-door',
        'confirmed' => 1,
    ])->assertRedirect();

    $command = DeviceCommand::query()->firstOrFail();

    expect($command->status)->toBe(DeviceCommandStatus::Accepted)
        ->and($command->completed_at)->toBeNull()
        ->and($door->refresh()->operating_mode)->toBe('normal');
});

test('a theft report preserves owner access to lost mode', function () {
    $gps = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
        'is_reported_stolen' => true,
    ]);

    $this->post(route('devices.commands.store', $gps), [
        'idempotency_key' => (string) Str::uuid(),
        'command_type' => 'enable-lost-mode',
        'confirmed' => 1,
    ])->assertRedirect();

    expect(DeviceCommand::query()->count())->toBe(1)
        ->and($gps->refresh()->status->value)->toBe('lost-mode');
});

test('owners can configure retention and record an encrypted device lifecycle', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'firmware_version' => '1.0.0',
    ]);
    $oldLocation = DeviceReading::factory()->for($device)->create([
        'metric_type' => 'location',
        'recorded_at' => now()->subDays(40),
    ]);
    $currentLocation = DeviceReading::factory()->for($device)->create([
        'metric_type' => 'location',
        'recorded_at' => now()->subMinute(),
    ]);
    $oldEvent = DeviceEvent::factory()->for($device)->create([
        'occurred_at' => now()->subDays(120),
    ]);

    $this->put(route('devices.retention.update', $device), [
        'location_retention_days' => 7,
        'media_retention_days' => 3,
        'telemetry_retention_days' => 90,
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('devices.manage', $device));

    $this->post(route('devices.lifecycle.store', $device), [
        'kind' => 'firmware',
        'status' => 'completed',
        'severity' => 'important',
        'effective_at' => now()->format('Y-m-d H:i:s'),
        'version_from' => '1.0.0',
        'version_to' => '1.1.0',
        'reference' => 'SEC-UPDATE-110',
        'note' => 'Installed after reviewing the vendor signature.',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('devices.manage', $device));

    $record = DeviceLifecycleRecord::query()->firstOrFail();
    $device->refresh();

    expect($device->location_retention_days)->toBe(7)
        ->and($device->media_retention_days)->toBe(3)
        ->and($device->telemetry_retention_days)->toBe(90)
        ->and($device->firmware_version)->toBe('1.1.0')
        ->and($record->details['reference'])->toBe('SEC-UPDATE-110')
        ->and((string) $record->getRawOriginal('details'))
        ->not->toContain('SEC-UPDATE-110')
        ->and(DeviceReading::query()->whereKey($oldLocation->id)->doesntExist())
        ->toBeTrue()
        ->and(DeviceReading::query()->whereKey($currentLocation->id)->exists())
        ->toBeTrue()
        ->and(DeviceEvent::query()->whereKey($oldEvent->id)->doesntExist())
        ->toBeTrue()
        ->and(AuditLog::query()->where('action', 'device-retention.updated')->count())
        ->toBe(1)
        ->and(AuditLog::query()->where('action', 'device-lifecycle.recorded')->count())
        ->toBe(1);
});

test('safe-zone geometry is encrypted and only gps trackers accept zones', function () {
    $gps = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
    ]);
    $feeder = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::Feeder,
    ]);
    $payload = deviceSafeZonePayload();

    $this->post(route('devices.safe-zones.store', $gps), $payload)->assertRedirect();

    $zone = DeviceSafeZone::query()->firstOrFail();

    expect($zone->exact_geometry)->toMatchArray([
        'latitude' => 54.68916,
        'longitude' => 25.27083,
        'radius_meters' => 120.0,
    ])->and((string) $zone->getRawOriginal('exact_geometry'))
        ->not->toContain('54.68916');

    $this->from(route('devices.manage', $feeder))
        ->post(route('devices.safe-zones.store', $feeder), $payload)
        ->assertRedirect(route('devices.manage', $feeder))
        ->assertSessionHasErrors('name');

    expect(DeviceSafeZone::query()->count())->toBe(1);
});

test('automation simulation records intent without sending a real command', function () {
    $gps = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
    ]);

    $this->post(route('devices.automations.store', $gps), [
        'name' => 'Confirm zone exit',
        'trigger_type' => 'safe-zone-exit',
        'condition_mode' => 'any',
        'action_type' => 'enable-lost-mode',
        'priority' => 'urgent',
        'status' => 'enabled',
        'max_runs_per_hour' => 2,
        'cooldown_seconds' => 300,
        'safety_acknowledged' => 1,
    ])->assertRedirect(route('devices.manage', $gps));

    $automation = DeviceAutomation::query()->firstOrFail();

    $this->post(route('devices.automations.test', [$gps, $automation]))
        ->assertRedirect(route('devices.manage', $gps));

    $run = DeviceAutomationRun::query()->firstOrFail();

    expect($automation->status)->toBe(DeviceAutomationStatus::Enabled)
        ->and($run->is_simulation)->toBeTrue()
        ->and($run->result['real_command_sent'])->toBeFalse()
        ->and(DeviceCommand::query()->count())->toBe(0);
});

test('temporary device access is scoped masks home data expires by views and is revocable', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
        'name' => 'Scout Shared GPS',
        'serial_number' => 'DO-NOT-SHARE-9911',
        'private_location_label' => 'Exact private bedroom shelf',
        'public_zone_label' => 'Home area',
        'current_latitude' => '54.689160',
        'current_longitude' => '25.270830',
    ]);
    DeviceReading::factory()->for($device)->create([
        'metric_type' => 'battery-percent',
        'numeric_value' => 74,
        'unit' => '%',
        'text_value' => null,
    ]);
    DeviceEvent::factory()->for($device)->create([
        'title' => 'Visible device event',
        'summary' => 'A safe shared event summary.',
    ]);

    $response = $this->post(route('devices.access.store', $device), [
        'recipient_name' => 'Sam Sitter',
        'recipient_role' => 'sitter',
        'label' => 'One visit',
        'permissions' => ['view-status', 'view-readings', 'view-events'],
        'allow_location' => 1,
        'max_views' => 1,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ])->assertRedirect(route('devices.manage', $device));

    $accessUrl = $response->getSession()->get('device_access_url');
    $token = str($accessUrl)->afterLast('/')->toString();
    $grant = DeviceAccessGrant::query()->firstOrFail();

    expect($grant->token_hash)->not->toContain($token);

    $this->get($accessUrl)
        ->assertOk()
        ->assertSee('Home area')
        ->assertSee('74 %')
        ->assertSee('Visible device event')
        ->assertDontSee('54.68916')
        ->assertDontSee('Exact private bedroom shelf')
        ->assertDontSee('DO-NOT-SHARE-9911');
    $this->get($accessUrl)->assertNotFound();

    $secondResponse = $this->post(route('devices.access.store', $device), [
        'recipient_name' => 'Dr. Review',
        'recipient_role' => 'veterinarian',
        'label' => 'Telemetry review',
        'permissions' => ['view-status'],
        'max_views' => 5,
        'expires_in_hours' => 24,
        'privacy_acknowledged' => 1,
    ]);
    $secondUrl = $secondResponse->getSession()->get('device_access_url');
    $secondGrant = DeviceAccessGrant::query()->latest('id')->firstOrFail();

    $this->delete(route('devices.access.revoke', [$device, $secondGrant]))
        ->assertRedirect(route('devices.manage', $device));
    $this->get($secondUrl)->assertNotFound();
});

test('an owner can explicitly promote a device event into care as needs review', function () {
    $device = SmartDevice::factory()->create(['owner_key' => 'mia-carter']);
    CareJournal::factory()->create([
        'owner_key' => 'mia-carter',
        'pet_profile_key' => 'scout',
        'pet_name' => 'Scout',
    ]);
    $event = DeviceEvent::factory()->for($device)->create([
        'pet_profile_key' => 'scout',
        'pet_name' => 'Scout',
        'type' => 'water-use',
        'title' => 'Water use needs confirmation',
        'summary' => 'Device detected water movement.',
        'details' => ['numeric_value' => 180, 'unit' => 'ml'],
    ]);

    $this->post(route('devices.events.care-entry', [$device, $event]), [
        'confirmed' => 1,
    ])->assertRedirect(route('devices.show', $device));

    $entry = CareEntry::query()->firstOrFail();

    expect($entry->status)->toBe(CareEntryStatus::NeedsReview)
        ->and($entry->source_type->value)->toBe('device')
        ->and($entry->source_name)->toBe($device->name)
        ->and($event->refresh()->care_entry_id)->toBe($entry->id);
});

test('an owner can explicitly promote a scale reading to health as non-clinical', function () {
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::Scale,
        'name' => 'Nori Home Scale',
    ]);
    MedicalRecord::factory()->create([
        'owner_key' => 'mia-carter',
        'pet_profile_key' => 'nori',
        'pet_name' => 'Nori',
    ]);
    $reading = DeviceReading::factory()->for($device)->create([
        'pet_profile_key' => 'nori',
        'pet_name' => 'Nori',
        'metric_type' => 'weight-grams',
        'numeric_value' => 4720,
        'unit' => 'g',
    ]);

    $this->post(route('devices.readings.medical-entry', [$device, $reading]), [
        'confirmed' => 1,
    ])->assertRedirect(route('devices.show', $device));

    $weight = WeightEntry::query()->firstOrFail();

    expect($weight->weight_grams)->toBe(4720)
        ->and($weight->source_type->value)->toBe('device')
        ->and($weight->verification_status)->toBe(MedicalVerificationStatus::NeedsReview)
        ->and($reading->refresh()->weight_entry_id)->toBe($weight->id);
});

test('device directory and detail queries remain bounded as telemetry grows', function () {
    SmartDevice::factory()->count(8)->create(['owner_key' => 'mia-carter']);
    $device = SmartDevice::factory()->create([
        'owner_key' => 'mia-carter',
        'type' => DeviceType::GpsTracker,
    ]);
    DeviceReading::factory()->count(80)->for($device)->create();
    DeviceEvent::factory()->count(40)->for($device)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $directory = $this->get(route('devices.index'));
    $directoryQueries = count(DB::getQueryLog());
    DB::flushQueryLog();
    $detail = $this->get(route('devices.show', $device));
    $detailQueries = count(DB::getQueryLog());
    DB::disableQueryLog();

    $directory->assertOk();
    $detail->assertOk();
    expect($directoryQueries)->toBeLessThanOrEqual(6)
        ->and($detailQueries)->toBeLessThanOrEqual(13);
});

test('the smart device seeder is idempotent and keeps shared readings honest', function () {
    $seeder = app(SmartDeviceSeeder::class);

    $seeder->run();
    $seeder->run();

    $water = SmartDevice::query()
        ->where('slug', 'kitchen-water-fountain')
        ->firstOrFail();
    $sharedReading = $water->readings()
        ->where('external_event_id', 'water-shared-today')
        ->firstOrFail();

    expect(SmartDevice::query()->where('owner_key', 'mia-carter')->count())->toBe(9)
        ->and(DevicePetAssignment::query()->count())->toBe(12)
        ->and(DeviceReading::query()->count())->toBe(9)
        ->and(DeviceEvent::query()->count())->toBe(4)
        ->and(DeviceSafeZone::query()->count())->toBe(1)
        ->and(DeviceAutomation::query()->count())->toBe(1)
        ->and(DeviceLifecycleRecord::query()->count())->toBe(1)
        ->and($sharedReading->pet_profile_key)->toBeNull()
        ->and($sharedReading->confidence)->toBe(DeviceConfidence::Low);
});

test('device schema includes directory telemetry command and access indexes', function () {
    $devices = collect(Schema::getIndexes('smart_devices'))->pluck('name');
    $readings = collect(Schema::getIndexes('device_readings'))->pluck('name');
    $events = collect(Schema::getIndexes('device_events'))->pluck('name');
    $commands = collect(Schema::getIndexes('device_commands'))->pluck('name');
    $access = collect(Schema::getIndexes('device_access_grants'))->pluck('name');
    $lifecycle = collect(Schema::getIndexes('device_lifecycle_records'))->pluck('name');

    expect($devices)
        ->toContain('smart_devices_owner_key_status_updated_at_index')
        ->toContain('smart_devices_connection_status_last_seen_at_index')
        ->and($readings)
        ->toContain('device_readings_smart_device_id_external_event_id_unique')
        ->toContain('device_readings_smart_device_id_metric_type_recorded_at_index')
        ->and($events)
        ->toContain('device_events_smart_device_id_external_event_id_unique')
        ->toContain('device_events_severity_status_occurred_at_index')
        ->toContain('device_events_grouping_window_idx')
        ->and($commands)
        ->toContain('device_commands_idempotency_key_unique')
        ->toContain('device_commands_smart_device_id_command_type_issued_at_index')
        ->and($access)
        ->toContain('device_access_grants_token_hash_unique')
        ->toContain('device_access_grants_smart_device_id_revoked_at_expires_at_index')
        ->and($lifecycle)
        ->toContain('device_lifecycle_device_kind_status_idx');
});

/** @param array<string, mixed> $overrides */
function smartDevicePayload(array $overrides = []): array
{
    return [
        'name' => 'Shared Kitchen Waterer',
        'type' => 'waterer',
        'brand' => 'ClearDrop',
        'model' => 'Flow Test',
        'serial_number' => 'PRIVATE-SERIAL-1204',
        'pet_profile_keys' => ['scout', 'nori'],
        'public_zone_label' => 'Kitchen',
        'private_location_label' => 'Kitchen corner near the private entrance',
        'connection_type' => 'wi-fi',
        'firmware_version' => '1.2.3',
        'supports_local_operation' => 1,
        'ownership_confirmed' => 1,
        'privacy_acknowledged' => 1,
        ...$overrides,
    ];
}

/** @param array<string, mixed> $overrides */
function deviceReadingPayload(array $overrides = []): array
{
    return [
        'external_event_id' => 'reading-'.Str::uuid(),
        'metric_type' => 'activity-minutes',
        'numeric_value' => 32,
        'unit' => 'min',
        'recorded_at' => now()->startOfMinute()->format('Y-m-d H:i:s'),
        'timezone' => 'Europe/Vilnius',
        'confidence' => 'medium',
        ...$overrides,
    ];
}

/** @return array<string, mixed> */
function deviceSafeZonePayload(): array
{
    return [
        'name' => 'Home boundary',
        'shape' => 'circle',
        'public_area_label' => 'Home area',
        'latitude' => 54.68916,
        'longitude' => 25.27083,
        'radius_meters' => 120,
        'exit_delay_seconds' => 45,
        'accuracy_threshold_meters' => 35,
        'is_home' => 1,
        'always_active' => 1,
    ];
}
