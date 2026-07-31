<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use Database\Factories\KnowledgeWorkflowEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property int $article_id
 * @property int|null $actor_user_id
 * @property Carbon $created_at
 * @property KnowledgeWorkflowEventType $event_type
 * @property KnowledgeStatus|null $from_status
 * @property int $id
 * @property array<array-key, mixed>|null $metadata
 * @property string $reason_code
 * @property string $summary_translation_key
 * @property KnowledgeStatus|null $to_status
 * @property int|null $version_number
 */
final class KnowledgeWorkflowEvent extends Model
{
    /** @use HasFactory<KnowledgeWorkflowEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'actor_user_id',
        'event_type',
        'from_status',
        'to_status',
        'version_number',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected $hidden = [
        'metadata',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => KnowledgeWorkflowEventType::class,
            'from_status' => KnowledgeStatus::class,
            'to_status' => KnowledgeStatus::class,
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Knowledge workflow events are append-only.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Knowledge workflow events are append-only.');
        });
    }

    /** @return BelongsTo<KnowledgeArticle, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
