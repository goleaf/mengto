<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceWarningCategory;
use App\Enums\PlaceWarningResolution;
use App\Enums\PlaceWarningSeverity;
use App\Enums\PlaceWarningSource;
use App\Enums\PlaceWarningStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceWarningFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $author_user_id
 * @property PlaceWarningCategory $category
 * @property CarbonImmutable|null $expires_at
 * @property int $id
 * @property int $lock_version
 * @property int|null $moderator_user_id
 * @property int $place_id
 * @property CarbonImmutable|null $published_at
 * @property PlaceWarningResolution|null $resolution
 * @property PlaceWarningSeverity $severity
 * @property PlaceWarningSource $source
 * @property string $stable_key
 * @property PlaceWarningStatus $status
 * @property-read User $author
 * @property-read Place $place
 */
final class PlaceWarning extends Model
{
    /** @use HasFactory<PlaceWarningFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'author_user_id',
        'moderator_user_id',
        'stable_key',
        'idempotency_key',
        'category',
        'severity',
        'affected_scope',
        'source',
        'title',
        'detail',
        'evidence',
        'status',
        'published_at',
        'expires_at',
        'disputed_at',
        'resolved_at',
        'resolution',
        'moderation_reason',
        'lock_version',
    ];

    protected $hidden = ['idempotency_key'];

    protected $attributes = ['status' => 'needs_review', 'lock_version' => 0];

    protected function casts(): array
    {
        return [
            'category' => PlaceWarningCategory::class,
            'severity' => PlaceWarningSeverity::class,
            'source' => PlaceWarningSource::class,
            'status' => PlaceWarningStatus::class,
            'resolution' => PlaceWarningResolution::class,
            'published_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'disputed_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    /** @return HasMany<PlaceWarningConfirmation, $this> */
    public function confirmations(): HasMany
    {
        return $this->hasMany(PlaceWarningConfirmation::class);
    }

    /** @return HasMany<PlaceWarningDispute, $this> */
    public function disputes(): HasMany
    {
        return $this->hasMany(PlaceWarningDispute::class);
    }

    /** @return HasMany<PlaceWarningAppeal, $this> */
    public function appeals(): HasMany
    {
        return $this->hasMany(PlaceWarningAppeal::class);
    }

    /** @return HasMany<PlaceWarningEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceWarningEvent::class);
    }

    /** @param Builder<PlaceWarning> $query @return Builder<PlaceWarning> */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [PlaceWarningStatus::Published->value, PlaceWarningStatus::Disputed->value])
            ->where('expires_at', '>', now());
    }
}
