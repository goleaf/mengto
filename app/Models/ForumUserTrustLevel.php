<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumUserTrustLevelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumUserTrustLevel extends Model
{
    /** @use HasFactory<ForumUserTrustLevelFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_trust_level_id',
        'granted_by_user_id',
        'scope_type',
        'scope_key',
        'reason_code',
        'granted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ForumTrustLevel, $this> */
    public function level(): BelongsTo
    {
        return $this->belongsTo(ForumTrustLevel::class, 'forum_trust_level_id');
    }
}
