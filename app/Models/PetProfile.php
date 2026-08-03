<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\PetProfileStatusCast;
use App\Enums\PetBirthDatePrecision;
use App\Enums\PetBreedOriginType;
use App\Enums\PetProfileStatus;
use App\Enums\PetSpeciesConfidence;
use Carbon\CarbonImmutable;
use Database\Factories\PetProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property CarbonImmutable|null $birth_date
 * @property PetBirthDatePrecision $birth_date_precision
 * @property int|null $estimated_age_months
 * @property CarbonImmutable|null $estimated_age_recorded_at
 * @property int|null $birthday_celebration_month
 * @property int|null $birthday_celebration_day
 * @property string|null $breed
 * @property PetBreedOriginType|null $breed_origin_type
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 * @property int $id
 * @property string $name
 * @property array<string, mixed>|null $profile_data
 * @property string $profile_key
 * @property string $slug
 * @property string $species
 * @property PetSpeciesConfidence $species_confidence
 * @property PetProfileStatus $status
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property int $user_id
 * @property string $visibility
 * @property-read Collection<int, PetProfileLifecycleEvent> $lifecycleEvents
 * @property-read Collection<int, PetProfileManager> $managers
 * @property-read Collection<int, PetProfileName> $names
 * @property-read Collection<int, PetProfileBreedOrigin> $breedOrigins
 * @property-read Collection<int, PetProfileAccessRequest> $accessRequests
 * @property-read PetProfileFact|null $currentMicrochipRecord
 * @property-read MedicalRecord|null $medicalRecord
 * @property-read PetProfilePrivacySetting|null $privacySetting
 */
final class PetProfile extends Model
{
    /** @use HasFactory<PetProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'canonical_profile_id',
        'profile_key',
        'slug',
        'name',
        'species',
        'species_confidence',
        'taxon_id',
        'breed',
        'domestic_classification_id',
        'breed_origin_type',
        'birth_date',
        'birth_date_precision',
        'estimated_age_months',
        'estimated_age_recorded_at',
        'birthday_celebration_month',
        'birthday_celebration_day',
        'sex',
        'reproductive_status',
        'visibility',
        'status',
        'creation_key',
        'creator_relationship',
        'is_discoverable',
        'allow_external_indexing',
        'lock_version',
        'state_entered_at',
        'published_at',
        'hidden_at',
        'archived_at',
        'memorialized_at',
        'deletion_requested_at',
        'deletion_scheduled_for',
        'merged_at',
        'profile_data',
    ];

    protected $hidden = ['profile_data'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'breed_origin_type' => PetBreedOriginType::class,
            'birth_date_precision' => PetBirthDatePrecision::class,
            'estimated_age_months' => 'integer',
            'estimated_age_recorded_at' => 'immutable_datetime',
            'birthday_celebration_month' => 'integer',
            'birthday_celebration_day' => 'integer',
            'species_confidence' => PetSpeciesConfidence::class,
            'status' => PetProfileStatusCast::class,
            'is_discoverable' => 'boolean',
            'allow_external_indexing' => 'boolean',
            'lock_version' => 'integer',
            'state_entered_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'hidden_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'memorialized_at' => 'immutable_datetime',
            'deletion_requested_at' => 'immutable_datetime',
            'deletion_scheduled_for' => 'immutable_datetime',
            'merged_at' => 'immutable_datetime',
            'profile_data' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function canonicalProfile(): BelongsTo
    {
        return $this->belongsTo(self::class, 'canonical_profile_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<DomesticClassification, $this> */
    public function domesticClassification(): BelongsTo
    {
        return $this->belongsTo(DomesticClassification::class);
    }

    /** @return HasMany<PetProfileBreedOrigin, $this> */
    public function breedOrigins(): HasMany
    {
        return $this->hasMany(PetProfileBreedOrigin::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    /** @return HasMany<PetProfileManager, $this> */
    public function managers(): HasMany
    {
        return $this->hasMany(PetProfileManager::class);
    }

    /** @return HasMany<PetProfileAccessRequest, $this> */
    public function accessRequests(): HasMany
    {
        return $this->hasMany(PetProfileAccessRequest::class);
    }

    /** @return HasOne<PetProfilePrivacySetting, $this> */
    public function privacySetting(): HasOne
    {
        return $this->hasOne(PetProfilePrivacySetting::class);
    }

    /** @return HasOne<SocialActor, $this> */
    public function socialActor(): HasOne
    {
        return $this->hasOne(SocialActor::class);
    }

    /** @return HasOne<MedicalRecord, $this> */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /** @return HasMany<PetProfileLifecycleEvent, $this> */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(PetProfileLifecycleEvent::class);
    }

    /** @return HasMany<PetProfileName, $this> */
    public function names(): HasMany
    {
        return $this->hasMany(PetProfileName::class);
    }

    /** @return HasMany<PetProfileSlugAlias, $this> */
    public function slugAliases(): HasMany
    {
        return $this->hasMany(PetProfileSlugAlias::class);
    }

    /** @return HasMany<PetProfileFact, $this> */
    public function facts(): HasMany
    {
        return $this->hasMany(PetProfileFact::class);
    }

    /** @return HasOne<PetProfileFact, $this> */
    public function currentMicrochipRecord(): HasOne
    {
        return $this->hasOne(PetProfileFact::class)
            ->where('fact_key', 'microchip-record')
            ->where('is_current', true)
            ->whereNotNull('current_key');
    }

    /** @return HasMany<PetProfileMedia, $this> */
    public function media(): HasMany
    {
        return $this->petProfileMedia();
    }

    /**
     * Relation name used by Laravel's scoped nested route binding.
     *
     * @return HasMany<PetProfileMedia, $this>
     */
    public function petProfileMedia(): HasMany
    {
        return $this->hasMany(PetProfileMedia::class);
    }

    /** @return HasOne<PetProfileMedia, $this> */
    public function primaryMedia(): HasOne
    {
        return $this->hasOne(PetProfileMedia::class)
            ->where('role', 'primary')
            ->where('status', 'active')
            ->whereNotNull('current_key');
    }

    /** @return HasOne<PetProfileMedia, $this> */
    public function latestRecoverableMedia(): HasOne
    {
        return $this->hasOne(PetProfileMedia::class)
            ->where('role', 'primary')
            ->whereIn('status', ['superseded', 'removed'])
            ->where('recoverable_until', '>', now())
            ->latestOfMany();
    }

    /** @return HasMany<AdoptionCase, $this> */
    public function adoptionCases(): HasMany
    {
        return $this->hasMany(AdoptionCase::class);
    }

    /** @return HasMany<SearchCase, $this> */
    public function searchCases(): HasMany
    {
        return $this->hasMany(SearchCase::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query
            ->where(function (Builder $visibility) use ($user): void {
                $visibility->where(function (Builder $public): void {
                    $public
                        ->whereIn('status', array_map(
                            static fn (PetProfileStatus $status): string => $status->value,
                            array_filter(
                                PetProfileStatus::cases(),
                                static fn (PetProfileStatus $status): bool => $status->isPubliclyEligible(),
                            ),
                        ))
                        ->where('visibility', 'public')
                        ->where('is_discoverable', true);
                });

                if ($user !== null) {
                    $visibility->orWhere(function (Builder $managed) use ($user): void {
                        $this->applyManagedBy($managed, $user);
                    });
                }
            });
    }

    public function scopeManagedBy(Builder $query, User $user): Builder
    {
        return $this->applyManagedBy($query, $user);
    }

    private function applyManagedBy(Builder $query, User $user): Builder
    {
        $at = now();

        return $query->where(function (Builder $managed) use ($at, $user): void {
            $managed
                ->where('user_id', $user->id)
                ->orWhereHas('managers', function ($managers) use ($at, $user): void {
                    $managers->where('user_id', $user->id);
                    PetProfileManager::constrainActiveAt($managers, $at);
                });
        });
    }
}
