<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $body
 * @property-read Booking|null $booking
 * @property int|null $booking_id
 * @property int|null $clarity_rating
 * @property int|null $communication_rating
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property string|null $expert_reply
 * @property int $id
 * @property bool $is_anonymous
 * @property bool $is_verified_client
 * @property int|null $organization_rating
 * @property int|null $price_transparency_rating
 * @property int $rating
 * @property Carbon|null $replied_at
 * @property string $reviewer_key
 * @property string $reviewer_name
 * @property-read Service|null $service
 * @property int|null $service_id
 * @property ReviewStatus $status
 * @property Carbon|null $updated_at
 */
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

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<\App\Models\Service, $this>*/
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<\App\Models\Booking, $this>*/
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Published->value);
    }
}
