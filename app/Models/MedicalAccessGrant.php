<?php

namespace App\Models;

use Database\Factories\MedicalAccessGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
