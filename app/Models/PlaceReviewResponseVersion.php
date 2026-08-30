<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceReviewResponseVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $place_review_response_id @property int $version @property string $body @property CarbonImmutable $created_at */
final class PlaceReviewResponseVersion extends Model
{
    /** @use HasFactory<PlaceReviewResponseVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['place_review_response_id', 'editor_user_id', 'idempotency_key', 'version', 'body', 'reason', 'created_at'];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceReviewResponse, $this> */
    public function response(): BelongsTo
    {
        return $this->belongsTo(PlaceReviewResponse::class, 'place_review_response_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_user_id');
    }
}
