<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumModerationActionDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumModerationActionDefinition extends Model
{
    /** @use HasFactory<ForumModerationActionDefinitionFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'translation_key',
        'is_restrictive',
        'is_appealable',
        'requires_end_at',
        'requires_senior_review',
        'is_active',
        'position',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_restrictive' => 'boolean',
            'is_appealable' => 'boolean',
            'requires_end_at' => 'boolean',
            'requires_senior_review' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
            'metadata' => 'array',
        ];
    }

    /** @return HasMany<ForumModerationAction, $this> */
    public function actions(): HasMany
    {
        return $this->hasMany(ForumModerationAction::class);
    }
}
