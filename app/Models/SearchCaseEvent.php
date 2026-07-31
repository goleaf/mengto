<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchCaseEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class SearchCaseEvent extends Model
{
    /** @use HasFactory<SearchCaseEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'search_case_id',
        'actor_user_id',
        'event_type',
        'previous_status',
        'current_status',
        'reason_translation_key',
        'idempotency_key',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Search case events are append-only.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Search case events are append-only.');
        });
    }

    /** @return BelongsTo<SearchCase, $this> */
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
