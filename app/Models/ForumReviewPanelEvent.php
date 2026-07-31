<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReviewPanelEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class ForumReviewPanelEvent extends Model
{
    /** @use HasFactory<ForumReviewPanelEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_review_panel_id',
        'actor_user_id',
        'event_type',
        'from_state',
        'to_state',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
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
        self::updating(static fn (): never => throw new LogicException(
            'Forum review panel events are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum review panel events are append-only.',
        ));
    }

    /** @return BelongsTo<ForumReviewPanel, $this> */
    public function panel(): BelongsTo
    {
        return $this->belongsTo(ForumReviewPanel::class, 'forum_review_panel_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
