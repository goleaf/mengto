<?php

namespace App\Models;

use Database\Factories\ExpertReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertReport extends Model
{
    /** @use HasFactory<ExpertReportFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'booking_id', 'review_id', 'reporter_key',
        'reason', 'details', 'priority', 'status',
    ];

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
