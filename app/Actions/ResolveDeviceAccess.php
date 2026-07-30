<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DeviceAccessGrant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ResolveDeviceAccess
{
    public function handle(string $token): DeviceAccessGrant
    {
        return DB::transaction(function () use ($token): DeviceAccessGrant {
            $grant = DeviceAccessGrant::query()
                ->select([
                    'id', 'smart_device_id', 'granted_by_key', 'recipient_key',
                    'recipient_name', 'recipient_role', 'label', 'token_hash',
                    'permissions', 'allow_location', 'allow_camera',
                    'allow_commands', 'allow_audio', 'max_views', 'views_used',
                    'starts_at', 'expires_at', 'last_opened_at', 'revoked_at',
                    'created_at', 'updated_at',
                ])
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($grant === null || ! $grant->canBeOpened()) {
                throw (new ModelNotFoundException)->setModel(DeviceAccessGrant::class);
            }

            $grant->forceFill([
                'views_used' => $grant->views_used + 1,
                'last_opened_at' => now(),
            ])->save();

            AuditLog::query()->create([
                'actor_key' => $grant->recipient_key ?? 'temporary-device-link',
                'actor_role' => $grant->recipient_role,
                'action' => 'device-access.opened',
                'target_type' => DeviceAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'smart_device_id' => $grant->smart_device_id,
                    'permissions' => $grant->permissions,
                    'views_used' => $grant->views_used,
                    'max_views' => $grant->max_views,
                ],
            ]);

            return $grant->load([
                'smartDevice' => fn ($devices) => $devices->select([
                    'id', 'owner_key', 'slug', 'name', 'type', 'brand', 'model',
                    'image_url', 'public_zone_label', 'privacy', 'status',
                    'connection_status', 'operating_mode', 'battery_percent',
                    'last_seen_at', 'last_synced_at', 'last_location_at',
                    'location_accuracy_meters', 'is_blocked', 'updated_at',
                ]),
            ]);
        });
    }
}
