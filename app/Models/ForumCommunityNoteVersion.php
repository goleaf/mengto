<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumCommunityNoteStatus;
use Database\Factories\ForumCommunityNoteVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class ForumCommunityNoteVersion extends Model
{
    /** @use HasFactory<ForumCommunityNoteVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_community_note_id',
        'version_number',
        'editor_user_id',
        'status',
        'body',
        'evidence',
        'author_response',
        'change_reason',
        'source_event',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumCommunityNoteStatus::class,
            'evidence' => 'array',
            'metadata' => 'array',
            'version_number' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException(
            'Forum community note versions are append-only.',
        ));
        self::deleting(static fn (): never => throw new LogicException(
            'Forum community note versions are append-only.',
        ));
    }

    /** @return BelongsTo<ForumCommunityNote, $this> */
    public function note(): BelongsTo
    {
        return $this->belongsTo(ForumCommunityNote::class, 'forum_community_note_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_user_id');
    }
}
