<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, AvailabilitySlot> $availabilitySlots
 * @property-read Collection<int, Booking> $bookings
 * @property string|null $cancellation_policy
 * @property int $capacity
 * @property Carbon|null $created_at
 * @property string $currency
 * @property string $description
 * @property int $duration_minutes
 * @property array<array-key, mixed>|null $excludes
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property int $follow_up_days
 * @property string $format
 * @property int $id
 * @property array<array-key, mixed>|null $includes
 * @property string $name
 * @property array<array-key, mixed>|null $preparation
 * @property numeric-string|null $price
 * @property string $pricing_model
 * @property bool $requires_approval
 * @property bool $requires_payment
 * @property-read Collection<int, Review> $reviews
 * @property string $slug
 * @property string $status
 * @property string $type
 * @property Carbon|null $updated_at
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'slug', 'name', 'type', 'format', 'description',
        'duration_minutes', 'price', 'currency', 'pricing_model', 'includes',
        'excludes', 'preparation', 'cancellation_policy', 'follow_up_days',
        'requires_payment', 'requires_approval', 'capacity', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'includes' => 'array',
            'excludes' => 'array',
            'preparation' => 'array',
            'requires_payment' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return HasMany<\App\Models\AvailabilitySlot, $this>*/
    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    /** @return HasMany<\App\Models\Booking, $this>*/
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** @return HasMany<\App\Models\Review, $this>*/
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
