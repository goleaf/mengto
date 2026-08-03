<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumEventTrackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $description
 * @property int $forum_event_id
 * @property int $id
 * @property bool $is_public
 * @property string $name
 * @property int $position
 * @property string $stable_key
 */
final class ForumEventTrack extends Model
{
    /** @use HasFactory<ForumEventTrackFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'stable_key',
        'name',
        'description',
        'position',
        'is_public',
    ];

    protected $attributes = [
        'position' => 0,
        'is_public' => true,
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return HasMany<ForumEventSession, $this> */
    public function sessions(): HasMany
    {
        return $this->hasMany(ForumEventSession::class);
    }
}
