<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedSource;
use Database\Factories\PetProfileBreedOriginFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $approximate_share_percent
 * @property string $breed_name
 * @property PetBreedConfidence $confidence
 * @property int|null $domestic_classification_id
 * @property int $id
 * @property string $origin_key
 * @property int $pet_profile_id
 * @property int $position
 * @property PetBreedSource $source
 */
final class PetProfileBreedOrigin extends Model
{
    /** @use HasFactory<PetProfileBreedOriginFactory> */
    use HasFactory;

    protected $fillable = [
        'origin_key',
        'pet_profile_id',
        'domestic_classification_id',
        'breed_name',
        'confidence',
        'source',
        'approximate_share_percent',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => PetBreedConfidence::class,
            'source' => PetBreedSource::class,
            'approximate_share_percent' => 'integer',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<DomesticClassification, $this> */
    public function domesticClassification(): BelongsTo
    {
        return $this->belongsTo(DomesticClassification::class);
    }
}
