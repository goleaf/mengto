<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $body
 * @property string $category
 * @property string|null $conflict_disclosure
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property int $id
 * @property string $language
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $published_at
 * @property string $slug
 * @property array<array-key, mixed>|null $sources
 * @property PublicationStatus $status
 * @property string $summary
 * @property array<array-key, mixed>|null $tags
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 */
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

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published->value);
    }
}
