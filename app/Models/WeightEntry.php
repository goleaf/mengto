<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\WeightEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property int $id
 * @property Carbon $measured_at
 * @property string|null $measurement_context
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property string|null $notes
 * @property string $source_name
 * @property MedicalSourceType $source_type
 * @property int|null $tare_grams
 * @property string $timezone
 * @property Carbon|null $updated_at
 * @property MedicalVerificationStatus $verification_status
 * @property int $weight_grams
 */
class WeightEntry extends Model
{
    /** @use HasFactory<WeightEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'measured_at', 'timezone', 'weight_grams',
        'tare_grams', 'source_type', 'source_name', 'measurement_context',
        'notes', 'verification_status', 'created_by_key',
    ];

    protected $hidden = ['notes'];

    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime',
            'source_type' => MedicalSourceType::class,
            'notes' => 'encrypted',
            'verification_status' => MedicalVerificationStatus::class,
        ];
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /** @return HasMany<DeviceReading, $this> */
    public function deviceReadings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function scopeForChart(Builder $query): Builder
    {
        return $query->select([
            'id', 'medical_record_id', 'measured_at', 'weight_grams',
            'source_type', 'source_name', 'measurement_context', 'notes',
            'verification_status',
        ]);
    }
}
