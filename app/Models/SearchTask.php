<?php

namespace App\Models;

use App\Enums\SearchTaskStatus;
use Database\Factories\SearchTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(SearchSector::class, 'search_sector_id');
    }
}
