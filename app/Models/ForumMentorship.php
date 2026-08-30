<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumMentorshipState;
use App\Enums\ForumMentorshipType;
use Carbon\CarbonImmutable;
use Database\Factories\ForumMentorshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $completion_validated_at
 * @property string $communication_preference
 * @property string|null $end_reason
 * @property CarbonImmutable|null $ended_at
 * @property int|null $ended_by_user_id
 * @property int $forum_mentor_scope_id
 * @property int $id
 * @property string $language
 * @property string|null $location_scope
 * @property int $lock_version
 * @property CarbonImmutable|null $mentee_safety_acknowledged_at
 * @property int $mentee_user_id
 * @property CarbonImmutable|null $mentor_safety_acknowledged_at
 * @property string|null $mentor_response
 * @property int $mentor_user_id
 * @property ForumMentorshipType $mentorship_type
 * @property string|null $open_key
 * @property string $request_message
 * @property CarbonImmutable $requested_at
 * @property ForumMentorshipState $state
 * @property int|null $validated_by_user_id
 * @property-read User $mentee
 * @property-read User $mentor
 * @property-read ForumMentorScope $scope
 */
final class ForumMentorship extends Model
{
    /** @use HasFactory<ForumMentorshipFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_mentor_scope_id',
        'mentor_user_id',
        'mentee_user_id',
        'mentorship_type',
        'state',
        'language',
        'location_scope',
        'communication_preference',
        'request_message',
        'mentor_response',
        'mentee_safety_acknowledged_at',
        'mentor_safety_acknowledged_at',
        'requested_at',
        'accepted_at',
        'declined_at',
        'ended_at',
        'completed_at',
        'completion_validated_at',
        'validated_by_user_id',
        'ended_by_user_id',
        'end_reason',
        'lock_version',
        'open_key',
        'idempotency_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'mentorship_type' => ForumMentorshipType::class,
            'state' => ForumMentorshipState::class,
            'mentee_safety_acknowledged_at' => 'immutable_datetime',
            'mentor_safety_acknowledged_at' => 'immutable_datetime',
            'requested_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'declined_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'completion_validated_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function isParticipant(User $user): bool
    {
        return $this->mentor_user_id === $user->id || $this->mentee_user_id === $user->id;
    }

    public function counterpartId(User $user): ?int
    {
        return match ($user->id) {
            $this->mentor_user_id => $this->mentee_user_id,
            $this->mentee_user_id => $this->mentor_user_id,
            default => null,
        };
    }

    /** @return BelongsTo<ForumMentorScope, $this> */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(ForumMentorScope::class, 'forum_mentor_scope_id');
    }

    /** @return BelongsTo<User, $this> */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by_user_id');
    }

    /** @return HasMany<ForumMentorshipMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ForumMentorshipMessage::class);
    }

    /** @return HasMany<ForumMentorshipFeedback, $this> */
    public function feedback(): HasMany
    {
        return $this->hasMany(ForumMentorshipFeedback::class);
    }

    /** @return HasMany<ForumMentorshipEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumMentorshipEvent::class);
    }

    /** @return MorphMany<ForumReport, $this> */
    public function subjectReports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }
}
