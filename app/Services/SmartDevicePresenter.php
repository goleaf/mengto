<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeviceAutomationStatus;
use App\Enums\DeviceLifecycleKind;
use App\Enums\DeviceLifecycleStatus;
use App\Enums\DeviceType;
use App\Models\AuditLog;
use App\Models\CareJournal;
use App\Models\DeviceAccessGrant;
use App\Models\DeviceAutomation;
use App\Models\DeviceAutomationRun;
use App\Models\DeviceCommand;
use App\Models\DeviceEvent;
use App\Models\DeviceLifecycleRecord;
use App\Models\DeviceReading;
use App\Models\DeviceSafeZone;
use App\Models\MedicalRecord;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SmartDevicePresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly LocaleFormatter $formatter,
    ) {}

    /** @return array<string, mixed> */
    public function directory(): array
    {
        $devices = SmartDevice::query()
            ->forOwnerDirectory($this->actor->key())
            ->with([
                'assignments' => fn ($assignments) => $assignments->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'confidence', 'is_primary',
                ]),
            ])
            ->withCount([
                'events as open_events_count' => fn (Builder $query): Builder => $query
                    ->where('status', 'open'),
                'events as urgent_events_count' => fn (Builder $query): Builder => $query
                    ->where('status', 'open')
                    ->whereIn('severity', ['urgent', 'critical']),
                'automations as enabled_automations_count' => fn (Builder $query): Builder => $query
                    ->where('status', DeviceAutomationStatus::Enabled->value),
            ])
            ->latest('updated_at')
            ->latest('id')
            ->simplePaginate(12)
            ->withQueryString();

        $pageCollection = $devices->getCollection();
        $summary = [
            'total' => $pageCollection->count(),
            'online' => $pageCollection
                ->filter(fn (SmartDevice $device): bool => (
                    $device->connection_status->value === 'online'
                ))
                ->count(),
            'needs_attention' => $pageCollection
                ->filter(fn (SmartDevice $device): bool => (
                    (int) $device->open_events_count > 0
                    || $device->status->value === 'needs-attention'
                ))
                ->count(),
            'low_battery' => $pageCollection
                ->filter(fn (SmartDevice $device): bool => (
                    $device->battery_percent !== null
                    && $device->battery_percent < 30
                ))
                ->count(),
        ];
        $devices->through(fn (SmartDevice $device): array => $this->deviceCard($device));

        return [
            ...$this->page(__('messages.private_smart_devices')),
            'devices' => $devices,
            'summary' => $summary,
        ];
    }

    /** @return array<string, mixed> */
    public function editor(): array
    {
        $connectionTypes = [
            'wi-fi',
            'bluetooth',
            'cellular',
            'radio',
            'matter',
            'manual',
        ];

        return [
            ...$this->page(__('messages.connect_a_smart_device')),
            'device_types' => collect(DeviceType::cases())
                ->map(fn (DeviceType $type): array => [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'icon' => $type->icon(),
                ])
                ->all(),
            'connection_types' => $this->labelledOptions(
                $connectionTypes,
                'devices.connection_type',
            ),
            'pets' => collect(['scout', 'nori'])
                ->map(function (string $key): array {
                    $pet = $this->profiles->pet($key);

                    return [
                        'key' => $key,
                        'name' => $pet['name'],
                        'species' => $pet['species'],
                    ];
                })
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function show(SmartDevice $device): array
    {
        $assignments = $device->assignments()
            ->select([
                'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                'relationship_type', 'identification_method', 'confidence',
                'is_primary',
            ])
            ->orderByDesc('is_primary')
            ->get();
        $readings = $device->readings()
            ->select([
                'id', 'smart_device_id', 'device_pet_assignment_id',
                'pet_profile_key', 'pet_name', 'metric_type', 'numeric_value',
                'text_value', 'unit', 'recorded_at', 'timezone',
                'accuracy_meters', 'confidence', 'verification_status',
                'is_stale', 'care_entry_id', 'medical_event_id',
                'weight_entry_id',
            ])
            ->latest('recorded_at')
            ->limit(30)
            ->get();
        $events = $device->events()
            ->select([
                'id', 'smart_device_id', 'pet_profile_key', 'pet_name', 'type',
                'severity', 'status', 'occurrence_count', 'first_occurred_at',
                'last_occurred_at', 'title', 'summary', 'details',
                'occurred_at', 'timezone', 'confidence', 'source',
                'requires_attention', 'acknowledged_at', 'care_entry_id',
                'search_case_id',
            ])
            ->latest('occurred_at')
            ->limit(20)
            ->get();
        $commands = $device->commands()
            ->select([
                'id', 'smart_device_id', 'author_name', 'command_type',
                'parameters', 'status', 'safety_level', 'issued_at',
                'completed_at', 'result',
            ])
            ->latest('issued_at')
            ->limit(12)
            ->get();
        $zones = $device->safeZones()
            ->select([
                'id', 'smart_device_id', 'name', 'shape', 'public_area_label',
                'exact_geometry', 'schedule', 'exit_delay_seconds',
                'accuracy_threshold_meters', 'status', 'is_home',
            ])
            ->orderByDesc('is_home')
            ->limit(10)
            ->get();
        $automations = $device->automations()
            ->select([
                'id', 'smart_device_id', 'owner_key', 'name', 'trigger_type',
                'trigger_config', 'condition_config', 'action_type',
                'action_config', 'status', 'priority', 'safety_level',
                'max_runs_per_hour', 'cooldown_seconds', 'last_run_at',
                'version',
            ])
            ->latest('updated_at')
            ->limit(12)
            ->get();
        $careProfiles = CareJournal::query()
            ->select(['id', 'pet_profile_key', 'slug'])
            ->where('owner_key', $this->actor->key())
            ->whereIn('pet_profile_key', $assignments->pluck('pet_profile_key'))
            ->get()
            ->keyBy('pet_profile_key');
        $medicalProfiles = MedicalRecord::query()
            ->select(['id', 'pet_profile_key', 'slug'])
            ->where('owner_key', $this->actor->key())
            ->whereIn('pet_profile_key', $assignments->pluck('pet_profile_key'))
            ->get()
            ->keyBy('pet_profile_key');

        return [
            ...$this->page($device->name),
            'device' => $this->device($device),
            'assignments' => $assignments
                ->map(fn ($assignment): array => [
                    'pet_profile_key' => $assignment->pet_profile_key,
                    'pet_name' => $assignment->pet_name,
                    'relationship' => Str::headline($assignment->relationship_type),
                    'identification' => Str::headline($assignment->identification_method),
                    'confidence' => $assignment->confidence->label(),
                    'is_primary' => $assignment->is_primary,
                ])
                ->all(),
            'readings' => $readings
                ->map(fn (DeviceReading $reading): array => $this->reading(
                    $reading,
                    $medicalProfiles,
                ))
                ->all(),
            'events' => $events
                ->map(fn (DeviceEvent $event): array => $this->event(
                    $device,
                    $event,
                    $careProfiles,
                ))
                ->all(),
            'commands' => $commands
                ->map(fn (DeviceCommand $command): array => $this->command($command))
                ->all(),
            'safe_zones' => $zones
                ->map(fn (DeviceSafeZone $zone): array => $this->safeZone($zone, true))
                ->all(),
            'automations' => $automations
                ->map(fn (DeviceAutomation $automation): array => $this->automation($automation))
                ->all(),
            'command_options' => $this->commandOptions($device->type),
            'metric_options' => $this->metricOptions($device->type),
            'command_idempotency_key' => (string) Str::uuid(),
            'reading_external_id' => 'manual-'.Str::uuid(),
            'now_local' => now('Europe/Vilnius')->startOfMinute()->format('Y-m-d\TH:i'),
        ];
    }

    /** @return array<string, mixed> */
    public function manage(SmartDevice $device): array
    {
        $access = $device->accessGrants()
            ->select([
                'id', 'smart_device_id', 'recipient_name', 'recipient_role',
                'label', 'permissions', 'allow_location', 'allow_camera',
                'allow_commands', 'allow_audio', 'max_views', 'views_used',
                'starts_at', 'expires_at', 'last_opened_at', 'revoked_at',
                'created_at',
            ])
            ->latest()
            ->limit(20)
            ->get();
        $automations = $device->automations()
            ->select([
                'id', 'smart_device_id', 'owner_key', 'name', 'trigger_type',
                'trigger_config', 'condition_config', 'action_type',
                'action_config', 'status', 'priority', 'safety_level',
                'max_runs_per_hour', 'cooldown_seconds', 'last_run_at',
                'version', 'updated_at',
            ])
            ->latest('updated_at')
            ->limit(20)
            ->get();
        $runs = DeviceAutomationRun::query()
            ->select([
                'id', 'device_automation_id', 'smart_device_id',
                'status', 'is_simulation', 'started_at', 'completed_at',
                'result', 'error',
            ])
            ->where('smart_device_id', $device->id)
            ->latest('started_at')
            ->limit(20)
            ->get();
        $lifecycleRecords = $device->lifecycleRecords()
            ->select([
                'id', 'smart_device_id', 'kind', 'status', 'created_by_key',
                'version_from', 'version_to', 'severity', 'details',
                'effective_at', 'resolved_at',
            ])
            ->latest('effective_at')
            ->limit(20)
            ->get();
        $audit = AuditLog::query()
            ->select([
                'id', 'actor_key', 'actor_role', 'action', 'target_type',
                'target_id', 'metadata', 'created_at',
            ])
            ->whereIn('action', [
                'smart-device.created',
                'device-reading.recorded',
                'device-command.accepted',
                'device-lifecycle.recorded',
                'device-retention.updated',
                'device-event.acknowledged',
                'device-safe-zone.created',
                'device-automation.created',
                'device-automation.simulated',
                'device-access.created',
                'device-access.opened',
                'device-access.revoked',
            ])
            ->where(function (Builder $query) use ($device): void {
                $query
                    ->where(fn (Builder $nested): Builder => $nested
                        ->where('target_type', SmartDevice::class)
                        ->where('target_id', (string) $device->id))
                    ->orWhere('metadata', 'like', '%"smart_device_id":'.$device->id.'%');
            })
            ->latest()
            ->limit(30)
            ->get();

        return [
            ...$this->page(__('presentation.device_settings_for', ['device' => $device->name])),
            'device' => $this->device($device),
            'access_grants' => $access
                ->map(fn (DeviceAccessGrant $grant): array => $this->grant($grant))
                ->all(),
            'automations' => $automations
                ->map(fn (DeviceAutomation $automation): array => $this->automation($automation))
                ->all(),
            'automation_runs' => $runs
                ->map(fn (DeviceAutomationRun $run): array => $this->automationRun($run))
                ->all(),
            'lifecycle_records' => $lifecycleRecords
                ->map(fn (DeviceLifecycleRecord $record): array => $this->lifecycle($record))
                ->all(),
            'lifecycle_kinds' => collect(DeviceLifecycleKind::cases())
                ->map(fn (DeviceLifecycleKind $kind): array => [
                    'value' => $kind->value,
                    'label' => $kind->label(),
                ])
                ->all(),
            'lifecycle_statuses' => collect(DeviceLifecycleStatus::cases())
                ->map(fn (DeviceLifecycleStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->all(),
            'lifecycle_severities' => collect(['normal', 'important', 'critical'])
                ->map(fn (string $severity): array => [
                    'value' => $severity,
                    'label' => __("devices.severity.{$severity}"),
                ])
                ->all(),
            'retention_options' => [
                'location' => $this->retentionOptions([0, 1, 7, 30, 90, 365]),
                'media' => $this->retentionOptions([0, 1, 3, 7, 30, 90]),
                'telemetry' => $this->retentionOptions([30, 90, 365, 730]),
            ],
            'automation_triggers' => $this->labelledOptions([
                'safe-zone-exit',
                'battery-low',
                'device-offline',
                'feeding-failed',
                'water-low',
                'temperature-high',
                'temperature-low',
                'door-open',
                'leak-detected',
            ], 'devices.automation_trigger'),
            'automation_modes' => $this->labelledOptions([
                'any',
                'home',
                'away',
                'sitter',
                'night',
                'lost-mode',
            ], 'devices.automation_mode'),
            'automation_actions' => $this->labelledOptions([
                'send-notification',
                'create-task',
                'lock-door',
                'stop-water-pump',
                'enable-lost-mode',
            ], 'devices.automation_action'),
            'automation_priorities' => $this->labelledOptions([
                'normal',
                'important',
                'urgent',
                'critical',
            ], 'devices.automation_priority'),
            'access_permission_options' => $this->labelledOptions([
                'view-status',
                'view-readings',
                'view-events',
                'control',
            ], 'devices.access_permission'),
            'lifecycle_effective_at' => now()
                ->startOfMinute()
                ->format('Y-m-d\TH:i'),
            'audit' => $audit->map(fn (AuditLog $item): array => [
                'action' => Str::headline(str_replace('.', ' ', $item->action)),
                'actor' => Str::headline($item->actor_key),
                'role' => Str::headline($item->actor_role),
                'at' => $this->formatter->dateTime($item->created_at),
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function shared(DeviceAccessGrant $grant, string $token): array
    {
        $device = $grant->smartDevice;
        $readings = collect();
        $events = collect();

        if ($grant->can('view-readings')) {
            $readings = $device->readings()
                ->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'metric_type', 'numeric_value', 'text_value', 'unit',
                    'recorded_at', 'timezone', 'accuracy_meters', 'confidence',
                    'verification_status', 'is_stale', 'care_entry_id',
                    'medical_event_id', 'weight_entry_id',
                ])
                ->latest('recorded_at')
                ->limit(20)
                ->get();
        }

        if ($grant->can('view-events')) {
            $events = $device->events()
                ->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'type', 'severity', 'status', 'title', 'summary',
                    'occurrence_count', 'first_occurred_at', 'last_occurred_at',
                    'occurred_at', 'confidence', 'source',
                    'requires_attention', 'acknowledged_at',
                ])
                ->latest('occurred_at')
                ->limit(20)
                ->get();
        }

        $sharedDevice = $this->device($device);
        $sharedDevice['exact_location'] = null;
        $sharedDevice['location_label'] = $grant->allow_location
            ? $device->public_zone_label
            : __('messages.location_not_shared');
        $sharedDevice['camera_available'] = $grant->allow_camera
            && $device->type === DeviceType::Camera;
        $sharedDevice['audio_available'] = $grant->allow_audio;

        return [
            ...$this->page(__('presentation.device_shared_status_for', ['device' => $device->name])),
            'device' => $sharedDevice,
            'grant' => $this->grant($grant),
            'token' => $token,
            'readings' => $readings
                ->map(fn (DeviceReading $reading): array => $this->reading($reading, collect()))
                ->all(),
            'events' => $events
                ->map(fn (DeviceEvent $event): array => $this->event(
                    $device,
                    $event,
                    collect(),
                ))
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function page(string $title): array
    {
        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $title]),
            'active_section' => 'devices',
        ];
    }

    /** @return array<string, mixed> */
    private function device(SmartDevice $device): array
    {
        $latitude = $device->current_latitude;
        $longitude = $device->current_longitude;

        return [
            'id' => $device->id,
            'slug' => $device->slug,
            'name' => $device->name,
            'type' => $device->type->value,
            'type_label' => $device->type->label(),
            'icon' => $device->type->icon(),
            'brand_model' => trim(implode(' ', array_filter([$device->brand, $device->model]))),
            'serial' => $device->maskedSerialNumber(),
            'image_url' => $device->image_url,
            'privacy' => Str::headline($device->privacy),
            'status' => $device->status->value,
            'status_label' => $device->status->label(),
            'status_tone' => $device->status->tone(),
            'connection' => $device->connection_status->value,
            'connection_label' => $device->connection_status->label(),
            'connection_tone' => $device->connection_status->tone(),
            'operating_mode' => Str::headline($device->operating_mode),
            'connection_type' => $device->connection_type
                ? Str::headline($device->connection_type)
                : __('ui.not_recorded'),
            'provider_status' => __("devices.provider_status.{$device->provider_status}"),
            'firmware_version' => $device->firmware_version ?: __('ui.not_reported'),
            'battery_percent' => $device->battery_percent,
            'battery_label' => $device->battery_percent !== null
                ? $this->formatter->percent($device->battery_percent)
                : __('ui.not_reported'),
            'signal_strength' => $device->signal_strength,
            'last_seen' => $this->formatter->relative($device->last_seen_at)
                ?? __('ui.no_signal_yet'),
            'last_seen_exact' => $this->formatter->dateTime($device->last_seen_at),
            'last_synced' => $this->formatter->relative($device->last_synced_at)
                ?? __('ui.not_synced'),
            'last_location' => $this->formatter->relative($device->last_location_at)
                ?? __('ui.no_location_yet'),
            'location_label' => $device->private_location_label
                ?: $device->public_zone_label
                ?: __('ui.location_not_recorded'),
            'public_zone_label' => $device->public_zone_label
                ?: __('ui.private_zone'),
            'exact_location' => $latitude !== null && $longitude !== null
                ? __('presentation.coordinates', [
                    'latitude' => $this->formatter->number((float) $latitude, 5, 5),
                    'longitude' => $this->formatter->number((float) $longitude, 5, 5),
                ])
                : null,
            'location_accuracy' => $device->location_accuracy_meters !== null
                ? __('presentation.approximate_meters', ['count' => $this->formatter->number((float) $device->location_accuracy_meters)])
                : __('ui.accuracy_unavailable'),
            'location_retention_days' => $device->location_retention_days,
            'media_retention_days' => $device->media_retention_days,
            'telemetry_retention_days' => $device->telemetry_retention_days,
            'safety_state_recorded_at' => $this->formatter->dateTime(
                $device->safety_state_recorded_at,
            ),
            'has_fresh_safety_state' => $device->safety_state_recorded_at?->isAfter(
                now()->subMinutes(2),
            ) === true,
            'has_backup_power' => $device->has_backup_power,
            'supports_local_operation' => $device->supports_local_operation,
            'requires_cloud' => $device->requires_cloud,
            'is_medical_device' => $device->is_medical_device,
            'is_blocked' => $device->is_blocked,
            'is_reported_stolen' => $device->is_reported_stolen,
            'camera_available' => $device->type === DeviceType::Camera
                && $device->status->value !== 'privacy-mode',
            'audio_available' => false,
            'show_url' => route('devices.show', $device),
            'manage_url' => route('devices.manage', $device),
            'lost_found_url' => route('lost-found.index'),
        ];
    }

    /** @return array<string, mixed> */
    private function deviceCard(SmartDevice $device): array
    {
        $openEvents = (int) $device->open_events_count;
        $urgentEvents = (int) $device->urgent_events_count;
        $eventSummary = trans_choice(
            'devices.events.open',
            $openEvents,
            ['count' => $openEvents],
        );

        if ($urgentEvents > 0) {
            $eventSummary .= ' · '.trans_choice(
                'devices.events.urgent',
                $urgentEvents,
                ['count' => $urgentEvents],
            );
        }

        return [
            ...$this->device($device),
            'pets' => $device->assignments
                ->pluck('pet_name')
                ->values()
                ->all(),
            'open_events_count' => $openEvents,
            'urgent_events_count' => $urgentEvents,
            'event_summary' => $eventSummary,
            'enabled_automations_count' => (int) $device->enabled_automations_count,
        ];
    }

    /**
     * @param  Collection<string, MedicalRecord>  $medicalProfiles
     * @return array<string, mixed>
     */
    private function reading(DeviceReading $reading, Collection $medicalProfiles): array
    {
        $value = $reading->numeric_value !== null
            ? $this->formatter->number((float) $reading->numeric_value, 6)
            : $reading->text_value;
        $record = $reading->pet_profile_key !== null
            ? $medicalProfiles->get($reading->pet_profile_key)
            : null;

        return [
            'id' => $reading->id,
            'metric_type' => $reading->metric_type,
            'metric_label' => Str::headline($reading->metric_type),
            'value' => trim(($value ?? __('ui.not_reported')).' '.($reading->unit ?? '')),
            'pet_name' => $reading->pet_name ?: __('ui.pet_not_identified'),
            'recorded_at' => $this->formatter->dateTime($reading->recorded_at),
            'recorded_relative' => $this->formatter->relative($reading->recorded_at),
            'confidence' => $reading->confidence->label(),
            'verification' => Str::headline($reading->verification_status),
            'is_stale' => $reading->is_stale,
            'accuracy' => $reading->accuracy_meters !== null
                ? __('presentation.approximate_meters', ['count' => $this->formatter->number((float) $reading->accuracy_meters)])
                : null,
            'can_add_medical' => $record !== null
                && $reading->medical_event_id === null
                && $reading->weight_entry_id === null,
            'medical_url' => $record !== null
                ? route('medical-records.show', $record)
                : route('medical-records.index'),
        ];
    }

    /**
     * @param  Collection<string, CareJournal>  $careProfiles
     * @return array<string, mixed>
     */
    private function event(
        SmartDevice $device,
        DeviceEvent $event,
        Collection $careProfiles,
    ): array {
        $journal = $event->pet_profile_key !== null
            ? $careProfiles->get($event->pet_profile_key)
            : null;

        return [
            'id' => $event->id,
            'type' => $event->type,
            'type_label' => Str::headline($event->type),
            'severity' => $event->severity->value,
            'severity_label' => $event->severity->label(),
            'severity_tone' => $event->severity->tone(),
            'status' => $event->status,
            'status_label' => Str::headline($event->status),
            'occurrence_count' => $event->occurrence_count,
            'occurrence_label' => trans_choice(
                'devices.events.occurrences',
                $event->occurrence_count,
                ['count' => $event->occurrence_count],
            ),
            'first_occurred_at' => $this->formatter->dateTime(
                $event->first_occurred_at,
            ),
            'last_occurred_at' => $this->formatter->dateTime(
                $event->last_occurred_at,
            ),
            'title' => $event->title,
            'summary' => $event->summary,
            'pet_name' => $event->pet_name ?: __('ui.pet_not_identified'),
            'occurred_at' => $this->formatter->dateTime($event->occurred_at),
            'occurred_relative' => $this->formatter->relative($event->occurred_at),
            'confidence' => $event->confidence->label(),
            'source' => Str::headline($event->source),
            'requires_attention' => $event->requires_attention,
            'is_acknowledged' => $event->acknowledged_at !== null,
            'can_add_care' => $journal !== null && $event->care_entry_id === null,
            'care_url' => $journal !== null
                ? route('care-journals.show', $journal)
                : route('care-journals.index'),
            'acknowledge_url' => route('devices.events.acknowledge', [$device, $event]),
            'care_entry_url' => route('devices.events.care-entry', [$device, $event]),
        ];
    }

    /** @return array<string, mixed> */
    private function command(DeviceCommand $command): array
    {
        return [
            'id' => $command->id,
            'type' => $command->command_type,
            'type_label' => __("devices.command.{$command->command_type}"),
            'status' => $command->status->value,
            'status_label' => $command->status->label(),
            'author_name' => $command->author_name,
            'safety_level' => Str::headline($command->safety_level),
            'issued_at' => $this->formatter->dateTime($command->issued_at),
            'parameters' => collect($command->parameters ?? [])
                ->map(fn (mixed $value, string $key): array => [
                    'label' => Str::headline($key),
                    'value' => (string) $value,
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function lifecycle(DeviceLifecycleRecord $record): array
    {
        return [
            'id' => $record->id,
            'kind' => $record->kind->label(),
            'status' => $record->status->label(),
            'severity' => __("devices.severity.{$record->severity}"),
            'version' => trim(implode(' → ', array_filter([
                $record->version_from,
                $record->version_to,
            ]))),
            'note' => $record->details['note'] ?? null,
            'reference' => $record->details['reference'] ?? null,
            'effective_at' => $this->formatter->dateTime($record->effective_at),
            'resolved_at' => $this->formatter->dateTime($record->resolved_at),
        ];
    }

    /**
     * @param  list<int>  $days
     * @return list<array{value: int, label: string}>
     */
    private function retentionOptions(array $days): array
    {
        return collect($days)
            ->map(fn (int $value): array => [
                'value' => $value,
                'label' => $value === 0
                    ? __('devices.retention.disabled')
                    : trans_choice('devices.retention.days', $value, ['count' => $value]),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $values
     * @return list<array{value: string, label: string}>
     */
    private function labelledOptions(array $values, string $translationGroup): array
    {
        return array_map(
            static fn (string $value): array => [
                'value' => $value,
                'label' => __("{$translationGroup}.{$value}"),
            ],
            $values,
        );
    }

    /** @return array<string, mixed> */
    private function safeZone(DeviceSafeZone $zone, bool $showExact): array
    {
        $geometry = $showExact ? ($zone->exact_geometry ?? []) : [];

        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'shape' => Str::headline($zone->shape),
            'public_area_label' => $zone->public_area_label,
            'radius' => isset($geometry['radius_meters'])
                ? __('presentation.meters', ['count' => $this->formatter->number((float) $geometry['radius_meters'])])
                : null,
            'coordinates' => isset($geometry['latitude'], $geometry['longitude'])
                ? __('presentation.coordinates', [
                    'latitude' => $this->formatter->number((float) $geometry['latitude'], 5, 5),
                    'longitude' => $this->formatter->number((float) $geometry['longitude'], 5, 5),
                ])
                : null,
            'exit_delay' => __('presentation.seconds_confirmation', [
                'count' => $this->formatter->number($zone->exit_delay_seconds),
            ]),
            'accuracy_threshold' => __('presentation.ignore_accuracy_worse', [
                'distance' => __('presentation.meters', ['count' => $this->formatter->number((float) $zone->accuracy_threshold_meters)]),
            ]),
            'status' => Str::headline($zone->status),
            'is_home' => $zone->is_home,
        ];
    }

    /** @return array<string, mixed> */
    private function automation(DeviceAutomation $automation): array
    {
        return [
            'id' => $automation->id,
            'name' => $automation->name,
            'trigger' => Str::headline($automation->trigger_type),
            'condition' => Str::headline(
                (string) ($automation->condition_config['home_mode'] ?? 'any'),
            ),
            'action' => Str::headline($automation->action_type),
            'status' => $automation->status->value,
            'status_label' => $automation->status->label(),
            'status_tone' => $automation->status->tone(),
            'priority' => Str::headline($automation->priority),
            'safety_level' => Str::headline($automation->safety_level),
            'cooldown' => __('presentation.seconds_short', [
                'count' => $this->formatter->number($automation->cooldown_seconds),
            ]),
            'max_runs' => __('presentation.runs_per_hour', [
                'count' => $this->formatter->number($automation->max_runs_per_hour),
            ]),
            'last_run' => $this->formatter->relative($automation->last_run_at)
                ?? __('ui.never_run'),
        ];
    }

    /** @return array<string, mixed> */
    private function automationRun(DeviceAutomationRun $run): array
    {
        return [
            'id' => $run->id,
            'automation_id' => $run->device_automation_id,
            'status' => Str::headline($run->status),
            'is_simulation' => $run->is_simulation,
            'started_at' => $this->formatter->dateTime($run->started_at),
            'result' => $run->result['message'] ?? __('ui.no_result_message'),
        ];
    }

    /** @return array<string, mixed> */
    private function grant(DeviceAccessGrant $grant): array
    {
        return [
            'id' => $grant->id,
            'recipient_name' => $grant->recipient_name,
            'recipient_role' => Str::headline($grant->recipient_role),
            'label' => $grant->label,
            'permissions' => collect($grant->permissions ?? [])
                ->map(fn (string $permission): string => Str::headline($permission))
                ->all(),
            'allow_location' => $grant->allow_location,
            'allow_camera' => $grant->allow_camera,
            'allow_commands' => $grant->allow_commands,
            'allow_audio' => $grant->allow_audio,
            'views' => $this->formatter->number($grant->views_used)
                .' / '.$this->formatter->number($grant->max_views),
            'starts_at' => $this->formatter->dateTime($grant->starts_at),
            'expires_at' => $this->formatter->dateTime($grant->expires_at),
            'last_opened' => $this->formatter->relative($grant->last_opened_at)
                ?? __('ui.never'),
            'is_active' => $grant->canBeOpened(),
        ];
    }

    /** @return array<int, array{value: string, label: string, confirmation: bool}> */
    private function commandOptions(DeviceType $type): array
    {
        $commands = match ($type) {
            DeviceType::GpsTracker => [
                'refresh-status', 'locate-device', 'enable-lost-mode',
                'disable-lost-mode',
            ],
            DeviceType::Feeder => ['refresh-status', 'dispense-food'],
            DeviceType::Waterer => [
                'refresh-status', 'stop-water-pump', 'start-water-pump',
            ],
            DeviceType::Camera => [
                'refresh-status', 'enable-privacy-mode', 'disable-privacy-mode',
            ],
            DeviceType::LitterBox => ['refresh-status', 'clean-litter'],
            DeviceType::SmartDoor => ['refresh-status', 'lock-door', 'unlock-door'],
            default => ['refresh-status'],
        };

        return collect($commands)->map(fn (string $command): array => [
            'value' => $command,
            'label' => __("devices.command.{$command}"),
            'confirmation' => in_array($command, [
                'unlock-door',
                'lock-door',
                'clean-litter',
                'enable-lost-mode',
            ], true),
        ])->all();
    }

    /** @return array<int, array{value: string, label: string, unit: string}> */
    private function metricOptions(DeviceType $type): array
    {
        $metrics = match ($type) {
            DeviceType::GpsTracker => [['location', __('messages.location'), 'coordinates'], ['battery-percent', __('messages.battery'), '%']],
            DeviceType::ActivityTracker => [['activity-minutes', __('messages.activity'), 'min'], ['sleep-minutes', __('messages.sleep'), 'min']],
            DeviceType::Feeder => [['food-dispensed', __('messages.food_dispensed'), 'g']],
            DeviceType::Waterer => [['water-use', __('messages.water_use'), 'ml']],
            DeviceType::LitterBox => [['litter-visit', __('messages.litter_visit'), 'times'], ['weight-grams', __('messages.weight'), 'g']],
            DeviceType::Scale => [['weight-grams', __('messages.weight'), 'g']],
            DeviceType::TemperatureSensor => [['temperature-c', __('messages.temperature'), '°C']],
            DeviceType::HumiditySensor => [['humidity-percent', __('messages.humidity'), '%']],
            DeviceType::SmartDoor => [['door-use', __('messages.door_use'), 'times']],
            DeviceType::Camera => [['activity-minutes', __('messages.observed_activity'), 'min'], ['sleep-minutes', __('messages.estimated_sleep'), 'min']],
        };

        return collect($metrics)->map(fn (array $metric): array => [
            'value' => $metric[0],
            'label' => $metric[1],
            'unit' => $metric[2],
        ])->all();
    }
}
