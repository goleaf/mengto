<?php

namespace App\Models;

use App\Enums\DeviceConfidence;
use App\Enums\DeviceEventSeverity;
use Database\Factories\DeviceEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    /** @use HasFactory<DeviceEventFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'device_pet_assignment_id', 'pet_profile_key',
        'pet_name', 'external_event_id', 'type', 'severity', 'status',
        'title', 'summary', 'details', 'occurred_at', 'timezone',
        'confidence', 'source', 'requires_attention', 'acknowledged_at',
        'acknowledged_by_key', 'care_entry_id', 'search_case_id',
    ];

    protected $hidden = ['summary', 'details'];

    protected function casts(): array
    {
        return [
            'severity' => DeviceEventSeverity::class,
            'summary' => 'encrypted',
            'details' => 'encrypted:array',
            'occurred_at' => 'datetime',
            'confidence' => DeviceConfidence::class,
            'requires_attention' => 'boolean',
            'acknowledged_at' => 'datetime',
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

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
