<?php

namespace Database\Seeders;

use App\Enums\DeviceAutomationStatus;
use App\Enums\DeviceCommandStatus;
use App\Enums\DeviceConfidence;
use App\Enums\DeviceConnectionStatus;
use App\Enums\DeviceEventSeverity;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\DeviceAutomation;
use App\Models\DeviceCommand;
use App\Models\DeviceEvent;
use App\Models\DeviceLifecycleRecord;
use App\Models\DevicePetAssignment;
use App\Models\DeviceReading;
use App\Models\DeviceSafeZone;
use App\Models\SmartDevice;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class SmartDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();

        $gps = $this->device('scout-trail-gps', [
            'name' => 'Scout Trail GPS',
            'type' => DeviceType::GpsTracker,
            'brand' => 'Northstar Pet',
            'model' => 'Trail One',
            'serial_number' => 'NS-GPS-4207-SCOUT',
            'public_zone_label' => 'Home area',
            'private_location_label' => 'Hallway charging shelf',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'operating_mode' => 'active-walk',
            'connection_type' => 'gps-cellular-bluetooth',
            'firmware_version' => '4.8.2',
            'battery_percent' => 82,
            'signal_strength' => -57,
            'last_seen_at' => $now->subMinutes(2),
            'last_synced_at' => $now->subMinutes(2),
            'last_location_at' => $now->subMinutes(2),
            'current_latitude' => '54.689160',
            'current_longitude' => '25.270830',
            'location_accuracy_meters' => 8,
            'subscription_details' => [
                'plan' => 'Family GPS',
                'renews_on' => $now->addMonth()->toDateString(),
                'remote_tracking' => true,
            ],
            'purchased_on' => $now->subMonths(8)->toDateString(),
            'warranty_ends_on' => $now->addMonths(16)->toDateString(),
            'has_backup_power' => true,
            'supports_local_operation' => true,
            'requires_cloud' => true,
        ]);
        $gpsScout = $this->assign($gps, 'scout', 'Scout', true, 'collar');

        DeviceSafeZone::query()->updateOrCreate(
            ['smart_device_id' => $gps->id, 'name' => 'Home boundary'],
            [
                'shape' => 'circle',
                'public_area_label' => 'Home area',
                'exact_geometry' => [
                    'latitude' => 54.68916,
                    'longitude' => 25.27083,
                    'radius_meters' => 120,
                ],
                'schedule' => [
                    'always_active' => true,
                    'timezone' => 'Europe/Vilnius',
                ],
                'exit_delay_seconds' => 45,
                'accuracy_threshold_meters' => 35,
                'status' => 'active',
                'is_home' => true,
            ],
        );
        $this->reading($gps, $gpsScout, 'gps-location-now', [
            'metric_type' => 'location',
            'text_value' => 'Inside Home boundary',
            'recorded_at' => $now->subMinutes(2),
            'accuracy_meters' => 8,
            'confidence' => DeviceConfidence::High,
            'original_payload' => [
                'latitude' => 54.68916,
                'longitude' => 25.27083,
                'provider' => 'gps',
            ],
            'processed_payload' => ['safe_zone' => 'Home boundary'],
        ]);
        $this->reading($gps, $gpsScout, 'gps-battery-now', [
            'metric_type' => 'battery-percent',
            'numeric_value' => 82,
            'unit' => '%',
            'recorded_at' => $now->subMinutes(2),
            'confidence' => DeviceConfidence::High,
        ]);
        DeviceCommand::query()->updateOrCreate(
            ['idempotency_key' => '69381324-d76a-4a8b-85fb-5f647bb48379'],
            [
                'smart_device_id' => $gps->id,
                'author_key' => 'mia-carter',
                'author_name' => 'Mia Carter',
                'command_type' => 'start-walk',
                'parameters' => ['mode' => 'active-walk'],
                'status' => DeviceCommandStatus::Completed,
                'safety_level' => 'normal',
                'requires_confirmation' => false,
                'issued_at' => $now->subMinutes(38),
                'delivered_at' => $now->subMinutes(38),
                'completed_at' => $now->subMinutes(38),
                'result' => ['message' => 'Walk tracking started locally.'],
            ],
        );
        DeviceLifecycleRecord::query()->updateOrCreate(
            [
                'smart_device_id' => $gps->id,
                'kind' => 'firmware',
                'version_to' => '4.8.2',
            ],
            [
                'status' => 'completed',
                'created_by_key' => 'mia-carter',
                'version_from' => '4.8.1',
                'severity' => 'normal',
                'details' => [
                    'note' => 'Security and battery stability update verified locally.',
                    'reference' => 'DEMO-FW-482',
                ],
                'effective_at' => $now->subDays(12),
                'resolved_at' => $now->subDays(12),
            ],
        );
        DeviceAutomation::query()->updateOrCreate(
            [
                'smart_device_id' => $gps->id,
                'owner_key' => 'mia-carter',
                'name' => 'Confirm home-zone exit',
            ],
            [
                'trigger_type' => 'safe-zone-exit',
                'trigger_config' => ['zone' => 'Home boundary', 'signals' => 3],
                'condition_config' => ['home_mode' => 'any'],
                'action_type' => 'enable-lost-mode',
                'action_config' => ['manual_confirmation' => true],
                'status' => DeviceAutomationStatus::Enabled,
                'priority' => 'urgent',
                'safety_level' => 'guarded',
                'max_runs_per_hour' => 2,
                'cooldown_seconds' => 300,
                'version' => 1,
            ],
        );

        $activity = $this->device('scout-activity-band', [
            'name' => 'Scout Activity Band',
            'type' => DeviceType::ActivityTracker,
            'brand' => 'Pace & Rest',
            'model' => 'Motion Mini',
            'serial_number' => 'PR-ACT-0912',
            'public_zone_label' => 'With Scout',
            'private_location_label' => 'Scout collar',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'bluetooth',
            'firmware_version' => '2.3.1',
            'battery_percent' => 64,
            'signal_strength' => -62,
            'last_seen_at' => $now->subMinutes(4),
            'last_synced_at' => $now->subMinutes(4),
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $activityScout = $this->assign($activity, 'scout', 'Scout', true, 'collar');
        $this->reading($activity, $activityScout, 'activity-today', [
            'metric_type' => 'activity-minutes',
            'numeric_value' => 46,
            'unit' => 'min',
            'recorded_at' => $now->subMinutes(8),
            'confidence' => DeviceConfidence::Medium,
            'processed_payload' => ['method' => 'accelerometer estimate'],
        ]);
        $this->reading($activity, $activityScout, 'sleep-last-night', [
            'metric_type' => 'sleep-minutes',
            'numeric_value' => 438,
            'unit' => 'min',
            'recorded_at' => $now->setTime(6, 40),
            'confidence' => DeviceConfidence::Medium,
            'processed_payload' => ['method' => 'movement estimate'],
        ]);

        $feeder = $this->device('nori-kitchen-feeder', [
            'name' => 'Nori Kitchen Feeder',
            'type' => DeviceType::Feeder,
            'brand' => 'Portion Home',
            'model' => 'Feeder Duo',
            'serial_number' => 'PH-FD-7721',
            'public_zone_label' => 'Kitchen',
            'private_location_label' => 'Kitchen feeding corner',
            'status' => DeviceStatus::NeedsAttention,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'wi-fi',
            'firmware_version' => '5.1.0',
            'battery_percent' => null,
            'signal_strength' => -48,
            'last_seen_at' => $now->subMinute(),
            'last_synced_at' => $now->subMinute(),
            'has_backup_power' => true,
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $feederNori = $this->assign($feeder, 'nori', 'Nori', true, 'microchip');
        $this->reading($feeder, $feederNori, 'feeder-portion-1900', [
            'metric_type' => 'food-dispensed',
            'numeric_value' => 42,
            'unit' => 'g',
            'recorded_at' => $now->subMinutes(22),
            'confidence' => DeviceConfidence::High,
            'processed_payload' => [
                'scheduled_grams' => 60,
                'confirmed_eaten' => false,
            ],
        ]);
        $this->event($feeder, $feederNori, 'feeder-jam-1900', [
            'type' => 'feeding-failed',
            'severity' => DeviceEventSeverity::Important,
            'title' => 'Feeder dispensed less than planned',
            'summary' => 'The feeder released 42 g of the scheduled 60 g. Nori is not marked as fed.',
            'details' => ['scheduled_grams' => 60, 'dispensed_grams' => 42],
            'occurred_at' => $now->subMinutes(22),
            'confidence' => DeviceConfidence::High,
        ]);

        $waterer = $this->device('kitchen-water-fountain', [
            'name' => 'Kitchen Water Fountain',
            'type' => DeviceType::Waterer,
            'brand' => 'ClearDrop',
            'model' => 'Flow S2',
            'serial_number' => 'CD-FLOW-1188',
            'public_zone_label' => 'Kitchen',
            'private_location_label' => 'Kitchen water station',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'wi-fi',
            'firmware_version' => '3.7.4',
            'signal_strength' => -51,
            'last_seen_at' => $now->subMinutes(3),
            'last_synced_at' => $now->subMinutes(3),
            'has_backup_power' => false,
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $this->assign($waterer, 'scout', 'Scout', true, 'shared');
        $this->assign($waterer, 'nori', 'Nori', false, 'shared');
        $this->reading($waterer, null, 'water-shared-today', [
            'metric_type' => 'water-use',
            'numeric_value' => 730,
            'unit' => 'ml',
            'recorded_at' => $now->subMinutes(11),
            'confidence' => DeviceConfidence::Low,
            'processed_payload' => [
                'individual_consumption_known' => false,
                'possible_spill_ml' => 40,
            ],
        ]);

        $camera = $this->device('living-room-pet-camera', [
            'name' => 'Living Room Pet Camera',
            'type' => DeviceType::Camera,
            'brand' => 'QuietLook',
            'model' => 'Home Eye',
            'serial_number' => 'QL-CAM-4420',
            'public_zone_label' => 'Living room',
            'private_location_label' => 'Living room shelf facing pet area',
            'status' => DeviceStatus::PrivacyMode,
            'connection_status' => DeviceConnectionStatus::Online,
            'operating_mode' => 'privacy',
            'connection_type' => 'wi-fi',
            'firmware_version' => '8.0.3',
            'signal_strength' => -44,
            'last_seen_at' => $now->subMinutes(2),
            'last_synced_at' => $now->subMinutes(2),
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $cameraScout = $this->assign($camera, 'scout', 'Scout', true, 'camera');
        $this->assign($camera, 'nori', 'Nori', false, 'camera');
        $this->event($camera, $cameraScout, 'camera-privacy-home', [
            'type' => 'privacy-mode-enabled',
            'severity' => DeviceEventSeverity::Routine,
            'status' => 'resolved',
            'title' => 'Camera lens closed for household privacy',
            'summary' => 'The home mode rule closed the physical camera shutter.',
            'occurred_at' => $now->subMinutes(48),
            'confidence' => DeviceConfidence::High,
            'requires_attention' => false,
        ]);

        $litter = $this->device('nori-hallway-litter', [
            'name' => 'Nori Hallway Litter Box',
            'type' => DeviceType::LitterBox,
            'brand' => 'CleanStep',
            'model' => 'Sense One',
            'serial_number' => 'CS-LB-2204',
            'public_zone_label' => 'Hallway',
            'private_location_label' => 'Hallway litter area',
            'status' => DeviceStatus::NeedsAttention,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'wi-fi',
            'firmware_version' => '6.4.0',
            'battery_percent' => 71,
            'signal_strength' => -66,
            'last_seen_at' => $now->subMinutes(1),
            'last_synced_at' => $now->subMinutes(1),
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $litterNori = $this->assign($litter, 'nori', 'Nori', true, 'weight-camera');
        $this->reading($litter, $litterNori, 'litter-visits-2h', [
            'metric_type' => 'litter-visit',
            'numeric_value' => 8,
            'unit' => 'visits',
            'recorded_at' => $now->subMinutes(5),
            'confidence' => DeviceConfidence::Medium,
            'processed_payload' => ['confirmed_result' => false, 'window_hours' => 2],
        ]);
        $this->event($litter, $litterNori, 'litter-frequent-visits', [
            'type' => 'frequent-litter-visits',
            'severity' => DeviceEventSeverity::Urgent,
            'title' => 'Frequent short litter-box visits',
            'summary' => 'Eight visits were detected in two hours. Check Nori and contact a clinic urgently if urination is difficult.',
            'details' => ['visits' => 8, 'window_hours' => 2, 'result_confirmed' => false],
            'occurred_at' => $now->subMinutes(5),
            'confidence' => DeviceConfidence::Medium,
        ]);

        $scale = $this->device('nori-window-scale', [
            'name' => 'Nori Window Scale',
            'type' => DeviceType::Scale,
            'brand' => 'PetMetric',
            'model' => 'Step Light',
            'serial_number' => 'PM-SC-9032',
            'public_zone_label' => 'Quiet room',
            'private_location_label' => 'Window platform in the quiet room',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'bluetooth',
            'firmware_version' => '1.9.6',
            'battery_percent' => 46,
            'signal_strength' => -73,
            'last_seen_at' => $now->subHours(3),
            'last_synced_at' => $now->subHours(3),
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $scaleNori = $this->assign($scale, 'nori', 'Nori', true, 'camera-weight');
        $this->reading($scale, $scaleNori, 'nori-weight-today', [
            'metric_type' => 'weight-grams',
            'numeric_value' => 4720,
            'unit' => 'g',
            'recorded_at' => $now->subHours(3),
            'confidence' => DeviceConfidence::Medium,
            'processed_payload' => ['stable_seconds' => 4, 'calibration' => 'current'],
        ]);

        $temperature = $this->device('pet-room-temperature', [
            'name' => 'Pet Room Temperature',
            'type' => DeviceType::TemperatureSensor,
            'brand' => 'HomeClimate',
            'model' => 'Spot T',
            'serial_number' => 'HC-T-8173',
            'public_zone_label' => 'Pet room',
            'private_location_label' => 'Pet room wall away from direct sunlight',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'connection_type' => 'thread',
            'firmware_version' => '3.1.2',
            'battery_percent' => 37,
            'signal_strength' => -59,
            'last_seen_at' => $now->subMinutes(1),
            'last_synced_at' => $now->subMinutes(1),
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $this->assign($temperature, 'scout', 'Scout', true, 'shared-zone');
        $this->assign($temperature, 'nori', 'Nori', false, 'shared-zone');
        $this->reading($temperature, null, 'pet-room-temperature-now', [
            'metric_type' => 'temperature-c',
            'numeric_value' => 21.8,
            'unit' => '°C',
            'recorded_at' => $now->subMinute(),
            'confidence' => DeviceConfidence::High,
            'processed_payload' => ['range_min' => 18, 'range_max' => 25],
        ]);

        $door = $this->device('garden-pet-door', [
            'name' => 'Garden Pet Door',
            'type' => DeviceType::SmartDoor,
            'brand' => 'SafePass',
            'model' => 'Microchip Door',
            'serial_number' => 'SP-DOOR-5581',
            'public_zone_label' => 'Garden access',
            'private_location_label' => 'Rear garden door',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'operating_mode' => 'entry-only',
            'connection_type' => 'wi-fi-local',
            'firmware_version' => '7.2.5',
            'battery_percent' => 93,
            'signal_strength' => -55,
            'last_seen_at' => $now->subMinutes(2),
            'last_synced_at' => $now->subMinutes(2),
            'has_backup_power' => true,
            'supports_local_operation' => true,
            'requires_cloud' => false,
        ]);
        $doorScout = $this->assign($door, 'scout', 'Scout', true, 'microchip');
        $this->event($door, $doorScout, 'door-entry-only', [
            'type' => 'door-mode-changed',
            'severity' => DeviceEventSeverity::Routine,
            'status' => 'resolved',
            'title' => 'Door set to entry-only mode',
            'summary' => 'Scout can return through the door, but remote exit is blocked.',
            'occurred_at' => $now->subHours(2),
            'confidence' => DeviceConfidence::High,
            'requires_attention' => false,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function device(string $slug, array $attributes): SmartDevice
    {
        return SmartDevice::query()->updateOrCreate(
            ['owner_key' => 'mia-carter', 'slug' => $slug],
            [
                'privacy' => 'private',
                'operating_mode' => 'normal',
                'has_backup_power' => false,
                'supports_local_operation' => false,
                'requires_cloud' => true,
                'is_medical_device' => false,
                'is_blocked' => false,
                'is_reported_stolen' => false,
                'lock_version' => 1,
                ...$attributes,
            ],
        );
    }

    private function assign(
        SmartDevice $device,
        string $petKey,
        string $petName,
        bool $primary,
        string $method,
    ): DevicePetAssignment {
        return DevicePetAssignment::query()->updateOrCreate(
            [
                'smart_device_id' => $device->id,
                'pet_profile_key' => $petKey,
            ],
            [
                'pet_name' => $petName,
                'relationship_type' => $method === 'shared-zone' ? 'shared-zone' : 'assigned',
                'identification_method' => $method,
                'confidence' => in_array($method, ['shared', 'shared-zone'], true)
                    ? DeviceConfidence::Unknown
                    : DeviceConfidence::High,
                'is_primary' => $primary,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function reading(
        SmartDevice $device,
        ?DevicePetAssignment $assignment,
        string $externalId,
        array $attributes,
    ): DeviceReading {
        return DeviceReading::query()->updateOrCreate(
            [
                'smart_device_id' => $device->id,
                'external_event_id' => $externalId,
            ],
            [
                'device_pet_assignment_id' => $assignment?->id,
                'pet_profile_key' => $assignment?->pet_profile_key,
                'pet_name' => $assignment?->pet_name,
                'timezone' => 'Europe/Vilnius',
                'confidence' => DeviceConfidence::Unknown,
                'verification_status' => 'device-unverified',
                'original_payload' => ['source' => 'demo-device'],
                'processed_payload' => ['method' => 'device estimate'],
                'is_stale' => false,
                ...$attributes,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function event(
        SmartDevice $device,
        ?DevicePetAssignment $assignment,
        string $externalId,
        array $attributes,
    ): DeviceEvent {
        return DeviceEvent::query()->updateOrCreate(
            [
                'smart_device_id' => $device->id,
                'external_event_id' => $externalId,
            ],
            [
                'device_pet_assignment_id' => $assignment?->id,
                'pet_profile_key' => $assignment?->pet_profile_key,
                'pet_name' => $assignment?->pet_name,
                'status' => 'open',
                'details' => ['source' => 'demo-device'],
                'timezone' => 'Europe/Vilnius',
                'confidence' => DeviceConfidence::Unknown,
                'source' => 'device',
                'requires_attention' => true,
                ...$attributes,
            ],
        );
    }
}
