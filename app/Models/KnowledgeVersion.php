<?php

namespace App\Models;

use Database\Factories\KnowledgeVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }
}
