<?php

namespace App\Models;

use Database\Factories\SearchReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchReport extends Model
{
    /** @use HasFactory<SearchReportFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'sighting_id', 'reporter_id', 'reporter_key',
        'reason', 'details', 'priority', 'status',
    ];

    protected $attributes = [
        'priority' => 'normal',
        'status' => 'open',
    ];

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }
}
