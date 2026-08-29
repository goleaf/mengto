<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\VenueAreaType;
use Database\Factories\VenueAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class VenueArea extends Model
{
    /** @use HasFactory<VenueAreaFactory> */
    use HasFactory;

    protected $fillable = [
        'venue_id', 'stable_key', 'name', 'type', 'is_public', 'human_capacity',
        'animal_capacity', 'species_capacities', 'accessibility_status',
        'accessibility_facts', 'private_instructions',
    ];

    protected $hidden = ['private_instructions'];

    protected $attributes = ['is_public' => true, 'accessibility_status' => 'not_assessed'];

    protected function casts(): array
    {
        return [
            'type' => VenueAreaType::class,
            'is_public' => 'boolean',
            'human_capacity' => 'integer',
            'animal_capacity' => 'integer',
            'species_capacities' => 'array',
            'accessibility_status' => PlaceAccessibilityStatus::class,
            'accessibility_facts' => 'array',
            'private_instructions' => 'encrypted',
        ];
    }

    /** @return BelongsTo<Venue, $this> */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /** @return HasMany<ForumEventRoom, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(ForumEventRoom::class);
    }
}
