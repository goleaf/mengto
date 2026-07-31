<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumJournalMeasurementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property-read ForumJournalEntry|null $entry
 * @property int $forum_journal_entry_id
 * @property int $id
 * @property string $metric_key
 * @property numeric-string $numeric_value
 * @property int $position
 * @property string $unit
 * @property Carbon|null $updated_at
 */
final class ForumJournalMeasurement extends Model
{
    /** @use HasFactory<ForumJournalMeasurementFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_journal_entry_id',
        'metric_key',
        'numeric_value',
        'unit',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:4',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<ForumJournalEntry, $this> */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(ForumJournalEntry::class, 'forum_journal_entry_id');
    }
}
