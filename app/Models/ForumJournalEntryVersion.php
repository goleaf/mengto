<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumJournalEntryVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $created_at
 * @property-read User|null $editedBy
 * @property int|null $edited_by_user_id
 * @property-read ForumJournalEntry|null $entry
 * @property int $forum_journal_entry_id
 * @property int $id
 * @property string $reason_code
 * @property array<array-key, mixed> $snapshot
 * @property int $version
 */
final class ForumJournalEntryVersion extends Model
{
    /** @use HasFactory<ForumJournalEntryVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_journal_entry_id',
        'edited_by_user_id',
        'version',
        'snapshot',
        'reason_code',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'snapshot' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumJournalEntry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(ForumJournalEntry::class, 'forum_journal_entry_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }
}
