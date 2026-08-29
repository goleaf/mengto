<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTrustLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return HasMany<ForumTrustHistory, $this> */
    public function transitionsTo(): HasMany
    {
        return $this->hasMany(ForumTrustHistory::class, 'to_forum_trust_level_id');
    }

    /** @return HasMany<ForumTrustHistory, $this> */
    public function transitionsFrom(): HasMany
    {
        return $this->hasMany(ForumTrustHistory::class, 'from_forum_trust_level_id');
    }

    /** @return HasMany<ForumUserTrustLevel, $this> */
    public function userTrustAssignments(): HasMany
    {
        return $this->hasMany(ForumUserTrustLevel::class);
    }
}
