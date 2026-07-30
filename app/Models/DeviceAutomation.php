<?php

namespace App\Models;

use App\Enums\DeviceAutomationStatus;
use Database\Factories\DeviceAutomationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceAutomation extends Model
{
    /** @use HasFactory<DeviceAutomationFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'owner_key', 'name', 'trigger_type',
        'trigger_config', 'condition_config', 'action_type', 'action_config',
        'status', 'priority', 'safety_level', 'max_runs_per_hour',
        'cooldown_seconds', 'last_run_at', 'version',
    ];

    protected $hidden = [
        'trigger_config',
        'condition_config',
        'action_config',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'encrypted:array',
            'condition_config' => 'encrypted:array',
            'action_config' => 'encrypted:array',
            'status' => DeviceAutomationStatus::class,
            'last_run_at' => 'datetime',
        ];
    }

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(DeviceAutomationRun::class);
    }
}
