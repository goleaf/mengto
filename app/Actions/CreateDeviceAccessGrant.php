<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DeviceAccessGrant;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateDeviceAccessGrant
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{grant: DeviceAccessGrant, token: string}
     */
    public function handle(SmartDevice $device, array $data): array
    {
        return DB::transaction(function () use ($device, $data): array {
            $permissions = array_values(array_unique($data['permissions']));
            $allowCommands = (bool) ($data['allow_commands'] ?? false);

            if ($allowCommands && ! in_array('control', $permissions, true)) {
                throw ValidationException::withMessages([
                    'permissions' => __('messages.command_access_requires_the_control_permission_e2e74ae6f1'),
                ]);
            }

            if (($data['allow_audio'] ?? false) && ! ($data['allow_camera'] ?? false)) {
                throw ValidationException::withMessages([
                    'allow_audio' => __('messages.audio_cannot_be_shared_without_camera_access_2a2b75e880'),
                ]);
            }

            $token = Str::random(64);
            $grant = $device->accessGrants()->create([
                'granted_by_key' => $this->actor->key(),
                'recipient_name' => $data['recipient_name'],
                'recipient_role' => $data['recipient_role'],
                'label' => $data['label'],
                'token_hash' => hash('sha256', $token),
                'permissions' => $permissions,
                'allow_location' => (bool) ($data['allow_location'] ?? false),
                'allow_camera' => (bool) ($data['allow_camera'] ?? false),
                'allow_commands' => $allowCommands,
                'allow_audio' => (bool) ($data['allow_audio'] ?? false),
                'max_views' => $data['max_views'],
                'views_used' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addHours((int) $data['expires_in_hours']),
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-access.created',
                'target_type' => DeviceAccessGrant::class,
                'target_id' => (string) $grant->id,
                'metadata' => [
                    'smart_device_id' => $device->id,
                    'recipient_role' => $grant->recipient_role,
                    'permissions' => $grant->permissions,
                    'allow_location' => $grant->allow_location,
                    'allow_camera' => $grant->allow_camera,
                    'allow_commands' => $grant->allow_commands,
                    'expires_at' => $grant->expires_at?->toAtomString(),
                ],
            ]);

            return ['grant' => $grant, 'token' => $token];
        });
    }
}
