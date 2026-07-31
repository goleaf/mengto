<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read ForumAnswer|null $answer
 * @property int|null $answer_id
 * @property int|null $author_id
 * @property string $author_initials
 * @property string $author_key
 * @property string $author_name
 * @property string $body
 * @property Carbon|null $created_at
 * @property int $id
 * @property string|null $idempotency_key
 * @property bool $is_pinned
 * @property-read ForumComment|null $parent
 * @property int|null $parent_id
 * @property-read Collection<int, ForumComment> $replies
 * @property string $status
 * @property-read ForumJournalEntry|null $journalEntry
 * @property int|null $forum_journal_entry_id
 * @property-read ForumTopic|null $topic
 * @property int $topic_id
 * @property Carbon|null $updated_at
 */
class ForumComment extends Model
{
    /** @use HasFactory<ForumCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'answer_id',
        'forum_journal_entry_id',
        'parent_id',
        'author_id',
        'author_key',
        'author_name',
        'author_initials',
        'body',
        'status',
        'is_pinned',
        'idempotency_key',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    /** @return BelongsTo<\App\Models\ForumAnswer, $this>*/
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ForumAnswer::class, 'answer_id');
    }

    /** @return BelongsTo<ForumJournalEntry, $this> */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(ForumJournalEntry::class);
    }

    /** @return BelongsTo<\App\Models\ForumComment, $this>*/
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<\App\Models\ForumComment, $this>*/
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

    public function scopeForJournalEntry(Builder $query): Builder
    {
        return $query->select([
            'id',
            'topic_id',
            'forum_journal_entry_id',
            'author_id',
            'author_key',
            'author_name',
            'author_initials',
            'body',
            'status',
            'created_at',
        ])->where('status', 'published');
    }
}
