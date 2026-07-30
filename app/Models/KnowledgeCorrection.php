<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\KnowledgeCorrectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read KnowledgeArticle|null $article
 * @property int $article_id
 * @property Carbon|null $created_at
 * @property string $field
 * @property int $id
 * @property string $reporter_key
 * @property string|null $source_url
 * @property string $status
 * @property string $suggestion
 * @property Carbon|null $updated_at
 */
class KnowledgeCorrection extends Model
{
    /** @use HasFactory<KnowledgeCorrectionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'reporter_key',
        'field',
        'suggestion',
        'source_url',
        'status',
    ];

    /** @return BelongsTo<\App\Models\KnowledgeArticle, $this>*/
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }
}
