<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalVerificationStatus;
use App\Enums\MedicationStatus;
use Database\Factories\MedicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string|null $active_ingredient
 * @property string|null $clinic_name
 * @property string|null $concentration
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $dose
 * @property-read Collection<int, MedicationDose> $doses
 * @property Carbon|null $ends_on
 * @property Carbon|null $expires_on
 * @property string $form
 * @property int $id
 * @property string|null $instructions
 * @property bool $is_high_risk
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property string $name
 * @property Carbon|null $next_dose_at
 * @property string|null $prescribed_by_name
 * @property string|null $reason
 * @property numeric-string|null $remaining_quantity
 * @property string|null $remaining_unit
 * @property string $route
 * @property string $schedule_text
 * @property string $schedule_type
 * @property Carbon $starts_on
 * @property MedicationStatus $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 * @property MedicalVerificationStatus $verification_status
 */
class Medication extends Model
{
    /** @use HasFactory<MedicationFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'name', 'active_ingredient', 'form',
        'concentration', 'dose', 'route', 'schedule_type', 'schedule_text',
        'starts_on', 'ends_on', 'next_dose_at', 'timezone', 'status', 'reason',
        'prescribed_by_name', 'clinic_name', 'instructions', 'is_high_risk',
        'remaining_quantity', 'remaining_unit', 'expires_on',
        'verification_status', 'created_by_key',
    ];

    protected $hidden = ['instructions'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'next_dose_at' => 'datetime',
            'status' => MedicationStatus::class,
            'instructions' => 'encrypted',
            'is_high_risk' => 'boolean',
            'remaining_quantity' => 'decimal:2',
            'expires_on' => 'date',
            'verification_status' => MedicalVerificationStatus::class,
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select([
                'id', 'medical_record_id', 'name', 'active_ingredient', 'form',
                'concentration', 'dose', 'route', 'schedule_type',
                'schedule_text', 'starts_on', 'ends_on', 'next_dose_at',
                'timezone', 'status', 'reason', 'prescribed_by_name',
                'clinic_name', 'instructions', 'is_high_risk',
                'remaining_quantity', 'remaining_unit', 'expires_on',
                'verification_status', 'created_by_key', 'created_at',
                'updated_at',
            ]);
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /** @return HasMany<\App\Models\MedicationDose, $this>*/
    public function doses(): HasMany
    {
        return $this->hasMany(MedicationDose::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MedicationStatus::Active->value);
    }

    public function scopeForSchedule(Builder $query): Builder
    {
        return $query->select([
            'id', 'medical_record_id', 'name', 'active_ingredient', 'form',
            'concentration', 'dose', 'route', 'schedule_type', 'schedule_text',
            'starts_on', 'ends_on', 'next_dose_at', 'timezone', 'status',
            'reason', 'prescribed_by_name', 'clinic_name', 'instructions',
            'is_high_risk', 'remaining_quantity', 'remaining_unit',
            'expires_on', 'verification_status', 'created_at',
        ]);
    }
}
