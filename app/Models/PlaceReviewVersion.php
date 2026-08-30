<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceReviewVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $place_review_id @property int $version @property string $body @property CarbonImmutable $created_at */
final class PlaceReviewVersion extends Model
{
    /** @use HasFactory<PlaceReviewVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_review_id', 'editor_user_id', 'idempotency_key', 'version', 'rating_overall',
        'rating_service', 'rating_accessibility', 'rating_pet_friendliness', 'body',
        'anonymity_mode', 'reason', 'created_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceReview, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(PlaceReview::class, 'place_review_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_user_id');
    }
}
