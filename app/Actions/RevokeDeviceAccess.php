<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DeviceAccessGrant;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevokeDeviceAccess
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(
        SmartDevice $device,
        DeviceAccessGrant $grant,
    ): DeviceAccessGrant {
        if ($grant->smart_device_id !== $device->id) {
            throw ValidationException::withMessages([
                'access' => 'This access grant does not belong to the selected device.',
            ]);
        }

        return DB::transaction(function () use ($device, $grant): DeviceAccessGrant {
            if ($grant->revoked_at === null) {
                $grant->forceFill(['revoked_at' => now()])->save();

                AuditLog::query()->create([
                    'actor_key' => $this->actor->key(),
                    'actor_role' => 'device-owner',
                    'action' => 'device-access.revoked',
                    'target_type' => DeviceAccessGrant::class,
                    'target_id' => (string) $grant->id,
                    'metadata' => ['smart_device_id' => $device->id],
                ]);
            }

            return $grant;
        });
    }
}
