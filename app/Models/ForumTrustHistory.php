<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumTrustHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumTrustHistory extends Model
{
    /** @use HasFactory<ForumTrustHistoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'forum_trust_history';

    protected $fillable = [
        'user_id',
        'from_forum_trust_level_id',
        'to_forum_trust_level_id',
        'actor_user_id',
        'scope_type',
        'scope_key',
        'reason_code',
        'evidence',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ForumTrustLevel, $this> */
    public function fromLevel(): BelongsTo
    {
        return $this->belongsTo(ForumTrustLevel::class, 'from_forum_trust_level_id');
    }

    /** @return BelongsTo<ForumTrustLevel, $this> */
    public function toLevel(): BelongsTo
    {
        return $this->belongsTo(ForumTrustLevel::class, 'to_forum_trust_level_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
