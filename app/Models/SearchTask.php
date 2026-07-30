<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SearchTaskStatus;
use Database\Factories\SearchTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string|null $assignee_key
 * @property string|null $assignee_name
 * @property array<array-key, mixed>|null $attachments
 * @property Carbon|null $claimed_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $description
 * @property Carbon|null $due_at
 * @property int $id
 * @property string|null $result
 * @property string $safety_level
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property int|null $search_sector_id
 * @property-read SearchSector|null $sector
 * @property Carbon|null $starts_at
 * @property SearchTaskStatus $status
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property int $version
 */
class SearchTask extends Model
{
    /** @use HasFactory<SearchTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'search_sector_id', 'created_by_key', 'assignee_key',
        'assignee_name', 'type', 'title', 'description', 'status',
        'safety_level', 'starts_at', 'due_at', 'claimed_at', 'completed_at',
        'result', 'attachments', 'version',
    ];

    protected $attributes = [
        'status' => 'open',
        'safety_level' => 'standard',
        'version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'status' => SearchTaskStatus::class,
            'starts_at' => 'datetime',
            'due_at' => 'datetime',
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return BelongsTo<\App\Models\SearchSector, $this>*/
    public function sector(): BelongsTo
    {
        return $this->belongsTo(SearchSector::class, 'search_sector_id');
    }
}
