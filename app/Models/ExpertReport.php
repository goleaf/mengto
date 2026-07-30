<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExpertReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read Booking|null $booking
 * @property int|null $booking_id
 * @property Carbon|null $created_at
 * @property string|null $details
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property int $id
 * @property string $priority
 * @property string $reason
 * @property string $reporter_key
 * @property-read Review|null $review
 * @property int|null $review_id
 * @property string $status
 * @property Carbon|null $updated_at
 */
class ExpertReport extends Model
{
    /** @use HasFactory<ExpertReportFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'booking_id', 'review_id', 'reporter_key',
        'reason', 'details', 'priority', 'status',
    ];

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<\App\Models\Booking, $this>*/
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<\App\Models\Review, $this>*/
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
