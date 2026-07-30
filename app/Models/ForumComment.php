<?php

namespace App\Models;

use Database\Factories\ForumCommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumComment extends Model
{
    /** @use HasFactory<ForumCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'answer_id',
        'parent_id',
        'author_id',
        'author_key',
        'author_name',
        'author_initials',
        'body',
        'status',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeForThread(Builder $query): Builder
    {
        return $query->select([
            'id',
            'topic_id',
            'answer_id',
            'parent_id',
            'author_key',
            'author_name',
            'author_initials',
            'body',
            'status',
            'is_pinned',
            'created_at',
        ])->where('status', 'published');
    }
}
