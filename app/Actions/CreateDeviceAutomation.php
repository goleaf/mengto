<?php

namespace App\Actions;

use App\Enums\DeviceAutomationStatus;
use App\Enums\DeviceType;
use App\Models\AuditLog;
use App\Models\DeviceAutomation;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateDeviceAutomation
{
    private const FORBIDDEN_ACTIONS = [
        'dispense-medication',
        'electroshock',
        'disable-ventilation',
        'open-door',
        'delete-video',
    ];

    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(SmartDevice $device, array $data): DeviceAutomation
    {
        return DB::transaction(function () use ($device, $data): DeviceAutomation {
            if (in_array($data['action_type'], self::FORBIDDEN_ACTIONS, true)) {
                throw ValidationException::withMessages([
                    'action_type' => __('messages.this_automatic_action_is_prohibited_9aceea4dba'),
                ]);
            }

            $this->guardDeviceCapability($device, $data['action_type']);

            $duplicate = DeviceAutomation::query()
                ->select(['id', 'name'])
                ->where('owner_key', $this->actor->key())
                ->where('smart_device_id', $device->id)
                ->where('trigger_type', $data['trigger_type'])
                ->where('action_type', $data['action_type'])
                ->whereIn('status', [
                    DeviceAutomationStatus::Draft->value,
                    DeviceAutomationStatus::Enabled->value,
                ])
                ->first();

            if ($duplicate !== null) {
                throw ValidationException::withMessages([
                    'action_type' => __('messages.a_rule_with_the_same_trigger_and_action_already_exists_90ba15948f'),
                ]);
            }

            $automation = DeviceAutomation::query()->create([
                'smart_device_id' => $device->id,
                'owner_key' => $this->actor->key(),
                'name' => $data['name'],
                'trigger_type' => $data['trigger_type'],
                'trigger_config' => ['value' => $data['trigger_value'] ?? null],
                'condition_config' => ['home_mode' => $data['condition_mode']],
                'action_type' => $data['action_type'],
                'action_config' => ['requires_manual_override' => true],
                'status' => DeviceAutomationStatus::from($data['status']),
                'priority' => $data['priority'],
                'safety_level' => in_array(
                    $data['action_type'],
                    ['lock-door', 'stop-water-pump', 'enable-lost-mode'],
                    true,
                ) ? 'guarded' : 'normal',
                'max_runs_per_hour' => $data['max_runs_per_hour'],
                'cooldown_seconds' => $data['cooldown_seconds'],
                'version' => 1,
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-automation.created',
                'target_type' => DeviceAutomation::class,
                'target_id' => (string) $automation->id,
                'metadata' => [
                    'smart_device_id' => $device->id,
                    'trigger_type' => $automation->trigger_type,
                    'action_type' => $automation->action_type,
                    'status' => $automation->status->value,
                ],
            ]);

            return $automation;
        });
    }

    private function guardDeviceCapability(SmartDevice $device, string $action): void
    {
        $valid = match ($action) {
            'lock-door' => $device->type === DeviceType::SmartDoor,
            'stop-water-pump' => $device->type === DeviceType::Waterer,
            'enable-lost-mode' => $device->type === DeviceType::GpsTracker,
            default => true,
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'action_type' => __('messages.this_automatic_action_does_not_match_the_device_type_ae326608bf'),
            ]);
        }
    }
}
