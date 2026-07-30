<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceConfidence;
use Database\Factories\DeviceReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property numeric-string|null $accuracy_meters
 * @property-read DevicePetAssignment|null $assignment
 * @property-read CareEntry|null $careEntry
 * @property int|null $care_entry_id
 * @property DeviceConfidence $confidence
 * @property Carbon|null $created_at
 * @property int|null $device_pet_assignment_id
 * @property string|null $external_event_id
 * @property int $id
 * @property bool $is_stale
 * @property-read MedicalEvent|null $medicalEvent
 * @property int|null $medical_event_id
 * @property string $metric_type
 * @property numeric-string|null $numeric_value
 * @property array<array-key, mixed>|null $original_payload
 * @property string|null $pet_name
 * @property string|null $pet_profile_key
 * @property array<array-key, mixed>|null $processed_payload
 * @property Carbon $recorded_at
 * @property-read SmartDevice|null $smartDevice
 * @property int $smart_device_id
 * @property string|null $text_value
 * @property string $timezone
 * @property string|null $unit
 * @property Carbon|null $updated_at
 * @property string $verification_status
 * @property-read WeightEntry|null $weightEntry
 * @property int|null $weight_entry_id
 */
class DeviceReading extends Model
{
    /** @use HasFactory<DeviceReadingFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'device_pet_assignment_id', 'pet_profile_key',
        'pet_name', 'external_event_id', 'metric_type', 'numeric_value',
        'text_value', 'unit', 'recorded_at', 'timezone', 'accuracy_meters',
        'confidence', 'verification_status', 'original_payload',
        'processed_payload', 'is_stale', 'care_entry_id', 'medical_event_id',
        'weight_entry_id',
    ];

    protected $hidden = ['original_payload', 'processed_payload'];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:6',
            'recorded_at' => 'datetime',
            'accuracy_meters' => 'decimal:2',
            'confidence' => DeviceConfidence::class,
            'original_payload' => 'encrypted:array',
            'processed_payload' => 'encrypted:array',
            'is_stale' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    /** @return BelongsTo<\App\Models\DevicePetAssignment, $this>*/
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DevicePetAssignment::class, 'device_pet_assignment_id');
    }

    /** @return BelongsTo<\App\Models\CareEntry, $this>*/
    public function careEntry(): BelongsTo
    {
        return $this->belongsTo(CareEntry::class);
    }

    /** @return BelongsTo<\App\Models\MedicalEvent, $this>*/
    public function medicalEvent(): BelongsTo
    {
        return $this->belongsTo(MedicalEvent::class);
    }

    /** @return BelongsTo<\App\Models\WeightEntry, $this>*/
    public function weightEntry(): BelongsTo
    {
        return $this->belongsTo(WeightEntry::class);
    }
}
