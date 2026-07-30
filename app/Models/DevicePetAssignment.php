<?php

namespace App\Models;

use App\Enums\DeviceConfidence;
use Database\Factories\DevicePetAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
