<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'service_id', 'booking_id', 'reviewer_key',
        'reviewer_name', 'is_verified_client', 'is_anonymous', 'rating',
        'communication_rating', 'clarity_rating', 'organization_rating',
        'price_transparency_rating', 'body', 'status', 'expert_reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'is_verified_client' => 'boolean',
            'is_anonymous' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Published->value);
    }
}
