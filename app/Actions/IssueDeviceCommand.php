<?php

namespace App\Actions;

use App\Enums\DeviceCommandStatus;
use App\Enums\DeviceEventSeverity;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\AuditLog;
use App\Models\DeviceCommand;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IssueDeviceCommand
{
    private const FORBIDDEN_COMMANDS = [
        'electroshock', 'shock', 'pain-stimulus', 'dispense-medication',
        'disable-ventilation', 'delete-video', 'factory-reset',
    ];

    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(SmartDevice $device, array $data): DeviceCommand
    {
        return DB::transaction(function () use ($device, $data): DeviceCommand {
            if (in_array($data['command_type'], self::FORBIDDEN_COMMANDS, true)) {
                throw ValidationException::withMessages([
                    'command_type' => 'This command is not allowed by the platform safety policy.',
                ]);
            }

            $existing = DeviceCommand::query()
                ->select(['id', 'smart_device_id'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing !== null) {
                if ($existing->smart_device_id !== $device->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This command key is already in use.',
                    ]);
                }

                return DeviceCommand::query()->findOrFail($existing->id);
            }

            $lockedDevice = SmartDevice::query()
                ->select([
                    'id', 'owner_key', 'name', 'type', 'status',
                    'connection_status', 'operating_mode', 'battery_percent',
                    'is_blocked', 'is_reported_stolen', 'lock_version',
                ])
                ->lockForUpdate()
                ->findOrFail($device->id);

            if ($lockedDevice->is_blocked || $lockedDevice->is_reported_stolen) {
                throw ValidationException::withMessages([
                    'command_type' => 'Remote control is blocked for this device.',
                ]);
            }

            $this->guardCapability($lockedDevice, $data['command_type']);
            $this->guardConfirmation($data);
            $this->guardDuplicateDispense($lockedDevice, $data);

            $command = DeviceCommand::query()->create([
                'smart_device_id' => $lockedDevice->id,
                'author_key' => $this->actor->key(),
                'author_name' => $this->actor->identity()['name'],
                'idempotency_key' => $data['idempotency_key'],
                'command_type' => $data['command_type'],
                'parameters' => array_filter([
                    'portion_grams' => $data['portion_grams'] ?? null,
                    'duration_minutes' => $data['duration_minutes'] ?? null,
                    'reason' => $data['reason'] ?? null,
                ], static fn (mixed $value): bool => $value !== null && $value !== ''),
                'status' => DeviceCommandStatus::Completed,
                'safety_level' => $this->safetyLevel($data['command_type']),
                'requires_confirmation' => $this->requiresConfirmation($data['command_type']),
                'confirmed_at' => ($data['confirmed'] ?? false) ? now() : null,
                'issued_at' => now(),
                'delivered_at' => now(),
                'completed_at' => now(),
                'expires_at' => now()->addMinutes(5),
                'result' => [
                    'adapter' => 'local-demo',
                    'message' => 'Command accepted and applied by the local demo adapter.',
                ],
            ]);

            $this->applyDeviceState($lockedDevice, $command);
            $this->recordCommandEvent($lockedDevice, $command);
            $lockedDevice->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-command.completed',
                'target_type' => DeviceCommand::class,
                'target_id' => (string) $command->id,
                'metadata' => [
                    'smart_device_id' => $lockedDevice->id,
                    'command_type' => $command->command_type,
                    'status' => $command->status->value,
                    'safety_level' => $command->safety_level,
                ],
            ]);

            return $command;
        });
    }

    private function guardCapability(SmartDevice $device, string $command): void
    {
        $allowed = match ($device->type) {
            DeviceType::GpsTracker => [
                'refresh-status', 'enable-lost-mode', 'disable-lost-mode',
                'locate-device',
            ],
            DeviceType::Feeder => ['refresh-status', 'dispense-food'],
            DeviceType::Waterer => [
                'refresh-status', 'stop-water-pump', 'start-water-pump',
            ],
            DeviceType::Camera => [
                'refresh-status', 'enable-privacy-mode', 'disable-privacy-mode',
            ],
            DeviceType::SmartDoor => ['refresh-status', 'lock-door', 'unlock-door'],
            default => ['refresh-status'],
        };

        if (! in_array($command, $allowed, true)) {
            throw ValidationException::withMessages([
                'command_type' => 'This command is not supported by the selected device type.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function guardConfirmation(array $data): void
    {
        if (
            $this->requiresConfirmation($data['command_type'])
            && ! ($data['confirmed'] ?? false)
        ) {
            throw ValidationException::withMessages([
                'confirmed' => 'Confirm this high-impact command before it is sent.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function guardDuplicateDispense(SmartDevice $device, array $data): void
    {
        if (
            $data['command_type'] !== 'dispense-food'
            || ($data['confirm_duplicate'] ?? false)
        ) {
            return;
        }

        $recent = DeviceCommand::query()
            ->select(['id', 'author_name', 'issued_at'])
            ->where('smart_device_id', $device->id)
            ->where('command_type', 'dispense-food')
            ->whereIn('status', [
                DeviceCommandStatus::Accepted->value,
                DeviceCommandStatus::Completed->value,
            ])
            ->where('issued_at', '>=', now()->subMinutes(5))
            ->latest('issued_at')
            ->first();

        if ($recent !== null) {
            throw ValidationException::withMessages([
                'confirm_duplicate' => sprintf(
                    'A portion was already issued by %s at %s. Confirm only if another portion is intended.',
                    $recent->author_name,
                    $recent->issued_at?->format('H:i:s'),
                ),
            ]);
        }
    }

    private function requiresConfirmation(string $command): bool
    {
        return in_array($command, ['unlock-door', 'enable-lost-mode'], true);
    }

    private function safetyLevel(string $command): string
    {
        return match ($command) {
            'unlock-door' => 'critical',
            'enable-lost-mode', 'dispense-food' => 'important',
            default => 'normal',
        };
    }

    private function applyDeviceState(SmartDevice $device, DeviceCommand $command): void
    {
        $updates = match ($command->command_type) {
            'enable-lost-mode' => [
                'status' => DeviceStatus::LostMode,
                'operating_mode' => 'lost-mode',
            ],
            'disable-lost-mode' => [
                'status' => DeviceStatus::Active,
                'operating_mode' => 'normal',
            ],
            'enable-privacy-mode' => [
                'status' => DeviceStatus::PrivacyMode,
                'operating_mode' => 'privacy-mode',
            ],
            'disable-privacy-mode' => [
                'status' => DeviceStatus::Active,
                'operating_mode' => 'normal',
            ],
            'lock-door' => ['operating_mode' => 'locked'],
            'unlock-door' => ['operating_mode' => 'unlocked'],
            'stop-water-pump' => ['operating_mode' => 'pump-stopped'],
            'start-water-pump' => ['operating_mode' => 'normal'],
            default => [],
        };

        if ($updates !== []) {
            $device->forceFill($updates)->save();
        }
    }

    private function recordCommandEvent(SmartDevice $device, DeviceCommand $command): void
    {
        $assignment = $device->assignments()
            ->select(['id', 'smart_device_id', 'pet_profile_key', 'pet_name', 'is_primary'])
            ->where('is_primary', true)
            ->first();

        $device->events()->create([
            'device_pet_assignment_id' => $assignment?->id,
            'pet_profile_key' => $assignment?->pet_profile_key,
            'pet_name' => $assignment?->pet_name,
            'external_event_id' => 'command:'.$command->idempotency_key,
            'type' => $command->command_type,
            'severity' => match ($command->safety_level) {
                'critical' => DeviceEventSeverity::Critical,
                'important' => DeviceEventSeverity::Important,
                default => DeviceEventSeverity::Routine,
            },
            'status' => 'open',
            'title' => match ($command->command_type) {
                'dispense-food' => 'Portion dispensed; eating not confirmed',
                'enable-lost-mode' => 'Lost mode enabled',
                'unlock-door' => 'Pet door unlocked by confirmed command',
                default => str($command->command_type)->headline()->toString(),
            },
            'summary' => 'A device command completed. Its physical outcome may still require a person to verify it.',
            'details' => [
                'command_id' => $command->id,
                ...($command->parameters ?? []),
            ],
            'occurred_at' => $command->completed_at,
            'timezone' => 'Europe/Vilnius',
            'confidence' => 'high',
            'source' => 'device-command',
            'requires_attention' => $command->safety_level !== 'normal',
        ]);
    }
}
