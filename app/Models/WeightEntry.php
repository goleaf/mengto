<?php

namespace App\Models;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\WeightEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
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
