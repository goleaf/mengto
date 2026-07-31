<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CommunityAnimalGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class CommunityAnimalGroup extends Model
{
    /** @use HasFactory<CommunityAnimalGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'position',
        'is_system_managed',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_system_managed' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsToMany<Taxon, $this> */
    public function taxa(): BelongsToMany
    {
        return $this->belongsToMany(Taxon::class, 'community_animal_group_taxon')
            ->withPivot(['position', 'includes_descendants'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position')->orderBy('id');
    }
}
