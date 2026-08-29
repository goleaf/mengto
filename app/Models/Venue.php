<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VenueStatus;
use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $animal_capacity
 * @property int|null $human_capacity
 * @property int $id
 * @property int|null $organization_id
 * @property int $place_id
 * @property VenueStatus $status
 * @property string $timezone
 * @property-read Collection<int, VenueArea> $areas
 * @property-read Organization|null $organization
 * @property-read Place $place
 */
final class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'organization_id',
        'status',
        'timezone',
        'human_capacity',
        'animal_capacity',
        'species_capacities',
        'staff_to_participant_ratio',
        'operational_contact',
        'operational_rules',
        'confirmed_at',
        'information_expires_at',
    ];

    protected $hidden = ['operational_contact', 'operational_rules'];

    protected $attributes = ['status' => 'active'];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'human_capacity' => 'integer',
            'animal_capacity' => 'integer',
            'species_capacities' => 'array',
            'staff_to_participant_ratio' => 'integer',
            'operational_contact' => 'encrypted',
            'operational_rules' => 'encrypted:array',
            'confirmed_at' => 'immutable_datetime',
            'information_expires_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<VenueArea, $this> */
    public function areas(): HasMany
    {
        return $this->hasMany(VenueArea::class);
    }

    /** @return HasMany<ForumEventOccurrence, $this> */
    public function eventOccurrences(): HasMany
    {
        return $this->hasMany(ForumEventOccurrence::class);
    }

    /** @return HasMany<ForumEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumEvent::class);
    }
}
