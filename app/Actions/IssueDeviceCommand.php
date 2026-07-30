<?php

declare(strict_types=1);

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
                    'command_type' => __('messages.this_command_is_not_allowed_by_the_platform_safety_polic_d02835f564'),
                ]);
            }

            $existing = DeviceCommand::query()
                ->select(['id', 'smart_device_id'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing !== null) {
                if ($existing->smart_device_id !== $device->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('messages.this_command_key_is_already_in_use_d64c47642c'),
                    ]);
                }

                return DeviceCommand::query()->findOrFail($existing->id);
            }

            $lockedDevice = SmartDevice::query()
                ->select([
                    'id', 'owner_key', 'name', 'type', 'status',
                    'connection_status', 'operating_mode', 'battery_percent',
                    'is_blocked', 'is_reported_stolen', 'safety_state',
                    'safety_state_recorded_at', 'lock_version',
                ])
                ->lockForUpdate()
                ->findOrFail($device->id);

            if ($lockedDevice->is_blocked) {
                throw ValidationException::withMessages([
                    'command_type' => __('messages.remote_control_is_blocked_for_this_device_c0b2dc51f0'),
                ]);
            }

            $this->guardCapability($lockedDevice, $data['command_type']);
            $this->guardConfirmation($data);
            $this->guardSafetyInterlocks($lockedDevice, $data['command_type']);
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
                'status' => DeviceCommandStatus::Accepted,
                'safety_level' => $this->safetyLevel($data['command_type']),
                'requires_confirmation' => $this->requiresConfirmation($data['command_type']),
                'confirmed_at' => ($data['confirmed'] ?? false) ? now() : null,
                'issued_at' => now(),
                'expires_at' => now()->addMinutes(5),
                'result' => [
                    'platform_state_updated' => $this->updatesPlatformState($data['command_type']),
                    'device_execution_confirmed' => false,
                    'message' => __('messages.command_accepted_device_execution_not_yet_confirmed'),
                ],
            ]);

            $this->applyDeviceState($lockedDevice, $command);
            $this->recordCommandEvent($lockedDevice, $command);
            $lockedDevice->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-command.accepted',
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
            DeviceType::LitterBox => ['refresh-status', 'clean-litter'],
            DeviceType::SmartDoor => ['refresh-status', 'lock-door', 'unlock-door'],
            default => ['refresh-status'],
        };

        if (! in_array($command, $allowed, true)) {
            throw ValidationException::withMessages([
                'command_type' => __('messages.this_command_is_not_supported_by_the_selected_device_typ_60d0f37c98'),
            ]);
        }
    }

    private function guardSafetyInterlocks(SmartDevice $device, string $command): void
    {
        $requiredClearKeys = match ($command) {
            'lock-door', 'unlock-door' => ['pet_in_doorway', 'obstruction_detected'],
            'clean-litter' => ['pet_present'],
            'start-water-pump' => ['leak_detected'],
            default => [],
        };

        if (
            $requiredClearKeys !== []
            && ! $device->hasFreshClearInterlocks($requiredClearKeys)
        ) {
            throw ValidationException::withMessages([
                'command_type' => __('messages.device_safety_interlock_is_missing_stale_or_not_clear'),
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
                'confirmed' => __('messages.confirm_this_high_impact_command_before_it_is_sent_78f41d9b30'),
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
                    __('messages.a_portion_was_already_issued_by_s_at_s_confirm_only_if_a_7e62030100'),
                    $recent->author_name,
                    $recent->issued_at?->format('H:i:s'),
                ),
            ]);
        }
    }

    private function requiresConfirmation(string $command): bool
    {
        return in_array($command, [
            'unlock-door',
            'lock-door',
            'clean-litter',
            'enable-lost-mode',
        ], true);
    }

    private function safetyLevel(string $command): string
    {
        return match ($command) {
            'unlock-door', 'lock-door', 'clean-litter' => 'critical',
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
            default => [],
        };

        if ($updates !== []) {
            $device->forceFill($updates)->save();
        }
    }

    private function updatesPlatformState(string $command): bool
    {
        return in_array($command, [
            'enable-lost-mode',
            'disable-lost-mode',
            'enable-privacy-mode',
            'disable-privacy-mode',
        ], true);
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
            'occurrence_count' => 1,
            'first_occurred_at' => $command->issued_at,
            'last_occurred_at' => $command->issued_at,
            'title' => match ($command->command_type) {
                'dispense-food' => __('messages.portion_dispensed_eating_unconfirmed'),
                'enable-lost-mode' => __('messages.lost_mode_enabled'),
                'unlock-door' => __('messages.pet_door_unlocked'),
                default => str($command->command_type)->headline()->toString(),
            },
            'summary' => __('messages.device_command_accepted_physical_outcome_unconfirmed'),
            'details' => [
                'command_id' => $command->id,
                ...($command->parameters ?? []),
            ],
            'occurred_at' => $command->issued_at,
            'timezone' => 'Europe/Vilnius',
            'confidence' => 'high',
            'source' => 'device-command',
            'requires_attention' => $command->safety_level !== 'normal',
        ]);
    }
}
