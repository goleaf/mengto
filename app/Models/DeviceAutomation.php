<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceAutomationStatus;
use Database\Factories\DeviceAutomationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property array<array-key, mixed>|null $action_config
 * @property string $action_type
 * @property array<array-key, mixed>|null $condition_config
 * @property int $cooldown_seconds
 * @property Carbon|null $created_at
 * @property int $id
 * @property Carbon|null $last_run_at
 * @property int $max_runs_per_hour
 * @property string $name
 * @property string $owner_key
 * @property string $priority
 * @property-read Collection<int, DeviceAutomationRun> $runs
 * @property string $safety_level
 * @property-read SmartDevice|null $smartDevice
 * @property int|null $smart_device_id
 * @property DeviceAutomationStatus $status
 * @property array<array-key, mixed>|null $trigger_config
 * @property string $trigger_type
 * @property Carbon|null $updated_at
 * @property int $version
 */
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

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    /** @return HasMany<\App\Models\DeviceAutomationRun, $this>*/
    public function runs(): HasMany
    {
        return $this->hasMany(DeviceAutomationRun::class);
    }
}
