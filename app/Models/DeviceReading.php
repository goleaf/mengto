<?php

namespace App\Models;

use App\Enums\DeviceConfidence;
use Database\Factories\DeviceReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DevicePetAssignment::class, 'device_pet_assignment_id');
    }

    public function careEntry(): BelongsTo
    {
        return $this->belongsTo(CareEntry::class);
    }

    public function medicalEvent(): BelongsTo
    {
        return $this->belongsTo(MedicalEvent::class);
    }

    public function weightEntry(): BelongsTo
    {
        return $this->belongsTo(WeightEntry::class);
    }
}
