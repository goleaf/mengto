<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $active_member_count
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $closed_at
 * @property string $creation_idempotency_key
 * @property string $default_locale
 * @property string $description
 * @property string|null $description_translation_key
 * @property int $id
 * @property bool $is_system_managed
 * @property string|null $location_scope
 * @property int $lock_version
 * @property list<string> $membership_questions
 * @property string $name
 * @property string|null $name_translation_key
 * @property int|null $owner_user_id
 * @property list<string> $rules
 * @property string $stable_key
 * @property ForumGroupStatus $status
 * @property ForumGroupVisibility $visibility
 * @property-read Collection<int, ForumGroupActivity> $activities
 * @property-read Collection<int, ForumGroupAnnouncement> $announcements
 * @property-read Collection<int, ForumGroupFile> $files
 * @property-read Collection<int, ForumEvent> $scheduledEvents
 * @property-read User|null $owner
 * @property-read Collection<int, ForumPoll> $polls
 */
final class ForumGroup extends Model
{
    /** @use HasFactory<ForumGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'stable_key',
        'creation_idempotency_key',
        'is_system_managed',
        'name',
        'name_translation_key',
        'description',
        'description_translation_key',
        'rules',
        'visibility',
        'status',
        'default_locale',
        'location_scope',
        'membership_questions',
        'active_member_count',
        'lock_version',
        'closed_at',
        'archived_at',
    ];

    protected $attributes = [
        'is_system_managed' => false,
        'status' => 'active',
        'active_member_count' => 1,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_system_managed' => 'boolean',
            'rules' => 'array',
            'visibility' => ForumGroupVisibility::class,
            'status' => ForumGroupStatus::class,
            'membership_questions' => 'array',
            'active_member_count' => 'integer',
            'lock_version' => 'integer',
            'closed_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    public function displayName(): string
    {
        return $this->translatedValue($this->name_translation_key, $this->name);
    }

    public function displayDescription(): string
    {
        return $this->translatedValue(
            $this->description_translation_key,
            $this->description,
        );
    }

    /** @return list<string> */
    public function displayRules(): array
    {
        if (! $this->is_system_managed) {
            return $this->rules;
        }

        return array_map(
            static fn (string $rule): string => __($rule),
            $this->rules,
        );
    }

    /** @return list<string> */
    public function displayMembershipQuestions(): array
    {
        if (! $this->is_system_managed) {
            return $this->membership_questions;
        }

        return array_map(
            static fn (string $question): string => __($question),
            $this->membership_questions,
        );
    }

    public function hasActiveMembership(User $user): bool
    {
        if ($this->relationLoaded('memberships')) {
            return $this->memberships->contains(
                static fn (ForumGroupMembership $membership): bool => $membership->user_id === $user->id
                    && $membership->state === ForumGroupMembershipState::Active,
            );
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->exists();
    }

    public function membershipFor(User $user): ?ForumGroupMembership
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * @param  Builder<ForumGroup>  $query
     * @return Builder<ForumGroup>
     */
    public function scopeDiscoverableTo(Builder $query, User $user): Builder
    {
        return $query
            ->where('status', ForumGroupStatus::Active->value)
            ->where(function (Builder $visibilityQuery) use ($user): void {
                $visibilityQuery
                    ->whereIn('visibility', [
                        ForumGroupVisibility::Public->value,
                        ForumGroupVisibility::RequestToJoin->value,
                    ])
                    ->orWhere('owner_user_id', $user->id)
                    ->orWhereHas('memberships', function (Builder $membershipQuery) use ($user): void {
                        $membershipQuery
                            ->where('user_id', $user->id)
                            ->where('state', ForumGroupMembershipState::Active->value);
                    });
            });
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<ForumGroupMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(ForumGroupMembership::class);
    }

    /** @return HasMany<ForumGroupInvitation, $this> */
    public function invitations(): HasMany
    {
        return $this->hasMany(ForumGroupInvitation::class);
    }

    /** @return HasMany<ForumGroupEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumGroupEvent::class);
    }

    /** @return HasMany<ForumTopic, $this> */
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    /** @return HasMany<KnowledgeArticle, $this> */
    public function guides(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class);
    }

    /** @return HasMany<ForumGroupActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(ForumGroupActivity::class);
    }

    /** @return HasMany<ForumEvent, $this> */
    public function scheduledEvents(): HasMany
    {
        return $this->hasMany(ForumEvent::class);
    }

    /** @return HasMany<ForumGroupAnnouncement, $this> */
    public function announcements(): HasMany
    {
        return $this->hasMany(ForumGroupAnnouncement::class);
    }

    /** @return HasMany<ForumGroupFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(ForumGroupFile::class);
    }

    /** @return HasMany<ForumPoll, $this> */
    public function polls(): HasMany
    {
        return $this->hasMany(ForumPoll::class);
    }

    /** @return BelongsToMany<Taxon, $this> */
    public function taxa(): BelongsToMany
    {
        return $this->belongsToMany(Taxon::class, 'forum_group_taxon')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    private function translatedValue(?string $key, string $fallback): string
    {
        if ($key === null || $key === '') {
            return $fallback;
        }

        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
