<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupAnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $archived_at
 * @property int $author_user_id
 * @property string $body
 * @property CarbonImmutable|null $expires_at
 * @property int $forum_group_id
 * @property int $id
 * @property int $lock_version
 * @property string $publication_idempotency_key
 * @property CarbonImmutable $published_at
 * @property string $stable_key
 * @property string $title
 * @property-read User $author
 * @property-read ForumGroup $group
 */
final class ForumGroupAnnouncement extends Model
{
    /** @use HasFactory<ForumGroupAnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'author_user_id',
        'stable_key',
        'publication_idempotency_key',
        'title',
        'body',
        'published_at',
        'expires_at',
        'lock_version',
        'archived_at',
    ];

    protected $attributes = [
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /**
     * @param  Builder<ForumGroupAnnouncement>  $query
     * @return Builder<ForumGroupAnnouncement>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereNull('archived_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $expiry): void {
                $expiry
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
