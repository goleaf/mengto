<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumEventRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $accessibility_information
 * @property int|null $capacity
 * @property string|null $exact_directions
 * @property int $forum_event_id
 * @property int $id
 * @property bool $is_online
 * @property bool $is_private
 * @property string $name
 * @property string|null $online_url
 * @property int $position
 * @property string|null $public_directions
 * @property string $stable_key
 */
final class ForumEventRoom extends Model
{
    /** @use HasFactory<ForumEventRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_event_id',
        'stable_key',
        'name',
        'public_directions',
        'exact_directions',
        'online_url',
        'capacity',
        'accessibility_information',
        'is_online',
        'is_private',
        'position',
    ];

    protected $hidden = ['exact_directions', 'online_url'];

    protected $attributes = [
        'is_online' => false,
        'is_private' => false,
        'position' => 0,
    ];

    protected function casts(): array
    {
        return [
            'exact_directions' => 'encrypted',
            'online_url' => 'encrypted',
            'capacity' => 'integer',
            'is_online' => 'boolean',
            'is_private' => 'boolean',
            'position' => 'integer',
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
