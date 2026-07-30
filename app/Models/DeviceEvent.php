<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceConfidence;
use App\Enums\DeviceEventSeverity;
use Database\Factories\DeviceEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $acknowledged_at
 * @property string|null $acknowledged_by_key
 * @property-read DevicePetAssignment|null $assignment
 * @property-read CareEntry|null $careEntry
 * @property int|null $care_entry_id
 * @property DeviceConfidence $confidence
 * @property Carbon|null $created_at
 * @property array<array-key, mixed>|null $details
 * @property int|null $device_pet_assignment_id
 * @property string|null $external_event_id
 * @property int $id
 * @property Carbon|null $first_occurred_at
 * @property Carbon|null $last_occurred_at
 * @property int $occurrence_count
 * @property Carbon $occurred_at
 * @property string|null $pet_name
 * @property string|null $pet_profile_key
 * @property bool $requires_attention
 * @property-read SearchCase|null $searchCase
 * @property int|null $search_case_id
 * @property DeviceEventSeverity $severity
 * @property-read SmartDevice|null $smartDevice
 * @property int $smart_device_id
 * @property string $source
 * @property string $status
 * @property string|null $summary
 * @property string $timezone
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 */
class DeviceEvent extends Model
{
    /** @use HasFactory<DeviceEventFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'device_pet_assignment_id', 'pet_profile_key',
        'pet_name', 'external_event_id', 'type', 'severity', 'status',
        'occurrence_count', 'first_occurred_at', 'last_occurred_at',
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
            'first_occurred_at' => 'datetime',
            'last_occurred_at' => 'datetime',
            'confidence' => DeviceConfidence::class,
            'requires_attention' => 'boolean',
            'acknowledged_at' => 'datetime',
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

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
