<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceMediaStatus;
use Database\Factories\PlaceMediaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlaceMedia extends Model
{
    /** @use HasFactory<PlaceMediaFactory> */
    use HasFactory;

    protected $table = 'place_media';

    protected $fillable = [
        'media_key', 'place_id', 'content_media_asset_id', 'attached_by_user_id',
        'moderated_by_user_id', 'status', 'position', 'is_featured', 'featured_key',
        'caption', 'attribution', 'licence', 'upload_key', 'moderation_reason_code',
        'moderated_at', 'archived_at', 'removed_at', 'recoverable_until',
        'retained_until', 'legal_hold_at', 'lock_version',
    ];

    protected $hidden = [
        'upload_key', 'featured_key', 'moderation_reason_code', 'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlaceMediaStatus::class,
            'position' => 'integer',
            'is_featured' => 'boolean',
            'moderated_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'removed_at' => 'immutable_datetime',
            'recoverable_until' => 'immutable_datetime',
            'retained_until' => 'immutable_datetime',
            'legal_hold_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'media_key';
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<ContentMediaAsset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(ContentMediaAsset::class, 'content_media_asset_id');
    }

    /** @return BelongsTo<User, $this> */
    public function attachedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_user_id');
    }

    /** @return HasMany<PlaceMediaVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(PlaceMediaVariant::class);
    }

    /** @return HasMany<PlaceMediaEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceMediaEvent::class);
    }

    /** @param Builder<PlaceMedia> $query @return Builder<PlaceMedia> */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', PlaceMediaStatus::Active->value)
            ->whereNull('removed_at')
            ->whereNull('archived_at');
    }
}
