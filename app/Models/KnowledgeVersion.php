<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\KnowledgeVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read KnowledgeArticle|null $article
 * @property int $article_id
 * @property string $body
 * @property string $change_summary
 * @property Carbon|null $created_at
 * @property string $edited_by
 * @property int $id
 * @property string $title
 * @property Carbon|null $updated_at
 * @property int $version_number
 */
class KnowledgeVersion extends Model
{
    /** @use HasFactory<KnowledgeVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'version_number',
        'title',
        'body',
        'edited_by',
        'change_summary',
    ];

    /** @return BelongsTo<\App\Models\KnowledgeArticle, $this>*/
    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }
}
