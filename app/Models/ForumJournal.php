<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\ForumJournalCollaboratorState;
use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use Database\Factories\ForumJournalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property string|null $archive_reason_code
 * @property Carbon|null $archived_at
 * @property int|null $archived_by_user_id
 * @property-read User|null $archivedBy
 * @property-read Collection<int, ForumJournalCollaborator> $collaborators
 * @property Carbon|null $created_at
 * @property string $creation_idempotency_key
 * @property-read Collection<int, ForumJournalEntry> $entries
 * @property int $forum_topic_id
 * @property int $id
 * @property int $lock_version
 * @property-read Collection<int, ForumJournalMedia> $media
 * @property array<array-key, mixed>|null $metadata
 * @property-read User|null $owner
 * @property int|null $owner_user_id
 * @property string $owner_key
 * @property ForumJournalStatus $status
 * @property string $stable_key
 * @property Carbon $started_on
 * @property string $timezone
 * @property-read ForumTopic|null $topic
 * @property ForumJournalType $type
 * @property Carbon|null $updated_at
 */
final class ForumJournal extends Model
{
    /** @use HasFactory<ForumJournalFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id',
        'forum_topic_id',
        'owner_user_id',
        'owner_key',
        'stable_key',
        'creation_idempotency_key',
        'type',
        'status',
        'started_on',
        'timezone',
        'lock_version',
        'archived_by_user_id',
        'archived_at',
        'archive_reason_code',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'forum_topic_id',
        'owner_user_id',
        'owner_key',
        'stable_key',
        'creation_idempotency_key',
        'type',
        'status',
        'started_on',
        'timezone',
        'lock_version',
        'archived_by_user_id',
        'archived_at',
        'archive_reason_code',
        'metadata',
    ];

    protected $hidden = [
        'creation_idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumJournalType::class,
            'status' => ForumJournalStatus::class,
            'started_on' => 'immutable_date',
            'lock_version' => 'integer',
            'archived_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_user_id');
    }

    /** @return HasMany<ForumJournalCollaborator, $this> */
    public function collaborators(): HasMany
    {
        return $this->hasMany(ForumJournalCollaborator::class);
    }

    /** @return HasMany<ForumJournalEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(ForumJournalEntry::class);
    }

    /** @return HasManyThrough<ForumJournalMedia, ForumJournalEntry, $this> */
    public function media(): HasManyThrough
    {
        return $this->hasManyThrough(
            ForumJournalMedia::class,
            ForumJournalEntry::class,
        );
    }

    public function scopeForUserDirectory(Builder $query, User $user): Builder
    {
        return $query
            ->select(self::ROUTE_COLUMNS)
            ->where(function (Builder $visibility) use ($user): void {
                $visibility
                    ->where('owner_user_id', $user->id)
                    ->orWhereHas(
                        'collaborators',
                        fn (Builder $collaborators): Builder => $collaborators
                            ->where('user_id', $user->id)
                            ->where('state', ForumJournalCollaboratorState::Active->value),
                    );
            });
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_user_id === $user->id
            || hash_equals($this->owner_key, $user->actor_key);
    }

    public function isArchived(): bool
    {
        return $this->status === ForumJournalStatus::Archived
            || $this->archived_at !== null;
    }

    public function activeCollaboratorRole(User $user): ?ForumJournalCollaboratorRole
    {
        $collaborator = $this->collaborators()
            ->select(['id', 'forum_journal_id', 'user_id', 'role', 'state'])
            ->where('user_id', $user->id)
            ->where('state', ForumJournalCollaboratorState::Active->value)
            ->first();

        return $collaborator?->role;
    }
}
