<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceSpeciesEligibility;
use Database\Factories\PlaceServiceOfferingTaxonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceServiceOfferingTaxon extends Model
{
    /** @use HasFactory<PlaceServiceOfferingTaxonFactory> */
    use HasFactory;

    protected $fillable = [
        'place_service_offering_id',
        'taxon_id',
        'eligibility',
        'includes_descendants',
    ];

    protected function casts(): array
    {
        return [
            'eligibility' => PlaceSpeciesEligibility::class,
            'includes_descendants' => 'boolean',
        ];
    }

    /** @return BelongsTo<PlaceServiceOffering, $this> */
    public function offering(): BelongsTo
    {
        return $this->belongsTo(PlaceServiceOffering::class, 'place_service_offering_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }
}
