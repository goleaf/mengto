<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;

final class UpdateDeviceRetention
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly EnforceDeviceRetention $enforceRetention,
    ) {}

    /** @param array<string, int> $data */
    public function handle(SmartDevice $device, array $data): SmartDevice
    {
        return DB::transaction(function () use ($device, $data): SmartDevice {
            $lockedDevice = SmartDevice::query()
                ->select([
                    'id',
                    'location_retention_days',
                    'media_retention_days',
                    'telemetry_retention_days',
                    'lock_version',
                ])
                ->lockForUpdate()
                ->findOrFail($device->id);
            $before = [
                'location_retention_days' => $lockedDevice->location_retention_days,
                'media_retention_days' => $lockedDevice->media_retention_days,
                'telemetry_retention_days' => $lockedDevice->telemetry_retention_days,
            ];

            $lockedDevice->forceFill($data)->save();
            $lockedDevice->increment('lock_version');
            $pruned = $this->enforceRetention->handle($lockedDevice);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-retention.updated',
                'target_type' => SmartDevice::class,
                'target_id' => (string) $lockedDevice->id,
                'metadata' => [
                    'before' => $before,
                    'after' => $data,
                    'pruned' => $pruned,
                ],
            ]);

            return $lockedDevice->refresh();
        }, 3);
    }
}
