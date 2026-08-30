<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceServiceAccessMode;
use App\Enums\PlaceServiceAvailability;
use App\Enums\PlaceVerificationStatus;
use Database\Factories\PlaceServiceOfferingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlaceServiceOffering extends Model
{
    /** @use HasFactory<PlaceServiceOfferingFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'place_id',
        'place_service_definition_id',
        'availability',
        'access_mode',
        'verification_status',
        'verification_source',
        'observed_at',
        'verified_at',
        'fresh_until',
        'position',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'availability' => PlaceServiceAvailability::class,
            'access_mode' => PlaceServiceAccessMode::class,
            'verification_status' => PlaceVerificationStatus::class,
            'observed_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'fresh_until' => 'immutable_datetime',
            'position' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<PlaceServiceDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(PlaceServiceDefinition::class, 'place_service_definition_id');
    }

    /** @return HasMany<PlaceServiceOfferingTaxon, $this> */
    public function taxonEligibilities(): HasMany
    {
        return $this->hasMany(PlaceServiceOfferingTaxon::class)
            ->orderBy('taxon_id')
            ->orderBy('id');
    }
}
