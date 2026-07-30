<?php

namespace App\Models;

use App\Enums\MedicationDoseStatus;
use Database\Factories\MedicationDoseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
