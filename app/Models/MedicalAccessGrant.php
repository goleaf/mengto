<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MedicalAccessGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property bool $allow_download
 * @property bool $allow_edit
 * @property Carbon|null $created_at
 * @property Carbon $expires_at
 * @property string $granted_by_key
 * @property int $id
 * @property string $label
 * @property Carbon|null $last_opened_at
 * @property int $max_views
 * @property-read MedicalRecord|null $medicalRecord
 * @property int $medical_record_id
 * @property array<array-key, mixed> $permissions
 * @property string|null $recipient_key
 * @property string $recipient_name
 * @property string $recipient_role
 * @property Carbon|null $revoked_at
 * @property array<array-key, mixed> $sections
 * @property string $token_hash
 * @property Carbon|null $updated_at
 * @property int $views_used
 */
class MedicalAccessGrant extends Model
{
    /** @use HasFactory<MedicalAccessGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'medical_record_id', 'granted_by_key', 'recipient_key',
        'recipient_name', 'recipient_role', 'label', 'token_hash', 'sections',
        'permissions', 'allow_download', 'allow_edit', 'max_views',
        'views_used', 'expires_at', 'last_opened_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'permissions' => 'array',
            'allow_download' => 'boolean',
            'allow_edit' => 'boolean',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select([
                'id', 'medical_record_id', 'granted_by_key', 'recipient_key',
                'recipient_name', 'recipient_role', 'label', 'token_hash',
                'sections', 'permissions', 'allow_download', 'allow_edit',
                'max_views', 'views_used', 'expires_at', 'last_opened_at',
                'revoked_at', 'created_at', 'updated_at',
            ]);
    }

    /** @return BelongsTo<\App\Models\MedicalRecord, $this>*/
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereColumn('views_used', '<', 'max_views');
    }

    public function canViewSection(string $section): bool
    {
        return in_array($section, $this->sections ?? [], true);
    }

    public function canBeOpened(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at?->isFuture()
            && $this->views_used < $this->max_views;
    }
}
