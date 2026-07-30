<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeviceAutomationRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property array<array-key, mixed> $action_snapshot
 * @property-read DeviceAutomation|null $automation
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property int $device_automation_id
 * @property int|null $device_event_id
 * @property string|null $error
 * @property-read DeviceEvent|null $event
 * @property int $id
 * @property string $idempotency_key
 * @property bool $is_simulation
 * @property array<array-key, mixed>|null $result
 * @property-read SmartDevice|null $smartDevice
 * @property int|null $smart_device_id
 * @property Carbon $started_at
 * @property string $status
 * @property array<array-key, mixed> $trigger_snapshot
 * @property Carbon|null $updated_at
 */
class DeviceAutomationRun extends Model
{
    /** @use HasFactory<DeviceAutomationRunFactory> */
    use HasFactory;

    protected $fillable = [
        'device_automation_id', 'smart_device_id', 'device_event_id',
        'idempotency_key', 'trigger_snapshot', 'action_snapshot', 'status',
        'is_simulation', 'started_at', 'completed_at', 'result', 'error',
    ];

    protected $hidden = [
        'trigger_snapshot',
        'action_snapshot',
        'result',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'trigger_snapshot' => 'encrypted:array',
            'action_snapshot' => 'encrypted:array',
            'is_simulation' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'result' => 'encrypted:array',
            'error' => 'encrypted',
        ];
    }

    /** @return BelongsTo<\App\Models\DeviceAutomation, $this>*/
    public function automation(): BelongsTo
    {
        return $this->belongsTo(DeviceAutomation::class, 'device_automation_id');
    }

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    /** @return BelongsTo<\App\Models\DeviceEvent, $this>*/
    public function event(): BelongsTo
    {
        return $this->belongsTo(DeviceEvent::class, 'device_event_id');
    }
}
