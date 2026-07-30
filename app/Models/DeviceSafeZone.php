<?php

namespace App\Models;

use Database\Factories\DeviceSafeZoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
