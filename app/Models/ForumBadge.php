<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumBadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ForumBadge extends Model
{
    /** @use HasFactory<ForumBadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name_translation_key',
        'description_translation_key',
        'criteria_version',
        'criteria',
        'revocation_rules',
        'requires_moderation_review',
        'expires',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'criteria_version' => 'integer',
            'criteria' => 'array',
            'revocation_rules' => 'array',
            'requires_moderation_review' => 'boolean',
            'expires' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<ForumUserBadge, $this> */
    public function userBadges(): HasMany
    {
        return $this->hasMany(ForumUserBadge::class);
    }
}
