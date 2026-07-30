<?php

namespace App\Models;

use Database\Factories\SearchAlertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAlert extends Model
{
    /** @use HasFactory<SearchAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'kind', 'radius_km', 'region', 'channels',
        'audiences', 'status', 'recipient_count', 'message', 'sent_at',
        'stopped_at',
    ];

    protected $attributes = [
        'status' => 'queued',
        'recipient_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'audiences' => 'array',
            'sent_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }
}
