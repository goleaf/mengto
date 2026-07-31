<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use Database\Factories\ForumJournalCollaboratorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property-read ForumJournal|null $journal
 * @property int $forum_journal_id
 * @property Carbon $granted_at
 * @property-read User|null $grantedBy
 * @property int|null $granted_by_user_id
 * @property int $id
 * @property Carbon|null $revoked_at
 * @property ForumJournalCollaboratorRole $role
 * @property ForumJournalCollaboratorState $state
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property int $user_id
 */
final class ForumJournalCollaborator extends Model
{
    /** @use HasFactory<ForumJournalCollaboratorFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_journal_id',
        'user_id',
        'granted_by_user_id',
        'role',
        'state',
        'granted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => ForumJournalCollaboratorRole::class,
            'state' => ForumJournalCollaboratorState::class,
            'granted_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumJournal, $this> */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(ForumJournal::class, 'forum_journal_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
