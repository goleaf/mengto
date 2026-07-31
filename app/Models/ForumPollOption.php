<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumPollOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $first_choice_count
 * @property int $forum_poll_id
 * @property int $id
 * @property string $label
 * @property int $position
 * @property int $selection_count
 * @property string $stable_key
 * @property-read ForumPoll $poll
 */
final class ForumPollOption extends Model
{
    /** @use HasFactory<ForumPollOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_poll_id',
        'stable_key',
        'label',
        'position',
        'selection_count',
        'first_choice_count',
    ];

    protected $attributes = [
        'selection_count' => 0,
        'first_choice_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'selection_count' => 'integer',
            'first_choice_count' => 'integer',
        ];
    }

    /** @return BelongsTo<ForumPoll, $this> */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(ForumPoll::class, 'forum_poll_id');
    }
}
