<?php

namespace App\Models;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\MedicalDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalDocument extends Model
{
    /** @use HasFactory<MedicalDocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'medical_event_id', 'vaccination_id', 'type',
        'title', 'file_path', 'original_name', 'mime_type', 'size_bytes',
        'source_type', 'source_name', 'verification_status', 'expires_on',
        'uploaded_by_key', 'download_count',
    ];

    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return [
            'source_type' => MedicalSourceType::class,
            'verification_status' => MedicalVerificationStatus::class,
            'expires_on' => 'date',
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select([
                'id', 'medical_record_id', 'medical_event_id',
                'vaccination_id', 'type', 'title', 'file_path',
                'original_name', 'mime_type', 'size_bytes', 'source_type',
                'source_name', 'verification_status', 'expires_on',
                'uploaded_by_key', 'download_count', 'created_at', 'updated_at',
            ]);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function medicalEvent(): BelongsTo
    {
        return $this->belongsTo(MedicalEvent::class);
    }

    public function vaccination(): BelongsTo
    {
        return $this->belongsTo(Vaccination::class);
    }
}
