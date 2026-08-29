<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CareJournalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, CareAccessGrant> $accessGrants
 * @property string|null $breed
 * @property Carbon|null $created_at
 * @property string|null $current_caregiver_key
 * @property string|null $current_caregiver_name
 * @property-read Collection<int, CareEntry> $entries
 * @property int $id
 * @property string|null $image_url
 * @property Carbon|null $last_feeding_at
 * @property Carbon|null $last_toilet_at
 * @property Carbon|null $last_walk_at
 * @property Carbon|null $last_water_at
 * @property int $lock_version
 * @property-read Collection<int, CareMedia> $media
 * @property int $open_tasks_count
 * @property int $overdue_tasks_count
 * @property int|null $owner_id
 * @property string $owner_key
 * @property string $pet_name
 * @property string $pet_profile_key
 * @property string $privacy
 * @property-read Collection<int, CareRoutine> $routines
 * @property string $slug
 * @property string $species
 * @property string $status
 * @property-read Collection<int, CareTask> $tasks
 * @property string $timezone
 * @property int $today_entries_count
 * @property int $unusual_entries_count
 * @property Carbon|null $updated_at
 */
class CareJournal extends Model
{
    /** @use HasFactory<CareJournalFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'image_url', 'privacy', 'timezone',
        'current_caregiver_key', 'current_caregiver_name', 'status',
        'last_feeding_at', 'last_water_at', 'last_walk_at', 'last_toilet_at',
        'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
        'species', 'breed', 'image_url', 'privacy', 'timezone',
        'current_caregiver_key', 'current_caregiver_name', 'status',
        'last_feeding_at', 'last_water_at', 'last_walk_at', 'last_toilet_at',
        'lock_version',
    ];

    protected $attributes = [
        'privacy' => 'private',
        'timezone' => 'Europe/Vilnius',
        'status' => 'active',
        'lock_version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'last_feeding_at' => 'datetime',
            'last_water_at' => 'datetime',
            'last_walk_at' => 'datetime',
            'last_toilet_at' => 'datetime',
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

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<\App\Models\CareEntry, $this>*/
    public function entries(): HasMany
    {
        return $this->hasMany(CareEntry::class);
    }

    /** @return HasMany<\App\Models\CareRoutine, $this>*/
    public function routines(): HasMany
    {
        return $this->hasMany(CareRoutine::class);
    }

    /** @return HasMany<\App\Models\CareTask, $this>*/
    public function tasks(): HasMany
    {
        return $this->hasMany(CareTask::class);
    }

    /** @return HasMany<\App\Models\CareMedia, $this>*/
    public function media(): HasMany
    {
        return $this->hasMany(CareMedia::class);
    }

    /** @return HasMany<\App\Models\CareAccessGrant, $this>*/
    public function accessGrants(): HasMany
    {
        return $this->hasMany(CareAccessGrant::class);
    }

    public function scopeForOwnerDirectory(Builder $query, string $ownerKey): Builder
    {
        return $query
            ->select([
                'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                'species', 'breed', 'image_url', 'privacy', 'timezone',
                'current_caregiver_name', 'status', 'last_feeding_at',
                'last_water_at', 'last_walk_at', 'last_toilet_at', 'updated_at',
            ])
            ->where('owner_key', $ownerKey)
            ->where('status', 'active');
    }

    public function isOwnedBy(string $actorKey): bool
    {
        return hash_equals($this->owner_key, $actorKey);
    }
}
