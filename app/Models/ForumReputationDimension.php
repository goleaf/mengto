<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReputationDimensionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumReputationDimension extends Model
{
    /** @use HasFactory<ForumReputationDimensionFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'daily_actor_recipient_cap',
        'relationship_cap',
        'is_public_by_default',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'daily_actor_recipient_cap' => 'integer',
            'relationship_cap' => 'integer',
            'is_public_by_default' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return HasMany<ForumReputationEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(ForumReputationEvent::class);
    }
}
