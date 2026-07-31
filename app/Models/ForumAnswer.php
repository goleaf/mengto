<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumAnswerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read User|null $author
 * @property int|null $author_id
 * @property string $author_initials
 * @property string $author_key
 * @property string $author_name
 * @property string|null $author_role
 * @property string $body
 * @property-read Collection<int, ForumComment> $comments
 * @property Carbon|null $created_at
 * @property string $experience_type
 * @property-read ExpertProfile|null $expertProfile
 * @property int|null $expert_profile_id
 * @property string|null $expertise
 * @property int $helpful_count
 * @property int $id
 * @property bool $is_accepted
 * @property bool $is_highlighted
 * @property bool $is_verified_expert
 * @property array<array-key, mixed>|null $media
 * @property bool $needs_source
 * @property string|null $qualification_region
 * @property array<array-key, mixed>|null $sources
 * @property string $status
 * @property-read ForumTopic|null $topic
 * @property int $topic_id
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ForumVote> $votes
 */
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

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return HasMany<\App\Models\ForumComment, $this>*/
    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'answer_id');
    }

    /** @return HasMany<\App\Models\ForumVote, $this>*/
    public function votes(): HasMany
    {
        return $this->hasMany(ForumVote::class, 'answer_id');
    }

    /** @return HasMany<ForumCommunityNote, $this> */
    public function communityNotes(): HasMany
    {
        return $this->hasMany(ForumCommunityNote::class, 'subject_id')
            ->where('subject_type', 'forum-answer');
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
