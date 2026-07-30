<?php

namespace App\Models;

use App\Enums\MedicalVerificationStatus;
use App\Enums\VaccinationStatus;
use Database\Factories\VaccinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccination extends Model
{
    /** @use HasFactory<VaccinationFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'name', 'manufacturer', 'lot_number',
        'product_expires_on', 'administered_on', 'next_due_on', 'status',
        'dose', 'route', 'clinic_name', 'veterinarian_name', 'reaction',
        'verification_status', 'created_by_key',
    ];

    protected $hidden = ['reaction'];

    protected function casts(): array
    {
        return [
            'product_expires_on' => 'date',
            'administered_on' => 'date',
            'next_due_on' => 'date',
            'status' => VaccinationStatus::class,
            'reaction' => 'encrypted',
            'verification_status' => MedicalVerificationStatus::class,
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    public function scopeForOverview(Builder $query): Builder
    {
        return $query->select([
            'id', 'medical_record_id', 'name', 'manufacturer', 'lot_number',
            'administered_on', 'next_due_on', 'status', 'clinic_name',
            'veterinarian_name', 'reaction', 'verification_status', 'created_at',
        ]);
    }
}
