<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTopicTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumTopicType extends Model
{
    /** @use HasFactory<ForumTopicTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'schema_version',
        'field_schema',
        'configuration',
        'moderation_level',
        'allows_accepted_answers',
        'allows_confirmation',
        'expires',
        'is_system_managed',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schema_version' => 'integer',
            'field_schema' => 'array',
            'configuration' => 'array',
            'allows_accepted_answers' => 'boolean',
            'allows_confirmation' => 'boolean',
            'expires' => 'boolean',
            'is_system_managed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<ForumTopic, $this> */
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
