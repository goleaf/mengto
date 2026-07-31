<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumJournalMediaStatus;
use Database\Factories\ForumJournalMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $alt_text
 * @property int $byte_size
 * @property string|null $caption
 * @property string $checksum
 * @property Carbon|null $created_at
 * @property string $disk
 * @property-read ForumJournalEntry|null $entry
 * @property int $forum_journal_entry_id
 * @property int $id
 * @property string $mime_type
 * @property string $original_name
 * @property string $path
 * @property string $stable_key
 * @property ForumJournalMediaStatus $status
 * @property Carbon|null $updated_at
 * @property string $upload_idempotency_key
 * @property-read User|null $uploadedBy
 * @property int|null $uploaded_by_user_id
 */
final class ForumJournalMedia extends Model
{
    /** @use HasFactory<ForumJournalMediaFactory> */
    use HasFactory;

    protected $table = 'forum_journal_media';

    protected $fillable = [
        'forum_journal_entry_id',
        'uploaded_by_user_id',
        'stable_key',
        'upload_idempotency_key',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'byte_size',
        'checksum',
        'alt_text',
        'caption',
        'status',
    ];

    protected $hidden = [
        'path',
        'original_name',
        'upload_idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'original_name' => 'encrypted',
            'status' => ForumJournalMediaStatus::class,
            'byte_size' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<ForumJournalEntry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(ForumJournalEntry::class, 'forum_journal_entry_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
