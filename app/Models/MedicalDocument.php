<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use Database\Factories\MedicalDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $download_count
 * @property Carbon|null $expires_on
 * @property string $file_path
 * @property int $id
 * @property-read MedicalEvent|null $medicalEvent
 * @property-read MedicalRecord|null $medicalRecord
 * @property int|null $medical_event_id
 * @property int $medical_record_id
 * @property string $mime_type
 * @property string $original_name
 * @property int $size_bytes
 * @property string $source_name
 * @property MedicalSourceType $source_type
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property string $uploaded_by_key
 * @property-read Vaccination|null $vaccination
 * @property int|null $vaccination_id
 * @property MedicalVerificationStatus $verification_status
 */
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

    protected $hidden = ['file_path', 'original_name'];

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

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /** @return BelongsTo<\App\Models\MedicalEvent, $this>*/
    public function medicalEvent(): BelongsTo
    {
        return $this->belongsTo(MedicalEvent::class);
    }

    /** @return BelongsTo<\App\Models\Vaccination, $this>*/
    public function vaccination(): BelongsTo
    {
        return $this->belongsTo(Vaccination::class);
    }
}
