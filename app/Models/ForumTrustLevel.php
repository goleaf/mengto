<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTrustLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ForumTrustLevel extends Model
{
    /** @use HasFactory<ForumTrustLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'position',
        'is_professional',
        'is_moderation_role',
        'is_active',
        'criteria',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_professional' => 'boolean',
            'is_moderation_role' => 'boolean',
            'is_active' => 'boolean',
            'criteria' => 'array',
            'metadata' => 'array',
        ];
    }
}
