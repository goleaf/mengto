<?php

namespace App\Models;

use Database\Factories\CareJournalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function entries(): HasMany
    {
        return $this->hasMany(CareEntry::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(CareRoutine::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CareTask::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(CareMedia::class);
    }

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
