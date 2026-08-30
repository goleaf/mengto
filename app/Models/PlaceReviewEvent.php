<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceReviewEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $place_review_id @property string $event_type @property CarbonImmutable $created_at */
final class PlaceReviewEvent extends Model
{
    /** @use HasFactory<PlaceReviewEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_review_id', 'actor_user_id', 'idempotency_key', 'event_type', 'from_status',
        'to_status', 'public_summary_key', 'private_note', 'created_at',
    ];

    protected $hidden = ['idempotency_key', 'private_note'];

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
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
