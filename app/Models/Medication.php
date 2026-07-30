<?php

namespace App\Models;

use App\Enums\MedicalVerificationStatus;
use App\Enums\MedicationStatus;
use Database\Factories\MedicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

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
