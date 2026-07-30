<?php

namespace App\Models;

use Database\Factories\ListingReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingReport extends Model
{
    /** @use HasFactory<ListingReportFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id', 'reporter_id', 'reporter_key', 'reason', 'details',
        'priority', 'status',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
