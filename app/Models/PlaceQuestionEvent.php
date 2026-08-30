<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceQuestionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceQuestionEvent extends Model
{
    /** @use HasFactory<PlaceQuestionEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_question_id',
        'actor_user_id',
        'idempotency_key',
        'event_type',
        'from_status',
        'to_status',
        'public_summary_key',
        'private_note',
        'created_at',
    ];

    protected $hidden = ['idempotency_key', 'private_note'];

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(PlaceQuestion::class, 'place_question_id');
    }
}
