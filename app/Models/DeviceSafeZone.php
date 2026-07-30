<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeviceSafeZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property numeric-string $accuracy_threshold_meters
 * @property Carbon|null $created_at
 * @property array<array-key, mixed> $exact_geometry
 * @property int $exit_delay_seconds
 * @property int $id
 * @property bool $is_home
 * @property string $name
 * @property string $public_area_label
 * @property array<array-key, mixed>|null $schedule
 * @property string $shape
 * @property-read SmartDevice|null $smartDevice
 * @property int $smart_device_id
 * @property string $status
 * @property Carbon|null $updated_at
 */
class DeviceSafeZone extends Model
{
    /** @use HasFactory<DeviceSafeZoneFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'name', 'shape', 'public_area_label',
        'exact_geometry', 'schedule', 'exit_delay_seconds',
        'accuracy_threshold_meters', 'status', 'is_home',
    ];

    protected $hidden = ['exact_geometry', 'schedule'];

    protected function casts(): array
    {
        return [
            'exact_geometry' => 'encrypted:array',
            'schedule' => 'encrypted:array',
            'accuracy_threshold_meters' => 'decimal:2',
            'is_home' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
