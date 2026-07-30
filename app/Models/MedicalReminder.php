<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MedicalReminderStatus;
use Database\Factories\MedicalReminderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $confirmed_at
 * @property string|null $confirmed_by_key
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property Carbon $due_at
 * @property int $id
 * @property string|null $instructions
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property string $priority
 * @property array<array-key, mixed>|null $recipients
 * @property int|null $related_id
 * @property string|null $related_type
 * @property MedicalReminderStatus $status
 * @property string $timezone
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 */
class MedicalReminder extends Model
{
    /** @use HasFactory<MedicalReminderFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'type', 'title', 'due_at', 'timezone', 'priority',
        'status', 'recipients', 'instructions', 'related_type', 'related_id',
        'confirmed_at', 'confirmed_by_key', 'created_by_key',
    ];

    protected $hidden = ['recipients', 'instructions'];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'status' => MedicalReminderStatus::class,
            'recipients' => 'encrypted:array',
            'instructions' => 'encrypted',
            'confirmed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                MedicalReminderStatus::Scheduled->value,
                MedicalReminderStatus::Snoozed->value,
            ])
            ->where('due_at', '>=', now()->subDay());
    }
}
