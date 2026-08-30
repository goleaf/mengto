<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceQuestionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceQuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property CarbonImmutable|null $answered_at
 * @property int $author_user_id
 * @property string $body
 * @property int $id
 * @property string $idempotency_key
 * @property int $place_id
 * @property string $stable_key
 * @property PlaceQuestionStatus $status
 * @property-read PlaceQuestionAnswer|null $answer
 * @property-read User $author
 * @property-read Place $place
 */
final class PlaceQuestion extends Model
{
    /** @use HasFactory<PlaceQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'body',
        'status',
        'moderation_status',
        'duplicate_question_id',
        'closed_by_user_id',
        'answered_at',
        'closed_at',
        'close_reason',
    ];

    protected $hidden = ['idempotency_key'];

    protected $attributes = ['status' => 'open'];

    protected function casts(): array
    {
        return [
            'status' => PlaceQuestionStatus::class,
            'answered_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
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

    /** @return HasOne<PlaceQuestionAnswer, $this> */
    public function answer(): HasOne
    {
        return $this->hasOne(PlaceQuestionAnswer::class);
    }

    /** @return HasMany<PlaceQuestionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceQuestionEvent::class);
    }

    /** @return MorphMany<ForumReport, $this> */
    public function reports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }

    /** @param Builder<PlaceQuestion> $query @return Builder<PlaceQuestion> */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('moderation_status', 'approved')
            ->whereNotIn('status', [
                PlaceQuestionStatus::Hidden->value,
                PlaceQuestionStatus::Removed->value,
            ]);
    }
}
