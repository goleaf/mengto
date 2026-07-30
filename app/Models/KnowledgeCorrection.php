<?php

namespace App\Models;

use Database\Factories\KnowledgeCorrectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArticle::class, 'article_id');
    }
}
