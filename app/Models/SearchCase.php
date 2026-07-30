<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Enums\SearchVolunteerStatus;
use App\Enums\SightingStatus;
use Database\Factories\SearchCaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SearchCase extends Model
{
    /** @use HasFactory<SearchCaseFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'owner_name', 'owner_initials',
        'coordinator_key', 'coordinator_name', 'slug', 'public_code',
        'active_key', 'type', 'status', 'moderation_status', 'pet_profile_key',
        'pet_name', 'species', 'breed', 'sex', 'age_label', 'size',
        'primary_color', 'coat', 'distinctive_marks', 'hidden_marks',
        'description', 'health_notice', 'approach_instructions',
        'avoid_instructions', 'accessories', 'microchip_status',
        'last_seen_area', 'city', 'country', 'public_latitude',
        'public_longitude', 'exact_location', 'direction', 'last_seen_at',
        'reported_at', 'notification_radius_km', 'visibility', 'alerts_active',
        'volunteer_join_open', 'animal_secured', 'contact_protected',
        'contact_details', 'contact_token', 'cover_url', 'photos', 'risk_flags',
        'latest_update', 'last_sighting_at', 'found_at', 'returned_at',
        'closed_at', 'closure_reason', 'view_count', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'owner_name', 'owner_initials',
        'coordinator_key', 'coordinator_name', 'slug', 'public_code',
        'active_key', 'type', 'status', 'moderation_status', 'pet_profile_key',
        'pet_name', 'species', 'breed', 'sex', 'age_label', 'size',
        'primary_color', 'coat', 'distinctive_marks', 'hidden_marks',
        'description', 'health_notice', 'approach_instructions',
        'avoid_instructions', 'accessories', 'microchip_status',
        'last_seen_area', 'city', 'country', 'public_latitude',
        'public_longitude', 'exact_location', 'direction', 'last_seen_at',
        'reported_at', 'notification_radius_km', 'visibility', 'alerts_active',
        'volunteer_join_open', 'animal_secured', 'contact_protected',
        'contact_details', 'contact_token', 'cover_url', 'photos', 'risk_flags',
        'latest_update', 'last_sighting_at', 'found_at', 'returned_at',
        'closed_at', 'closure_reason', 'view_count',
    ];

    protected $hidden = [
        'hidden_marks',
        'exact_location',
        'contact_details',
        'contact_token',
        'active_key',
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
            'last_sighting_at' => 'datetime',
            'found_at' => 'datetime',
            'returned_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class);
    }

    public function sectors(): HasMany
    {
        return $this->hasMany(SearchSector::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SearchTask::class);
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(SearchVolunteer::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(SearchUpdate::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(SearchAlert::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SearchReport::class);
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select([
            'id', 'owner_key', 'owner_name', 'owner_initials', 'slug',
            'public_code', 'type', 'status', 'pet_name', 'species', 'breed',
            'size', 'primary_color', 'distinctive_marks', 'description',
            'health_notice', 'approach_instructions', 'last_seen_area', 'city',
            'public_latitude', 'public_longitude', 'direction', 'last_seen_at',
            'notification_radius_km', 'visibility', 'alerts_active',
            'animal_secured', 'contact_protected', 'cover_url', 'photos',
            'latest_update', 'last_sighting_at', 'view_count', 'created_at',
            'updated_at',
        ]);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('moderation_status', ModerationStatus::Approved->value)
            ->where('visibility', 'public');
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
