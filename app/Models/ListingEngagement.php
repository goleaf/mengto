<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ListingEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $id
 * @property bool $is_saved
 * @property Carbon|null $last_viewed_at
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property Carbon|null $updated_at
 * @property string $user_key
 */
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

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}
