<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollStatus;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use Carbon\CarbonImmutable;
use Database\Factories\ForumPollFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $closes_at
 * @property string $creation_idempotency_key
 * @property int $created_by_user_id
 * @property string|null $description
 * @property ForumPollEligibility $eligibility
 * @property int $forum_group_id
 * @property int $id
 * @property bool $is_vote_editable
 * @property string|null $location_scope
 * @property int $lock_version
 * @property string $question
 * @property ForumPollResultVisibility $result_visibility
 * @property string $stable_key
 * @property ForumPollStatus $status
 * @property int $total_vote_count
 * @property ForumPollType $type
 * @property ForumPollVoterVisibility $voter_visibility
 * @property-read User $creator
 * @property-read ForumGroup $group
 */
final class ForumPoll extends Model
{
    /** @use HasFactory<ForumPollFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'created_by_user_id',
        'stable_key',
        'creation_idempotency_key',
        'question',
        'description',
        'type',
        'voter_visibility',
        'result_visibility',
        'is_vote_editable',
        'eligibility',
        'location_scope',
        'status',
        'closes_at',
        'total_vote_count',
        'lock_version',
        'archived_at',
    ];

    protected $attributes = [
        'status' => 'active',
        'total_vote_count' => 0,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumPollType::class,
            'voter_visibility' => ForumPollVoterVisibility::class,
            'result_visibility' => ForumPollResultVisibility::class,
            'is_vote_editable' => 'boolean',
            'eligibility' => ForumPollEligibility::class,
            'status' => ForumPollStatus::class,
            'closes_at' => 'immutable_datetime',
            'total_vote_count' => 'integer',
            'lock_version' => 'integer',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function isClosed(): bool
    {
        return $this->status !== ForumPollStatus::Active
            || $this->archived_at !== null
            || ($this->closes_at !== null && ! $this->closes_at->isFuture());
    }

    public function resultsAreVisibleTo(User $user): bool
    {
        return match ($this->result_visibility) {
            ForumPollResultVisibility::Public => true,
            ForumPollResultVisibility::AfterVote => $this->relationLoaded('votes')
                ? $this->votes->contains(
                    static fn (ForumPollVote $vote): bool => $vote->user_id === $user->id,
                )
                : $this->votes()
                    ->where('user_id', $user->id)
                    ->exists(),
            ForumPollResultVisibility::AfterClose => $this->isClosed(),
        };
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return HasMany<ForumPollOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(ForumPollOption::class)->orderBy('position');
    }

    /** @return HasMany<ForumPollVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(ForumPollVote::class);
    }
}
