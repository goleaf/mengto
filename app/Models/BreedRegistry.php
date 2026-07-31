<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BreedRegistryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BreedRegistry extends Model
{
    /** @use HasFactory<BreedRegistryFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name',
        'jurisdiction',
        'source_url',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }

    /** @return HasMany<DomesticClassification, $this> */
    public function classifications(): HasMany
    {
        return $this->hasMany(DomesticClassification::class);
    }
}
