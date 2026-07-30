<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'slug', 'title', 'summary', 'body', 'type',
        'category', 'tags', 'sources', 'conflict_disclosure', 'language',
        'status', 'last_reviewed_at', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'tags' => 'array',
            'sources' => 'array',
            'last_reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published->value);
    }
}
