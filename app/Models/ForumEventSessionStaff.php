<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventSessionRole;
use Database\Factories\ForumEventSessionStaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $forum_event_session_id
 * @property int $id
 * @property bool $is_public
 * @property ForumEventSessionRole $role
 * @property int $user_id
 * @property-read ForumEventSession $session
 * @property-read User $user
 */
final class ForumEventSessionStaff extends Model
{
    /** @use HasFactory<ForumEventSessionStaffFactory> */
    use HasFactory;

    protected $table = 'forum_event_session_staff';

    protected $fillable = [
        'forum_event_session_id',
        'user_id',
        'role',
        'is_public',
    ];

    protected $attributes = ['is_public' => true];

    protected function casts(): array
    {
        return [
            'role' => ForumEventSessionRole::class,
            'is_public' => 'boolean',
        ];
    }

    /** @return BelongsTo<ForumEventSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ForumEventSession::class, 'forum_event_session_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
