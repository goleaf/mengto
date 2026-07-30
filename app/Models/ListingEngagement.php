<?php

namespace App\Models;

use Database\Factories\ListingEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingEngagement extends Model
{
    /** @use HasFactory<ListingEngagementFactory> */
    use HasFactory;

    protected $fillable = ['listing_id', 'user_key', 'is_saved', 'last_viewed_at'];

    protected function casts(): array
    {
        return [
            'is_saved' => 'boolean',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
