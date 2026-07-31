<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable([
    'actor_key',
    'name',
    'email',
    'password',
    'locale',
    'timezone',
    'status',
])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property string $actor_key
 * @property Carbon|null $created_at
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property int $id
 * @property bool $is_admin
 * @property CarbonImmutable|null $last_login_at
 * @property string $locale
 * @property string $name
 * @property string $password
 * @property string|null $remember_token
 * @property UserStatus $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use MustVerifyEmail;
    use Notifiable;

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isAdministrator(): bool
    {
        return $this->is_admin && $this->isActive();
    }

    /** @return HasMany<PetProfile, $this> */
    public function petProfiles(): HasMany
    {
        return $this->hasMany(PetProfile::class);
    }

    /** @return HasMany<SearchCase, $this> */
    public function searchCases(): HasMany
    {
        return $this->hasMany(SearchCase::class, 'owner_id');
    }

    /** @return HasMany<SearchContactRelay, $this> */
    public function sentSearchContactRelays(): HasMany
    {
        return $this->hasMany(SearchContactRelay::class, 'sender_user_id');
    }

    /** @return HasMany<SearchContactRelay, $this> */
    public function receivedSearchContactRelays(): HasMany
    {
        return $this->hasMany(SearchContactRelay::class, 'recipient_user_id');
    }

    /** @return HasMany<UserDomainState, $this> */
    public function domainStates(): HasMany
    {
        return $this->hasMany(UserDomainState::class);
    }

    /** @return HasMany<ExpertProfile, $this> */
    public function expertProfiles(): HasMany
    {
        return $this->hasMany(ExpertProfile::class, 'owner_id');
    }

    /** @return HasMany<KnowledgeArticle, $this> */
    public function createdKnowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'created_by_user_id');
    }

    /** @return HasMany<KnowledgeArticleCollaborator, $this> */
    public function knowledgeCollaborations(): HasMany
    {
        return $this->hasMany(KnowledgeArticleCollaborator::class);
    }

    /** @return HasMany<ForumCommunityNote, $this> */
    public function proposedCommunityNotes(): HasMany
    {
        return $this->hasMany(ForumCommunityNote::class, 'proposer_user_id');
    }

    /** @return HasMany<ForumReviewAssignment, $this> */
    public function forumReviewAssignments(): HasMany
    {
        return $this->hasMany(ForumReviewAssignment::class, 'reviewer_user_id');
    }

    /** @return HasMany<ForumUserTrustLevel, $this> */
    public function forumTrustAssignments(): HasMany
    {
        return $this->hasMany(ForumUserTrustLevel::class);
    }

    /** @return HasOne<ForumMentorProfile, $this> */
    public function mentorProfile(): HasOne
    {
        return $this->hasOne(ForumMentorProfile::class);
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorshipsAsMentor(): HasMany
    {
        return $this->hasMany(ForumMentorship::class, 'mentor_user_id');
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorshipsAsMentee(): HasMany
    {
        return $this->hasMany(ForumMentorship::class, 'mentee_user_id');
    }

    /** @return HasMany<ForumMentorshipMessage, $this> */
    public function mentorshipMessages(): HasMany
    {
        return $this->hasMany(ForumMentorshipMessage::class, 'sender_user_id');
    }

    /** @return HasMany<ForumMentorshipFeedback, $this> */
    public function mentorshipFeedback(): HasMany
    {
        return $this->hasMany(ForumMentorshipFeedback::class, 'author_user_id');
    }

    /** @return HasMany<ForumGroup, $this> */
    public function ownedForumGroups(): HasMany
    {
        return $this->hasMany(ForumGroup::class, 'owner_user_id');
    }

    /** @return HasMany<ForumGroupMembership, $this> */
    public function forumGroupMemberships(): HasMany
    {
        return $this->hasMany(ForumGroupMembership::class);
    }

    /** @return HasMany<ForumGroupInvitation, $this> */
    public function forumGroupInvitations(): HasMany
    {
        return $this->hasMany(ForumGroupInvitation::class, 'invited_user_id');
    }

    /** @return HasMany<ForumEvent, $this> */
    public function organizedForumEvents(): HasMany
    {
        return $this->hasMany(ForumEvent::class, 'organizer_user_id');
    }

    /** @return HasMany<ForumEventRegistration, $this> */
    public function forumEventRegistrations(): HasMany
    {
        return $this->hasMany(ForumEventRegistration::class);
    }

    /** @return HasMany<ForumEventInvitation, $this> */
    public function forumEventInvitations(): HasMany
    {
        return $this->hasMany(ForumEventInvitation::class, 'invited_user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'bool',
            'last_login_at' => 'immutable_datetime',
        ];
    }
}
