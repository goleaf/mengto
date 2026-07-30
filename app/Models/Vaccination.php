<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalVerificationStatus;
use App\Enums\VaccinationStatus;
use Database\Factories\VaccinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $administered_on
 * @property string|null $clinic_name
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property-read Collection<int, MedicalDocument> $documents
 * @property string|null $dose
 * @property int $id
 * @property string|null $lot_number
 * @property string|null $manufacturer
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property string $name
 * @property Carbon|null $next_due_on
 * @property Carbon|null $product_expires_on
 * @property string|null $reaction
 * @property string|null $route
 * @property VaccinationStatus $status
 * @property Carbon|null $updated_at
 * @property MedicalVerificationStatus $verification_status
 * @property string|null $veterinarian_name
 */
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

    public function scopeForOverview(Builder $query): Builder
    {
        return $query->select([
            'id', 'medical_record_id', 'name', 'manufacturer', 'lot_number',
            'administered_on', 'next_due_on', 'status', 'clinic_name',
            'veterinarian_name', 'reaction', 'verification_status', 'created_at',
        ]);
    }
}
