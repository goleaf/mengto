<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DeviceLifecycleKind;
use App\Enums\DeviceLifecycleStatus;
use App\Enums\DeviceStatus;
use App\Models\AuditLog;
use App\Models\DeviceLifecycleRecord;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class RecordDeviceLifecycle
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(SmartDevice $device, array $data): DeviceLifecycleRecord
    {
        return DB::transaction(function () use ($device, $data): DeviceLifecycleRecord {
            $lockedDevice = SmartDevice::query()
                ->select([
                    'id',
                    'firmware_version',
                    'status',
                    'operating_mode',
                    'is_blocked',
                    'is_reported_stolen',
                    'lock_version',
                ])
                ->lockForUpdate()
                ->findOrFail($device->id);
            $kind = DeviceLifecycleKind::from($data['kind']);
            $status = DeviceLifecycleStatus::from($data['status']);
            $effectiveAt = CarbonImmutable::parse($data['effective_at']);
            $record = $lockedDevice->lifecycleRecords()->create([
                'kind' => $kind,
                'status' => $status,
                'created_by_key' => $this->actor->key(),
                'version_from' => $data['version_from'] ?? null,
                'version_to' => $data['version_to'] ?? null,
                'severity' => $data['severity'],
                'details' => array_filter([
                    'note' => $data['note'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'consequences_reviewed' => (bool) ($data['consequences_reviewed'] ?? false),
                ], static fn (mixed $value): bool => $value !== null && $value !== ''),
                'effective_at' => $effectiveAt,
                'resolved_at' => $status->isResolved() ? now() : null,
            ]);

            $this->applyDeviceState($lockedDevice, $record, $data);
            $lockedDevice->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-lifecycle.recorded',
                'target_type' => DeviceLifecycleRecord::class,
                'target_id' => (string) $record->id,
                'metadata' => [
                    'smart_device_id' => $lockedDevice->id,
                    'kind' => $kind->value,
                    'status' => $status->value,
                    'severity' => $record->severity,
                ],
            ]);

            return $record;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    private function applyDeviceState(
        SmartDevice $device,
        DeviceLifecycleRecord $record,
        array $data,
    ): void {
        $updates = [];

        if (
            $record->kind === DeviceLifecycleKind::Firmware
            && $record->status === DeviceLifecycleStatus::Completed
            && isset($data['version_to'])
        ) {
            $updates['firmware_version'] = $data['version_to'];
        }

        if ($record->kind === DeviceLifecycleKind::Theft) {
            $reported = ! $record->status->isResolved();
            $updates['is_reported_stolen'] = $reported;
            $updates['status'] = $reported ? DeviceStatus::LostMode : DeviceStatus::Active;
            $updates['operating_mode'] = $reported ? 'lost-mode' : 'normal';
        }

        if (in_array($record->kind, [
            DeviceLifecycleKind::Recall,
            DeviceLifecycleKind::Vulnerability,
        ], true)) {
            $open = ! $record->status->isResolved();
            $updates['status'] = $open ? DeviceStatus::NeedsAttention : DeviceStatus::Active;
            $updates['is_blocked'] = $open
                && $record->severity === 'critical'
                && (bool) ($data['block_remote_control'] ?? false);
        }

        if (
            $record->kind === DeviceLifecycleKind::Disposal
            && $record->status === DeviceLifecycleStatus::Completed
        ) {
            $updates['status'] = DeviceStatus::Retired;
            $updates['is_blocked'] = true;
            $updates['operating_mode'] = 'retired';
        }

        if ($updates !== []) {
            $device->forceFill($updates)->save();
        }
    }
}
