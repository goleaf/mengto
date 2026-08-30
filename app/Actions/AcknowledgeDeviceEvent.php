<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcknowledgeDeviceEvent
{
    public function __construct(private readonly ForumActor $actor) {}

    public function handle(SmartDevice $device, DeviceEvent $event): DeviceEvent
    {
        return DB::transaction(function () use ($device, $event): DeviceEvent {
            $locked = DeviceEvent::query()
                ->select([
                    'id', 'smart_device_id', 'status', 'acknowledged_at',
                    'acknowledged_by_key',
                ])
                ->lockForUpdate()
                ->findOrFail($event->id);

            if ($locked->smart_device_id !== $device->id) {
                throw ValidationException::withMessages([
                    'event' => __('messages.this_event_does_not_belong_to_the_selected_device'),
                ]);
            }

            if ($locked->acknowledged_at === null) {
                $locked->forceFill([
                    'status' => 'acknowledged',
                    'acknowledged_at' => now(),
                    'acknowledged_by_key' => $this->actor->key(),
                ])->save();

                AuditLog::query()->create([
                    'actor_key' => $this->actor->key(),
                    'actor_role' => 'device-owner',
                    'action' => 'device-event.acknowledged',
                    'target_type' => DeviceEvent::class,
                    'target_id' => (string) $locked->id,
                    'metadata' => ['smart_device_id' => $device->id],
                ]);
            }

            return $locked;
        });
    }
}
