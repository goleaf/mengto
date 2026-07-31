<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReputationEventStatus;
use Database\Factories\ForumReputationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ForumReputationEvent extends Model
{
    /** @use HasFactory<ForumReputationEventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_reputation_dimension_id',
        'actor_user_id',
        'forum_category_id',
        'taxon_id',
        'reversal_of_event_id',
        'event_type',
        'source_entity_type',
        'source_entity_id',
        'amount',
        'reason_code',
        'explanation_translation_key',
        'location_scope_key',
        'status',
        'idempotency_key',
        'metadata',
        'effective_at',
        'expires_at',
        'review_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => ReputationEventStatus::class,
            'metadata' => 'array',
            'effective_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'review_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<ForumReputationDimension, $this> */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(ForumReputationDimension::class, 'forum_reputation_dimension_id');
    }

    /** @return BelongsTo<ForumReputationEvent, $this> */
    public function reversedEvent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_event_id');
    }

    /** @return HasOne<ForumReputationEvent, $this> */
    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reversal_of_event_id');
    }
}
