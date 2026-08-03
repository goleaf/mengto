<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceQuestionAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $answered_at
 * @property int $author_user_id
 * @property string $body
 * @property int $id
 * @property string $idempotency_key
 * @property int $place_question_id
 * @property string $stable_key
 * @property-read User $author
 * @property-read PlaceQuestion $question
 */
final class PlaceQuestionAnswer extends Model
{
    /** @use HasFactory<PlaceQuestionAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'place_question_id',
        'author_user_id',
        'stable_key',
        'idempotency_key',
        'body',
        'answered_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['answered_at' => 'immutable_datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<PlaceQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(PlaceQuestion::class, 'place_question_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
