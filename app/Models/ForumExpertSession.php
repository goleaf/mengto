<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumExpertSessionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumExpertSessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable|null $archived_at
 * @property int|null $archived_by_user_id
 * @property string|null $archive_reason_code
 * @property string $creation_idempotency_key
 * @property int $created_by_user_id
 * @property string $disclaimer_version
 * @property CarbonImmutable $ends_at
 * @property int $expert_profile_id
 * @property string $host_name_snapshot
 * @property int $id
 * @property string $jurisdiction
 * @property string $locale
 * @property int $lock_version
 * @property string $professional_scope
 * @property CarbonImmutable $question_closes_at
 * @property CarbonImmutable $question_opens_at
 * @property CarbonImmutable $starts_at
 * @property ForumExpertSessionStatus $status
 * @property string $stable_key
 * @property string $summary
 * @property string $title
 * @property string $timezone
 * @property-read User|null $archivedBy
 * @property-read Collection<int, ForumExpertSessionAnswer> $answers
 * @property-read User $creator
 * @property-read ExpertProfile $expertProfile
 * @property-read Collection<int, ForumExpertSessionHistory> $history
 * @property-read Collection<int, ForumExpertSessionQuestion> $questions
 */
final class ForumExpertSession extends Model
{
    /** @use HasFactory<ForumExpertSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id',
        'created_by_user_id',
        'stable_key',
        'creation_idempotency_key',
        'host_name_snapshot',
        'professional_scope',
        'jurisdiction',
        'title',
        'summary',
        'locale',
        'timezone',
        'status',
        'disclaimer_version',
        'question_opens_at',
        'question_closes_at',
        'starts_at',
        'ends_at',
        'archived_by_user_id',
        'archived_at',
        'archive_reason_code',
        'lock_version',
    ];

    protected $hidden = ['creation_idempotency_key'];

    protected $attributes = [
        'status' => 'published',
        'timezone' => 'UTC',
        'disclaimer_version' => '2026-07',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => ForumExpertSessionStatus::class,
            'question_opens_at' => 'immutable_datetime',
            'question_closes_at' => 'immutable_datetime',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<ExpertProfile, $this> */
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by_user_id');
    }

    /** @return HasMany<ForumExpertSessionQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(ForumExpertSessionQuestion::class);
    }

    /** @return HasMany<ForumExpertSessionAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(ForumExpertSessionAnswer::class);
    }

    /** @return HasMany<ForumExpertSessionCorrection, $this> */
    public function corrections(): HasMany
    {
        return $this->hasMany(ForumExpertSessionCorrection::class);
    }

    /** @return HasMany<ForumExpertSessionHistory, $this> */
    public function history(): HasMany
    {
        return $this->hasMany(ForumExpertSessionHistory::class);
    }

    public function isHost(User $user): bool
    {
        return $this->created_by_user_id === $user->id;
    }

    public function acceptsQuestions(): bool
    {
        return $this->status === ForumExpertSessionStatus::Published
            && now()->betweenIncluded($this->question_opens_at, $this->question_closes_at);
    }

    public function phase(): string
    {
        if ($this->status === ForumExpertSessionStatus::Archived) {
            return 'archived';
        }

        if ($this->status === ForumExpertSessionStatus::Cancelled) {
            return 'cancelled';
        }

        if (now()->lt($this->question_opens_at)) {
            return 'scheduled';
        }

        if (now()->betweenIncluded($this->question_opens_at, $this->question_closes_at)) {
            return 'questions-open';
        }

        if (now()->betweenIncluded($this->starts_at, $this->ends_at)) {
            return 'live';
        }

        return now()->gt($this->ends_at) ? 'ended' : 'questions-closed';
    }

    /**
     * @param  Builder<ForumExpertSession>  $query
     * @return Builder<ForumExpertSession>
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', ForumExpertSessionStatus::Published->value)
            ->whereNull('archived_at');
    }
}
