<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SearchSectorStatus;
use Database\Factories\SearchSectorFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string|null $access_notes
 * @property Carbon|null $checked_at
 * @property string|null $checked_by_key
 * @property string $code
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $label
 * @property array<array-key, mixed>|null $map_bounds
 * @property int $priority
 * @property string|null $risk_notes
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property SearchSectorStatus $status
 * @property-read Collection<int, SearchTask> $tasks
 * @property Carbon|null $updated_at
 */
class SearchSector extends Model
{
    /** @use HasFactory<SearchSectorFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'code', 'label', 'status', 'priority', 'map_bounds',
        'risk_notes', 'access_notes', 'checked_by_key', 'checked_at',
    ];

    protected $attributes = [
        'status' => 'unchecked',
        'priority' => 2,
    ];

    protected function casts(): array
    {
        return [
            'status' => SearchSectorStatus::class,
            'map_bounds' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return HasMany<\App\Models\SearchTask, $this>*/
    public function tasks(): HasMany
    {
        return $this->hasMany(SearchTask::class);
    }
}
