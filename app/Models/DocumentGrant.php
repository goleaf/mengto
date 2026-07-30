<?php

namespace App\Models;

use Database\Factories\DocumentGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentGrant extends Model
{
    /** @use HasFactory<DocumentGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id', 'expert_profile_id', 'owner_key', 'label', 'document_type',
        'file_path', 'permissions', 'expires_at', 'last_opened_at',
        'downloaded_at', 'revoked_at',
    ];

    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}
