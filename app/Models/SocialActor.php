<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use Database\Factories\SocialActorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $actor_key
 * @property SocialActorType $actor_type
 * @property Carbon|null $created_at
 * @property Carbon|null $detached_at
 * @property int|null $expert_profile_id
 * @property int|null $forum_group_id
 * @property int $id
 * @property bool $is_discoverable
 * @property int $lock_version
 * @property int|null $pet_profile_id
 * @property SocialActorStatus $status
 * @property Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read ExpertProfile|null $expertProfile
 * @property-read ForumGroup|null $forumGroup
 * @property-read PetProfile|null $petProfile
 * @property-read SocialActorSetting|null $settings
 * @property-read Collection<int, ContentPublication> $contentPublications
 * @property-read User|null $user
 */
final class SocialActor extends Model
{
    /** @use HasFactory<SocialActorFactory> */
    use HasFactory;

    protected $fillable = [
        'actor_key',
        'actor_type',
        'status',
        'user_id',
        'pet_profile_id',
        'expert_profile_id',
        'forum_group_id',
        'is_discoverable',
        'lock_version',
        'detached_at',
    ];

    protected function casts(): array
    {
        return [
            'actor_type' => SocialActorType::class,
            'status' => SocialActorStatus::class,
            'is_discoverable' => 'boolean',
            'lock_version' => 'integer',
            'detached_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'actor_key';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class);
    }

    /** @return BelongsTo<ExpertProfile, $this> */
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function forumGroup(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class);
    }

    /** @return HasOne<SocialActorSetting, $this> */
    public function settings(): HasOne
    {
        return $this->hasOne(SocialActorSetting::class);
    }

    /** @return HasMany<SocialRelationship, $this> */
    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(SocialRelationship::class, 'source_actor_id');
    }

    /** @return HasMany<SocialRelationship, $this> */
    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(SocialRelationship::class, 'target_actor_id');
    }

    /** @return HasMany<ContentPublication, $this> */
    public function contentPublications(): HasMany
    {
        return $this->hasMany(ContentPublication::class, 'publishing_actor_id');
    }

    /** @return HasMany<SocialRelationshipRequest, $this> */
    public function sentRequests(): HasMany
    {
        return $this->hasMany(SocialRelationshipRequest::class, 'source_actor_id');
    }

    /** @return HasMany<SocialRelationshipRequest, $this> */
    public function receivedRequests(): HasMany
    {
        return $this->hasMany(SocialRelationshipRequest::class, 'target_actor_id');
    }

    /**
     * @param  Builder<SocialActor>  $query
     * @return Builder<SocialActor>
     */
    public function scopeDirectoryFields(Builder $query): Builder
    {
        return $query->select([
            'id',
            'actor_key',
            'actor_type',
            'status',
            'user_id',
            'pet_profile_id',
            'expert_profile_id',
            'forum_group_id',
            'is_discoverable',
            'lock_version',
            'detached_at',
            'created_at',
            'updated_at',
        ]);
    }
}
