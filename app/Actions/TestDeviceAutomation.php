<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DeviceAutomation;
use App\Models\DeviceAutomationRun;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TestDeviceAutomation
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(
        SmartDevice $device,
        DeviceAutomation $automation,
    ): DeviceAutomationRun {
        if ($automation->smart_device_id !== $device->id) {
            throw ValidationException::withMessages([
                'automation' => 'This automation does not belong to the selected device.',
            ]);
        }

        return DB::transaction(function () use ($device, $automation): DeviceAutomationRun {
            $run = DeviceAutomationRun::query()->create([
                'device_automation_id' => $automation->id,
                'smart_device_id' => $device->id,
                'idempotency_key' => (string) Str::uuid(),
                'trigger_snapshot' => [
                    'type' => $automation->trigger_type,
                    'config' => $automation->trigger_config,
                    'condition' => $automation->condition_config,
                ],
                'action_snapshot' => [
                    'type' => $automation->action_type,
                    'config' => $automation->action_config,
                ],
                'status' => 'simulated',
                'is_simulation' => true,
                'started_at' => now(),
                'completed_at' => now(),
                'result' => [
                    'would_run' => true,
                    'real_command_sent' => false,
                    'message' => 'Conditions and recipients validated. No real command was sent.',
                ],
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-automation.simulated',
                'target_type' => DeviceAutomationRun::class,
                'target_id' => (string) $run->id,
                'metadata' => [
                    'smart_device_id' => $device->id,
                    'automation_id' => $automation->id,
                    'real_command_sent' => false,
                ],
            ]);

            return $run;
        });
    }
}
