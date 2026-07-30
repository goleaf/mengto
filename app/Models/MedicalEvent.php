<?php

namespace App\Models;

use App\Enums\MedicalEventType;
use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\MedicalEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
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
