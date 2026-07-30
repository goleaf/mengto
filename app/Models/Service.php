<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
