<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property numeric-string|null $amount
 * @property-read AvailabilitySlot|null $availabilitySlot
 * @property int|null $availability_slot_id
 * @property string|null $cancellation_reason
 * @property Carbon|null $cancelled_at
 * @property-read User|null $client
 * @property int|null $client_id
 * @property string $client_key
 * @property string $client_name
 * @property Carbon|null $completed_at
 * @property Carbon|null $confirmed_at
 * @property-read Consultation|null $consultation
 * @property Carbon|null $created_at
 * @property string $currency
 * @property bool $data_consent
 * @property-read Collection<int, DocumentGrant> $documentGrants
 * @property array<array-key, mixed>|null $documents
 * @property Carbon $ends_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property string $format
 * @property int $id
 * @property string $idempotency_key
 * @property string|null $location_label
 * @property PaymentStatus $payment_status
 * @property string|null $pet_age_label
 * @property string|null $pet_key
 * @property string $pet_name
 * @property string $pet_species
 * @property array<array-key, mixed> $questionnaire
 * @property bool $recording_consent
 * @property string $reference
 * @property Carbon|null $reschedule_proposed_at
 * @property-read Review|null $review
 * @property-read Service|null $service
 * @property int $service_id
 * @property Carbon $starts_at
 * @property BookingStatus $status
 * @property bool $terms_accepted
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
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

    /** @return BelongsTo<\App\Models\AvailabilitySlot, $this>*/
    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySlot::class);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** @return HasOne<\App\Models\Consultation, $this>*/
    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    /** @return HasMany<\App\Models\DocumentGrant, $this>*/
    public function documentGrants(): HasMany
    {
        return $this->hasMany(DocumentGrant::class);
    }

    /** @return HasOne<\App\Models\Review, $this>*/
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function scopeForClient(Builder $query, string $clientKey): Builder
    {
        return $query->where('client_key', $clientKey);
    }
}
