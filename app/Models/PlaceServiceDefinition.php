<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceServiceDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlaceServiceDefinition extends Model
{
    /** @use HasFactory<PlaceServiceDefinitionFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'service_domain',
        'is_emergency_capability',
        'is_active',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_emergency_capability' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** @return HasMany<PlaceServiceOffering, $this> */
    public function offerings(): HasMany
    {
        return $this->hasMany(PlaceServiceOffering::class);
    }
}
