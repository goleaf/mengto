<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Enums\SearchVolunteerStatus;
use App\Enums\SightingStatus;
use Database\Factories\SearchCaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property array<array-key, mixed>|null $accessories
 * @property string|null $active_key
 * @property string|null $age_label
 * @property-read Collection<int, SearchAlert> $alerts
 * @property bool $alerts_active
 * @property bool $animal_secured
 * @property Carbon|null $archived_at
 * @property string|null $approach_instructions
 * @property string|null $avoid_instructions
 * @property string|null $breed
 * @property string $city
 * @property Carbon|null $closed_at
 * @property string|null $closure_reason
 * @property string|null $coat
 * @property array<array-key, mixed>|null $contact_details
 * @property bool $contact_protected
 * @property string $contact_token
 * @property string|null $coordinator_key
 * @property string|null $coordinator_name
 * @property string $country
 * @property string|null $cover_url
 * @property Carbon|null $created_at
 * @property string $description
 * @property string|null $direction
 * @property string|null $distinctive_marks
 * @property array<array-key, mixed>|null $exact_location
 * @property Carbon|null $found_at
 * @property string|null $health_notice
 * @property string|null $hidden_marks
 * @property int $id
 * @property string $last_seen_area
 * @property Carbon $last_seen_at
 * @property Carbon|null $last_sighting_at
 * @property string|null $latest_update
 * @property string $microchip_status
 * @property ModerationStatus $moderation_status
 * @property int $notification_radius_km
 * @property int|null $owner_id
 * @property string $owner_initials
 * @property string $owner_key
 * @property string $owner_name
 * @property string $pet_name
 * @property string|null $pet_profile_key
 * @property array<array-key, mixed>|null $photos
 * @property string $primary_color
 * @property string $public_code
 * @property numeric-string|null $public_latitude
 * @property numeric-string|null $public_longitude
 * @property Carbon $reported_at
 * @property-read Collection<int, SearchReport> $reports
 * @property Carbon|null $returned_at
 * @property array<array-key, mixed>|null $risk_flags
 * @property-read Collection<int, SearchSector> $sectors
 * @property string|null $sex
 * @property-read Collection<int, Sighting> $sightings
 * @property string|null $size
 * @property string $slug
 * @property string $species
 * @property SearchStatus $status
 * @property string|null $temperament
 * @property-read Collection<int, SearchTask> $tasks
 * @property SearchCaseType $type
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SearchUpdate> $updates
 * @property int $view_count
 * @property string $visibility
 * @property bool $volunteer_join_open
 * @property-read Collection<int, SearchVolunteer> $volunteers
 */
class SearchCase extends Model
{
    /** @use HasFactory<SearchCaseFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'owner_name', 'owner_initials',
        'coordinator_key', 'coordinator_name', 'slug', 'public_code',
        'active_key', 'type', 'status', 'moderation_status', 'pet_profile_key',
        'pet_profile_id', 'taxon_id', 'domestic_classification_id',
        'duplicate_of_search_case_id', 'pet_name', 'species', 'breed', 'sex',
        'age_label', 'size',
        'primary_color', 'coat', 'distinctive_marks', 'hidden_marks',
        'description', 'health_notice', 'approach_instructions',
        'avoid_instructions', 'accessories', 'temperament', 'microchip_status',
        'last_seen_area', 'city', 'country', 'public_latitude',
        'public_longitude', 'exact_location', 'direction', 'last_seen_at',
        'reported_at', 'notification_radius_km', 'visibility', 'alerts_active',
        'volunteer_join_open', 'animal_secured', 'contact_protected',
        'contact_details', 'contact_token', 'reward_offered', 'reward_summary',
        'cover_url', 'photos', 'risk_flags', 'animal_snapshot',
        'requires_taxonomy_review', 'latest_update', 'last_sighting_at',
        'found_at', 'returned_at', 'reunited_confirmed_by_user_id',
        'reunited_at', 'closed_at', 'archived_at', 'closure_reason',
        'view_count', 'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'owner_name', 'owner_initials',
        'coordinator_key', 'coordinator_name', 'slug', 'public_code',
        'active_key', 'type', 'status', 'moderation_status', 'pet_profile_key',
        'pet_profile_id', 'taxon_id', 'domestic_classification_id',
        'duplicate_of_search_case_id', 'pet_name', 'species', 'breed', 'sex',
        'age_label', 'size',
        'primary_color', 'coat', 'distinctive_marks', 'hidden_marks',
        'description', 'health_notice', 'approach_instructions',
        'avoid_instructions', 'accessories', 'temperament', 'microchip_status',
        'last_seen_area', 'city', 'country', 'public_latitude',
        'public_longitude', 'exact_location', 'direction', 'last_seen_at',
        'reported_at', 'notification_radius_km', 'visibility', 'alerts_active',
        'volunteer_join_open', 'animal_secured', 'contact_protected',
        'contact_details', 'contact_token', 'reward_offered', 'reward_summary',
        'cover_url', 'photos', 'risk_flags', 'animal_snapshot',
        'requires_taxonomy_review', 'latest_update', 'last_sighting_at',
        'found_at', 'returned_at', 'reunited_confirmed_by_user_id',
        'reunited_at', 'closed_at', 'archived_at', 'closure_reason',
        'view_count', 'lock_version',
    ];

    protected $hidden = [
        'hidden_marks',
        'exact_location',
        'contact_details',
        'contact_token',
        'active_key',
        'animal_snapshot',
    ];

    protected $attributes = [
        'status' => 'active',
        'moderation_status' => 'approved',
        'country' => 'LT',
        'microchip_status' => 'unknown',
        'notification_radius_km' => 5,
        'visibility' => 'public',
        'alerts_active' => true,
        'volunteer_join_open' => true,
        'animal_secured' => false,
        'contact_protected' => true,
        'reward_offered' => false,
        'requires_taxonomy_review' => false,
        'lock_version' => 1,
        'archived_at' => null,
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('search-cases.directory.stats'));
        static::deleted(fn (): bool => Cache::forget('search-cases.directory.stats'));
    }

    protected function casts(): array
    {
        return [
            'type' => SearchCaseType::class,
            'status' => SearchStatus::class,
            'moderation_status' => ModerationStatus::class,
            'hidden_marks' => 'encrypted',
            'accessories' => 'array',
            'exact_location' => 'encrypted:array',
            'contact_details' => 'encrypted:array',
            'animal_snapshot' => 'encrypted:array',
            'photos' => 'array',
            'risk_flags' => 'array',
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'last_seen_at' => 'datetime',
            'reported_at' => 'datetime',
            'alerts_active' => 'boolean',
            'volunteer_join_open' => 'boolean',
            'animal_secured' => 'boolean',
            'contact_protected' => 'boolean',
            'reward_offered' => 'boolean',
            'requires_taxonomy_review' => 'boolean',
            'last_sighting_at' => 'datetime',
            'found_at' => 'datetime',
            'returned_at' => 'datetime',
            'reunited_at' => 'datetime',
            'closed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    /** @return HasMany<\App\Models\Sighting, $this>*/
    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class);
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

    /** @return BelongsTo<SearchCase, $this> */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_search_case_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reunitedConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reunited_confirmed_by_user_id');
    }

    /** @return HasMany<SearchCaseEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SearchCaseEvent::class);
    }

    /** @return HasMany<SearchContactRelay, $this> */
    public function contactRelays(): HasMany
    {
        return $this->hasMany(SearchContactRelay::class);
    }

    /** @return HasMany<\App\Models\SearchSector, $this>*/
    public function sectors(): HasMany
    {
        return $this->hasMany(SearchSector::class);
    }

    /** @return HasMany<\App\Models\SearchTask, $this>*/
    public function tasks(): HasMany
    {
        return $this->hasMany(SearchTask::class);
    }

    /** @return HasMany<\App\Models\SearchVolunteer, $this>*/
    public function volunteers(): HasMany
    {
        return $this->hasMany(SearchVolunteer::class);
    }

    /** @return HasMany<\App\Models\SearchUpdate, $this>*/
    public function updates(): HasMany
    {
        return $this->hasMany(SearchUpdate::class);
    }

    /** @return HasMany<\App\Models\SearchAlert, $this>*/
    public function alerts(): HasMany
    {
        return $this->hasMany(SearchAlert::class);
    }

    /** @return HasMany<\App\Models\SearchReport, $this>*/
    public function reports(): HasMany
    {
        return $this->hasMany(SearchReport::class);
    }

    /** @return HasMany<DeviceEvent, $this> */
    public function deviceEvents(): HasMany
    {
        return $this->hasMany(DeviceEvent::class);
    }

    /** @return HasMany<SearchCase, $this> */
    public function duplicates(): HasMany
    {
        return $this->hasMany(self::class, 'duplicate_of_search_case_id');
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select([
            'id', 'owner_key', 'owner_name', 'owner_initials', 'slug',
            'public_code', 'type', 'status', 'pet_profile_id', 'taxon_id',
            'domestic_classification_id', 'duplicate_of_search_case_id',
            'pet_name', 'species', 'breed',
            'size', 'primary_color', 'distinctive_marks', 'description',
            'health_notice', 'approach_instructions', 'last_seen_area', 'city',
            'public_latitude', 'public_longitude', 'direction', 'last_seen_at',
            'notification_radius_km', 'visibility', 'alerts_active',
            'animal_secured', 'contact_protected', 'reward_offered',
            'reward_summary', 'cover_url', 'photos', 'latest_update',
            'last_sighting_at', 'archived_at', 'view_count', 'created_at',
            'updated_at',
        ]);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->where('visibility', 'public')
            ->whereNull('archived_at');
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query
            ->where('alerts_active', true)
            ->whereIn('status', [
                SearchStatus::Active->value,
                SearchStatus::PossibleSighting->value,
                SearchStatus::PossibleFound->value,
                SearchStatus::Paused->value,
            ]);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $term = '%'.$search.'%';

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('pet_name', 'like', $term)
                ->orWhere('species', 'like', $term)
                ->orWhere('breed', 'like', $term)
                ->orWhere('primary_color', 'like', $term)
                ->orWhere('distinctive_marks', 'like', $term)
                ->orWhere('last_seen_area', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('public_code', 'like', $term);
        });
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return filled($type) ? $query->where('type', $type) : $query;
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        return filled($status) ? $query->where('status', $status) : $query;
    }

    public function scopeForSpecies(Builder $query, ?string $species): Builder
    {
        return filled($species) ? $query->where('species', $species) : $query;
    }

    public function scopeInCity(Builder $query, ?string $city): Builder
    {
        return filled($city) ? $query->where('city', $city) : $query;
    }

    public function isManagedBy(string $actorKey): bool
    {
        return in_array($actorKey, [$this->owner_key, $this->coordinator_key], true);
    }

    /** @return array{active: int, lost: int, found: int, sightings: int, volunteers: int} */
    public static function directoryStats(): array
    {
        return Cache::remember('search-cases.directory.stats', now()->addMinutes(2), fn (): array => [
            'active' => self::query()->publiclyVisible()->urgent()->count(),
            'lost' => self::query()->publiclyVisible()->urgent()
                ->where('type', SearchCaseType::Lost->value)
                ->count(),
            'found' => self::query()->publiclyVisible()
                ->where('type', SearchCaseType::Found->value)
                ->whereIn('status', [
                    SearchStatus::Active->value,
                    SearchStatus::Safe->value,
                    SearchStatus::IdentityConfirmed->value,
                ])
                ->count(),
            'sightings' => Sighting::query()
                ->whereIn('status', [
                    SightingStatus::Submitted->value,
                    SightingStatus::Confirmed->value,
                ])
                ->count(),
            'volunteers' => SearchVolunteer::query()
                ->where('status', SearchVolunteerStatus::Active->value)
                ->count(),
        ]);
    }
}
