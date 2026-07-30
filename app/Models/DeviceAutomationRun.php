<?php

namespace App\Models;

use Database\Factories\DeviceAutomationRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function automation(): BelongsTo
    {
        return $this->belongsTo(DeviceAutomation::class, 'device_automation_id');
    }

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(DeviceEvent::class, 'device_event_id');
    }
}
