<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'expert_profile_id', 'service_id', 'availability_slot_id',
        'client_id', 'reference', 'idempotency_key', 'client_key', 'client_name',
        'pet_key', 'pet_name', 'pet_species', 'pet_age_label', 'format',
        'starts_at', 'ends_at', 'timezone', 'location_label', 'status',
        'questionnaire', 'documents', 'amount', 'currency', 'payment_status',
        'terms_accepted', 'data_consent', 'recording_consent', 'confirmed_at',
        'cancelled_at', 'cancellation_reason', 'reschedule_proposed_at',
        'completed_at', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'expert_profile_id', 'service_id', 'availability_slot_id', 'client_id',
        'reference', 'idempotency_key', 'client_key', 'client_name', 'pet_key',
        'pet_name', 'pet_species', 'pet_age_label', 'format', 'starts_at',
        'ends_at', 'timezone', 'location_label', 'status', 'questionnaire',
        'documents', 'amount', 'currency', 'payment_status', 'terms_accepted',
        'data_consent', 'recording_consent', 'confirmed_at', 'cancelled_at',
        'cancellation_reason', 'reschedule_proposed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'questionnaire' => 'array',
            'documents' => 'array',
            'amount' => 'decimal:2',
            'terms_accepted' => 'boolean',
            'data_consent' => 'boolean',
            'recording_consent' => 'boolean',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reschedule_proposed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function documentGrants(): HasMany
    {
        return $this->hasMany(DocumentGrant::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function scopeForClient(Builder $query, string $clientKey): Builder
    {
        return $query->where('client_key', $clientKey);
    }
}
