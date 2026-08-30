<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceQuestionAnswerVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceQuestionAnswerVersion extends Model
{
    /** @use HasFactory<PlaceQuestionAnswerVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_question_answer_id',
        'editor_user_id',
        'idempotency_key',
        'version',
        'body',
        'reason',
        'created_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['version' => 'integer', 'created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceQuestionAnswer, $this> */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(PlaceQuestionAnswer::class, 'place_question_answer_id');
    }
}
