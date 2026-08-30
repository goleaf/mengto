<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceReviewAnonymityMode;
use App\Enums\PlaceReviewEligibilityContext;
use App\Enums\PlaceReviewModerationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property PlaceReviewAnonymityMode $anonymity_mode
 * @property int $author_user_id
 * @property string $body
 * @property int $current_version
 * @property CarbonImmutable|null $deleted_at
 * @property PlaceReviewEligibilityContext $eligibility_context
 * @property int $id
 * @property string $idempotency_key
 * @property PlaceReviewModerationStatus $moderation_status
 * @property int|null $pet_profile_id
 * @property int $place_id
 * @property int $rating_overall
 * @property int|null $rating_service
 * @property int|null $rating_accessibility
 * @property int|null $rating_pet_friendliness
 * @property string $stable_key
 * @property bool $verified_visit
 * @property-read User $author
 * @property-read Place $place
 * @property-read PlaceReviewResponse|null $response
 * @property-read Collection<int, PlaceReviewVersion> $versions
 * @property-read Collection<int, PlaceReviewEvent> $events
 */
final class PlaceReview extends Model
{
    /** @use HasFactory<PlaceReviewFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'place_id', 'author_user_id', 'pet_profile_id', 'moderator_user_id', 'stable_key',
        'idempotency_key', 'eligibility_context', 'verified_visit', 'rating_overall',
        'rating_service', 'rating_accessibility', 'rating_pet_friendliness', 'body',
        'anonymity_mode', 'moderation_status', 'current_version', 'moderation_reason',
        'deletion_reason', 'restored_at',
    ];

    protected $hidden = ['idempotency_key', 'moderation_reason', 'deletion_reason'];

    protected function casts(): array
    {
        return [
            'eligibility_context' => PlaceReviewEligibilityContext::class,
            'verified_visit' => 'boolean',
            'anonymity_mode' => PlaceReviewAnonymityMode::class,
            'moderation_status' => PlaceReviewModerationStatus::class,
            'current_version' => 'integer',
            'restored_at' => 'immutable_datetime',
            'deleted_at' => 'immutable_datetime',
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

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class);
    }

    /** @return BelongsTo<User, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }

    /** @return HasMany<PlaceReviewVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(PlaceReviewVersion::class)->orderBy('version');
    }

    /** @return HasMany<PlaceReviewEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceReviewEvent::class)->orderBy('created_at')->orderBy('id');
    }

    /** @return HasOne<PlaceReviewResponse, $this> */
    public function response(): HasOne
    {
        return $this->hasOne(PlaceReviewResponse::class);
    }

    /** @param Builder<PlaceReview> $query @return Builder<PlaceReview> */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('moderation_status', PlaceReviewModerationStatus::Published->value);
    }
}
