<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ListingReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property string|null $details
 * @property int $id
 * @property-read Listing|null $listing
 * @property int $listing_id
 * @property string $priority
 * @property string $reason
 * @property-read User|null $reporter
 * @property int|null $reporter_id
 * @property string $reporter_key
 * @property string $status
 * @property Carbon|null $updated_at
 */
class ListingReport extends Model
{
    /** @use HasFactory<ListingReportFactory> */
    use HasFactory;

    protected $fillable = [
        'listing_id', 'reporter_id', 'reporter_key', 'reason', 'details',
        'priority', 'status',
    ];

    /** @return BelongsTo<\App\Models\Listing, $this>*/
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
