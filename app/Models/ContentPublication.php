<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentAudienceActorEffect;
use App\Enums\ContentAudienceType;
use App\Enums\ContentPublicationStatus;
use App\Enums\ContentPublicationType;
use App\Enums\ForumGroupMembershipState;
use App\Enums\SocialActorStatus;
use App\Enums\SocialRelationshipStatus;
use App\Enums\SocialRelationshipType;
use App\Services\ContentAudienceCompatibility;
use Closure;
use Database\Factories\ContentPublicationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string|null $body
 * @property ContentPublicationType $content_type
 * @property Carbon|null $created_at
 * @property Carbon|null $expires_at
 * @property int $id
 * @property string $creation_fingerprint
 * @property string $idempotency_key
 * @property string $language
 * @property int $lock_version
 * @property Carbon|null $published_at
 * @property int $publishing_actor_id
 * @property string $publication_key
 * @property int $real_author_user_id
 * @property string $representation_role
 * @property Carbon|null $scheduled_at
 * @property ContentPublicationStatus $status
 * @property string|null $summary
 * @property string|null $title
 * @property Carbon|null $updated_at
 * @property-read ContentAudienceRule $audienceRule
 * @property-read Collection<int, ContentDomainLink> $domainLinks
 * @property-read Collection<int, ContentPublicationEvent> $events
 * @property-read ContentInteractionSetting $interactionSettings
 * @property-read Collection<int, ContentMediaAsset> $mediaAssets
 * @property-read SocialActor $publishingActor
 * @property-read User $realAuthor
 */
final class ContentPublication extends Model
{
    /** @use HasFactory<ContentPublicationFactory> */
    use HasFactory;

    protected $fillable = [
        'publication_key',
        'real_author_user_id',
        'publishing_actor_id',
        'representation_role',
        'content_type',
        'status',
        'language',
        'title',
        'summary',
        'body',
        'lock_version',
        'creation_fingerprint',
        'idempotency_key',
        'published_at',
        'scheduled_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'content_type' => ContentPublicationType::class,
            'status' => ContentPublicationStatus::class,
            'lock_version' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'publication_key';
    }

    /** @return BelongsTo<User, $this> */
    public function realAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'real_author_user_id');
    }

    /** @return BelongsTo<SocialActor, $this> */
    public function publishingActor(): BelongsTo
    {
        return $this->belongsTo(SocialActor::class, 'publishing_actor_id');
    }

    /** @return HasOne<ContentAudienceRule, $this> */
    public function audienceRule(): HasOne
    {
        return $this->hasOne(ContentAudienceRule::class);
    }

    /** @return HasOne<ContentInteractionSetting, $this> */
    public function interactionSettings(): HasOne
    {
        return $this->hasOne(ContentInteractionSetting::class);
    }

    /** @return HasMany<ContentDomainLink, $this> */
    public function domainLinks(): HasMany
    {
        return $this->hasMany(ContentDomainLink::class);
    }

    /** @return BelongsToMany<ContentMediaAsset, $this> */
    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            ContentMediaAsset::class,
            'content_publication_media',
        )->withPivot(['position', 'is_cover', 'caption'])->withTimestamps();
    }

    /** @return HasMany<ContentPublicationEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ContentPublicationEvent::class);
    }

    /** @param Builder<ContentPublication> $query */
    public function scopeFeedFields(Builder $query): Builder
    {
        return $query->select([
            'id',
            'publication_key',
            'real_author_user_id',
            'publishing_actor_id',
            'representation_role',
            'content_type',
            'status',
            'language',
            'title',
            'summary',
            'body',
            'lock_version',
            'published_at',
            'expires_at',
            'created_at',
            'updated_at',
        ]);
    }

    /** @param Builder<ContentPublication> $query */
    public function scopePublishedNow(Builder $query): Builder
    {
        return $query
            ->where('status', ContentPublicationStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * @param  Builder<ContentPublication>  $query
     * @param  list<int>  $viewerActorIds
     * @param  list<int>  $blockedActorIds
     */
    public function scopeVisibleTo(
        Builder $query,
        ?User $viewer,
        array $viewerActorIds = [],
        array $blockedActorIds = [],
    ): Builder {
        $viewer = $viewer?->isActive() === true ? $viewer : null;

        $query
            ->publishedNow()
            ->whereHas('publishingActor', fn (Builder $actor): Builder => $actor
                ->where('status', SocialActorStatus::Active->value));

        if ($blockedActorIds !== []) {
            $query->whereNotIn('publishing_actor_id', $blockedActorIds);
        }

        if ($viewer === null) {
            return $query
                ->whereHas('publishingActor', fn (Builder $actor): Builder => ContentAudienceCompatibility::constrainActor(
                    $actor,
                    ContentAudienceType::Everyone,
                ))
                ->whereHas('audienceRule', fn (Builder $audience): Builder => $audience
                    ->where(function (Builder $expiry): void {
                        $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->where('audience_type', ContentAudienceType::Everyone->value));
        }

        return $query
            ->whereHas('audienceRule', function (Builder $audience) use ($viewer, $viewerActorIds): void {
                $audience
                    ->where(function (Builder $expiry): void {
                        $expiry->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->where(function (Builder $allowed) use ($viewer, $viewerActorIds): void {
                        $allowed
                            ->where(function (Builder $everyone): void {
                                $everyone
                                    ->where('audience_type', ContentAudienceType::Everyone->value)
                                    ->whereHas(
                                        'publication.publishingActor',
                                        fn (Builder $actor): Builder => ContentAudienceCompatibility::constrainActor(
                                            $actor,
                                            ContentAudienceType::Everyone,
                                        ),
                                    );
                            })
                            ->orWhere(function (Builder $registered): void {
                                $registered
                                    ->where('audience_type', ContentAudienceType::Registered->value)
                                    ->whereHas(
                                        'publication.publishingActor',
                                        fn (Builder $actor): Builder => ContentAudienceCompatibility::constrainActor(
                                            $actor,
                                            ContentAudienceType::Registered,
                                        ),
                                    );
                            });

                        if ($viewerActorIds !== []) {
                            $allowed
                                ->orWhere(function (Builder $selected) use ($viewerActorIds): void {
                                    $selected
                                        ->where('audience_type', ContentAudienceType::Selected->value)
                                        ->whereHas('actors', fn (Builder $actors): Builder => $actors
                                            ->whereIn('social_actor_id', $viewerActorIds)
                                            ->where('effect', ContentAudienceActorEffect::Include->value));
                                })
                                ->orWhere(function (Builder $followers) use ($viewerActorIds): void {
                                    $followers
                                        ->where('audience_type', ContentAudienceType::Followers->value)
                                        ->whereHas(
                                            'publication.publishingActor.incomingRelationships',
                                            self::activeRelationship(
                                                'source_actor_id',
                                                $viewerActorIds,
                                                [SocialRelationshipType::Follow],
                                            ),
                                        );
                                })
                                ->orWhere(function (Builder $friends) use ($viewerActorIds): void {
                                    $friends
                                        ->where('audience_type', ContentAudienceType::Friends->value)
                                        ->where(function (Builder $relations) use ($viewerActorIds): void {
                                            $types = [
                                                SocialRelationshipType::OwnerFriendship,
                                                SocialRelationshipType::PetFriendship,
                                            ];

                                            $relations
                                                ->whereHas(
                                                    'publication.publishingActor.incomingRelationships',
                                                    self::activeRelationship('source_actor_id', $viewerActorIds, $types),
                                                )
                                                ->orWhereHas(
                                                    'publication.publishingActor.outgoingRelationships',
                                                    self::activeRelationship('target_actor_id', $viewerActorIds, $types),
                                                );
                                        });
                                })
                                ->orWhere(function (Builder $closeCircle) use ($viewerActorIds): void {
                                    $closeCircle
                                        ->where('audience_type', ContentAudienceType::CloseCircle->value)
                                        ->whereHas(
                                            'publication.publishingActor.outgoingRelationships',
                                            self::activeRelationship(
                                                'target_actor_id',
                                                $viewerActorIds,
                                                [SocialRelationshipType::CloseCircle],
                                            ),
                                        );
                                })
                                ->orWhere(function (Builder $family) use ($viewerActorIds): void {
                                    $family
                                        ->where('audience_type', ContentAudienceType::Family->value)
                                        ->where(function (Builder $relations) use ($viewerActorIds): void {
                                            $relations
                                                ->whereHas(
                                                    'publication.publishingActor.incomingRelationships',
                                                    self::activeRelationship(
                                                        'source_actor_id',
                                                        $viewerActorIds,
                                                        [SocialRelationshipType::Family],
                                                    ),
                                                )
                                                ->orWhereHas(
                                                    'publication.publishingActor.outgoingRelationships',
                                                    self::activeRelationship(
                                                        'target_actor_id',
                                                        $viewerActorIds,
                                                        [SocialRelationshipType::Family],
                                                    ),
                                                );
                                        });
                                });
                        }

                        $allowed
                            ->orWhere(function (Builder $group) use ($viewer): void {
                                $group
                                    ->where('audience_type', ContentAudienceType::Group->value)
                                    ->whereHas('contextActor.forumGroup', fn (Builder $forumGroup): Builder => $forumGroup
                                        ->where('owner_user_id', $viewer->id)
                                        ->orWhereHas('memberships', fn (Builder $memberships): Builder => $memberships
                                            ->where('user_id', $viewer->id)
                                            ->where('state', ForumGroupMembershipState::Active->value)));
                            })
                            ->orWhereHas('publication', fn (Builder $publication): Builder => $publication
                                ->where('real_author_user_id', $viewer->id));
                    });

                if ($viewerActorIds !== []) {
                    $audience->whereDoesntHave('actors', fn (Builder $actors): Builder => $actors
                        ->whereIn('social_actor_id', $viewerActorIds)
                        ->where('effect', ContentAudienceActorEffect::Exclude->value));
                }
            });
    }

    /**
     * @param  list<int>  $actorIds
     * @param  list<SocialRelationshipType>  $types
     */
    private static function activeRelationship(
        string $actorColumn,
        array $actorIds,
        array $types,
    ): Closure {
        $typeValues = array_map(
            static fn (SocialRelationshipType $type): string => $type->value,
            $types,
        );

        return static fn (Builder $relations): Builder => $relations
            ->where('status', SocialRelationshipStatus::Active->value)
            ->where(function (Builder $expiry): void {
                $expiry->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->whereIn($actorColumn, $actorIds)
            ->whereIn('relationship_type', $typeValues);
    }
}
