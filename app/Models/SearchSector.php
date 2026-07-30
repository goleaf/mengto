<?php

namespace App\Models;

use App\Enums\SearchSectorStatus;
use Database\Factories\SearchSectorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SearchTask::class);
    }
}
