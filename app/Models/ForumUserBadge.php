<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumUserBadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumUserBadge extends Model
{
    /** @use HasFactory<ForumUserBadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_badge_id',
        'granted_by_user_id',
        'scope_key',
        'status',
        'reason_code',
        'is_public',
        'granted_at',
        'expires_at',
        'revoked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'granted_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ForumBadge, $this> */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(ForumBadge::class, 'forum_badge_id');
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }
}
