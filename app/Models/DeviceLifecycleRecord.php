<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceLifecycleKind;
use App\Enums\DeviceLifecycleStatus;
use Database\Factories\DeviceLifecycleRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property array<array-key, mixed>|null $details
 * @property Carbon $effective_at
 * @property int $id
 * @property DeviceLifecycleKind $kind
 * @property Carbon|null $resolved_at
 * @property string $severity
 * @property-read SmartDevice $smartDevice
 * @property int $smart_device_id
 * @property DeviceLifecycleStatus $status
 * @property Carbon|null $updated_at
 * @property string|null $version_from
 * @property string|null $version_to
 */
final class DeviceLifecycleRecord extends Model
{
    /** @use HasFactory<DeviceLifecycleRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id',
        'kind',
        'status',
        'created_by_key',
        'version_from',
        'version_to',
        'severity',
        'details',
        'effective_at',
        'resolved_at',
    ];

    protected $hidden = ['details'];

    protected function casts(): array
    {
        return [
            'kind' => DeviceLifecycleKind::class,
            'status' => DeviceLifecycleStatus::class,
            'details' => 'encrypted:array',
            'effective_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<SmartDevice, $this> */
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
