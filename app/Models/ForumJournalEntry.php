<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumJournalEntryKind;
use Database\Factories\ForumJournalEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $author_key
 * @property string $author_name
 * @property int|null $author_user_id
 * @property string $body
 * @property-read Collection<int, ForumComment> $comments
 * @property Carbon|null $created_at
 * @property int $forum_journal_id
 * @property int $id
 * @property string $idempotency_key
 * @property ForumJournalEntryKind $kind
 * @property-read ForumJournal|null $journal
 * @property int $lock_version
 * @property-read Collection<int, ForumJournalMeasurement> $measurements
 * @property-read Collection<int, ForumJournalMedia> $media
 * @property Carbon $occurred_at
 * @property string $stable_key
 * @property string $timezone
 * @property string $title
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ForumJournalEntryVersion> $versions
 */
final class ForumJournalEntry extends Model
{
    /** @use HasFactory<ForumJournalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_journal_id',
        'author_user_id',
        'author_key',
        'author_name',
        'stable_key',
        'idempotency_key',
        'kind',
        'occurred_at',
        'timezone',
        'title',
        'body',
        'lock_version',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return [
            'kind' => ForumJournalEntryKind::class,
            'occurred_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<ForumJournal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(ForumJournal::class, 'forum_journal_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return HasMany<ForumJournalMeasurement, $this> */
    public function measurements(): HasMany
    {
        return $this->hasMany(ForumJournalMeasurement::class);
    }

    /** @return HasMany<ForumJournalMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ForumJournalMedia::class);
    }

    /** @return HasMany<ForumJournalEntryVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(ForumJournalEntryVersion::class);
    }

    /** @return HasMany<ForumComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class);
    }

    public function scopeForTimeline(Builder $query): Builder
    {
        return $query->select([
            'id',
            'forum_journal_id',
            'author_user_id',
            'author_key',
            'author_name',
            'stable_key',
            'kind',
            'occurred_at',
            'timezone',
            'title',
            'body',
            'lock_version',
            'created_at',
            'updated_at',
        ]);
    }
}
