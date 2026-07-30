<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceConfidence;
use Database\Factories\DevicePetAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property DeviceConfidence $confidence
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $identification_method
 * @property bool $is_primary
 * @property string $pet_name
 * @property string $pet_profile_key
 * @property string $relationship_type
 * @property-read SmartDevice|null $smartDevice
 * @property int $smart_device_id
 * @property Carbon|null $updated_at
 */
class DevicePetAssignment extends Model
{
    /** @use HasFactory<DevicePetAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'pet_profile_key', 'pet_name', 'relationship_type',
        'identification_method', 'confidence', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => DeviceConfidence::class,
            'is_primary' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
