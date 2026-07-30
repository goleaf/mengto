<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicationDoseStatus;
use Database\Factories\MedicationDoseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $administered_at
 * @property string $administered_by_key
 * @property string $administered_by_name
 * @property Carbon|null $created_at
 * @property string|null $dose_given
 * @property int $id
 * @property string $idempotency_key
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property-read Medication|null $medication
 * @property int $medication_id
 * @property string|null $notes
 * @property Carbon $scheduled_for
 * @property MedicationDoseStatus $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
class MedicationDose extends Model
{
    /** @use HasFactory<MedicationDoseFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'medication_id', 'idempotency_key',
        'scheduled_for', 'administered_at', 'timezone', 'status', 'dose_given',
        'administered_by_key', 'administered_by_name', 'notes',
    ];

    protected $hidden = ['notes'];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'administered_at' => 'datetime',
            'status' => MedicationDoseStatus::class,
            'notes' => 'encrypted',
        ];
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /** @return BelongsTo<\App\Models\Medication, $this>*/
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
