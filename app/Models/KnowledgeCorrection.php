<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KnowledgeCorrectionStatus;
use Database\Factories\KnowledgeCorrectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read KnowledgeArticle|null $article
 * @property int $article_id
 * @property int|null $base_version_number
 * @property Carbon|null $created_at
 * @property string|null $decision_reason
 * @property string $field
 * @property int $id
 * @property string $reporter_key
 * @property int|null $reporter_user_id
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by_user_id
 * @property string|null $source_url
 * @property KnowledgeCorrectionStatus $status
 * @property string $suggestion
 * @property Carbon|null $updated_at
 * @property-read User|null $reporter
 * @property-read User|null $reviewer
 */
class KnowledgeCorrection extends Model
{
    /** @use HasFactory<KnowledgeCorrectionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'reporter_key',
        'reporter_user_id',
        'field',
        'suggestion',
        'source_url',
        'status',
        'base_version_number',
        'reviewed_by_user_id',
        'reviewed_at',
        'decision_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeCorrectionStatus::class,
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\KnowledgeArticle, $this>*/
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
