<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReputationAggregateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumReputationAggregate extends Model
{
    /** @use HasFactory<ForumReputationAggregateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_reputation_dimension_id',
        'forum_category_id',
        'taxon_id',
        'location_scope_key',
        'scope_key',
        'total',
        'last_event_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'last_event_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ForumReputationDimension, $this> */
    public function dimension(): BelongsTo
    {
        return $this->belongsTo(ForumReputationDimension::class, 'forum_reputation_dimension_id');
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }
}
