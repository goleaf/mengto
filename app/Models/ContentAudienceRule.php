<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentAudienceType;
use Database\Factories\ContentAudienceRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property ContentAudienceType $audience_type
 * @property int $content_publication_id
 * @property int|null $context_actor_id
 * @property string|null $context_key
 * @property string|null $context_type
 * @property Carbon|null $expires_at
 * @property int $id
 * @property int $lock_version
 * @property-read Collection<int, ContentAudienceActor> $actors
 * @property-read SocialActor|null $contextActor
 * @property-read ContentPublication $publication
 */
final class ContentAudienceRule extends Model
{
    /** @use HasFactory<ContentAudienceRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'content_publication_id',
        'audience_type',
        'context_actor_id',
        'context_type',
        'context_key',
        'expires_at',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'audience_type' => ContentAudienceType::class,
            'expires_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<ContentPublication, $this> */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(ContentPublication::class, 'content_publication_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function contextActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'context_actor_id');
    }

    /** @return HasMany<ContentAudienceActor, $this> */
    public function actors(): HasMany
    {
        return $this->hasMany(ContentAudienceActor::class);
    }

    /** @param Builder<ContentAudienceRule> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $expiry): void {
            $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
