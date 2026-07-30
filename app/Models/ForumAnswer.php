<?php

namespace App\Models;

use Database\Factories\ForumAnswerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumAnswer extends Model
{
    /** @use HasFactory<ForumAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'author_id',
        'expert_profile_id',
        'author_key',
        'author_name',
        'author_initials',
        'author_role',
        'body',
        'experience_type',
        'is_verified_expert',
        'expertise',
        'qualification_region',
        'sources',
        'media',
        'status',
        'is_accepted',
        'is_highlighted',
        'needs_source',
        'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'media' => 'array',
            'is_verified_expert' => 'boolean',
            'is_accepted' => 'boolean',
            'is_highlighted' => 'boolean',
            'needs_source' => 'boolean',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'answer_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumVote::class, 'answer_id');
    }

    public function scopeForThread(Builder $query): Builder
    {
        return $query->select([
            'id',
            'topic_id',
            'expert_profile_id',
            'author_key',
            'author_name',
            'author_initials',
            'author_role',
            'body',
            'experience_type',
            'is_verified_expert',
            'expertise',
            'qualification_region',
            'sources',
            'media',
            'status',
            'is_accepted',
            'is_highlighted',
            'needs_source',
            'helpful_count',
            'created_at',
            'updated_at',
        ])->where('status', 'published');
    }
}
