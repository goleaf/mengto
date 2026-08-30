<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceReviewResponseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property int $author_user_id @property string $body @property int $current_version @property int $id @property string $idempotency_key @property int $place_review_id @property string $stable_key */
final class PlaceReviewResponse extends Model
{
    /** @use HasFactory<PlaceReviewResponseFactory> */
    use HasFactory;

    protected $fillable = ['place_review_id', 'author_user_id', 'stable_key', 'idempotency_key', 'body', 'current_version'];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['current_version' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<PlaceReview, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(PlaceReview::class, 'place_review_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return HasMany<PlaceReviewResponseVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(PlaceReviewResponseVersion::class)->orderBy('version');
    }
}
