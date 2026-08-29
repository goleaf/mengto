<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalEventType;
use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\MedicalEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $confirmed_at
 * @property string|null $confirmed_by_name
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $created_by_name
 * @property array<array-key, mixed>|null $details
 * @property-read Collection<int, MedicalDocument> $documents
 * @property Carbon|null $follow_up_at
 * @property int $id
 * @property bool $is_critical
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property Carbon $occurred_at
 * @property string $source_name
 * @property string|null $source_reference
 * @property MedicalSourceType $source_type
 * @property string $status
 * @property string|null $summary
 * @property string $timezone
 * @property string $title
 * @property MedicalEventType $type
 * @property Carbon|null $updated_at
 * @property MedicalVerificationStatus $verification_status
 */
class MedicalEvent extends Model
{
    /** @use HasFactory<MedicalEventFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'type', 'title', 'occurred_at', 'timezone',
        'status', 'source_type', 'source_name', 'source_reference',
        'verification_status', 'summary', 'details', 'created_by_key',
        'created_by_name', 'confirmed_by_name', 'confirmed_at',
        'follow_up_at', 'is_critical',
    ];

    protected $hidden = ['summary', 'details'];

    protected function casts(): array
    {
        return [
            'type' => MedicalEventType::class,
            'occurred_at' => 'datetime',
            'source_type' => MedicalSourceType::class,
            'verification_status' => MedicalVerificationStatus::class,
            'summary' => 'encrypted',
            'details' => 'encrypted:array',
            'confirmed_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'is_critical' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /** @return HasMany<\App\Models\MedicalDocument, $this>*/
    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    /** @return HasMany<DeviceReading, $this> */
    public function deviceReadings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function scopeForTimeline(Builder $query): Builder
    {
        return $query->select([
            'id', 'medical_record_id', 'type', 'title', 'occurred_at',
            'status', 'source_type', 'source_name', 'verification_status',
            'summary', 'details', 'created_by_name', 'confirmed_by_name',
            'confirmed_at', 'follow_up_at', 'is_critical', 'created_at',
        ]);
    }
}
