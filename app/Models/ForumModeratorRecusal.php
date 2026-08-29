<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumModeratorRecusalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ForumModeratorRecusal extends Model
{
    /** @use HasFactory<ForumModeratorRecusalFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'forum_moderation_case_id',
        'moderator_user_id',
        'reason_code',
        'private_note',
        'created_at',
    ];

    protected $hidden = ['private_note'];

    /** @return BelongsTo<ForumModerationCase, $this> */
    public function moderationCase(): BelongsTo
    {
        return $this->belongsTo(ForumModerationCase::class, 'forum_moderation_case_id');
    }

    /** @return BelongsTo<User, $this> */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_user_id');
    }
}
