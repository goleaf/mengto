<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdoptionCaseStatus;
use App\Enums\AdoptionProviderIdentityStatus;
use App\Enums\AdoptionProviderType;
use Database\Factories\AdoptionCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $adoption_fee_minor
 * @property string|null $age_description
 * @property string $animal_name
 * @property Carbon|null $archived_at
 * @property string|null $behavior_summary
 * @property string $case_number
 * @property Carbon|null $closed_at
 * @property string|null $compatibility_summary
 * @property string $currency
 * @property int|null $domestic_classification_id
 * @property string|null $fee_explanation
 * @property string|null $health_summary
 * @property int $id
 * @property int $listing_id
 * @property int $lock_version
 * @property string $microchip_status
 * @property int|null $pet_profile_id
 * @property string $privacy_level
 * @property AdoptionProviderType $provider_type
 * @property bool $provider_verified
 * @property Carbon|null $provider_verification_expires_at
 * @property Carbon|null $provider_verified_at
 * @property int|null $provider_credential_id
 * @property int|null $provider_expert_profile_id
 * @property AdoptionProviderIdentityStatus $provider_identity_status
 * @property string $public_location
 * @property Carbon|null $published_at
 * @property string|null $sex
 * @property string|null $special_requirements
 * @property AdoptionCaseStatus $status
 * @property string $sterilization_status
 * @property int|null $taxon_id
 * @property array<int, string>|null $transport_options
 * @property string $vaccination_status
 * @property-read Listing $listing
 */
final class AdoptionCase extends Model
{
    /** @use HasFactory<AdoptionCaseFactory> */
    use HasFactory;

    protected $attributes = [
        'provider_verified' => false,
        'provider_identity_status' => 'unverified',
        'status' => 'draft',
        'sterilization_status' => 'unknown',
        'vaccination_status' => 'unknown',
        'microchip_status' => 'unknown',
        'adoption_fee_minor' => 0,
        'currency' => 'EUR',
        'privacy_level' => 'approximate-location',
        'lock_version' => 1,
    ];

    protected $fillable = [
        'listing_id',
        'pet_profile_id',
        'taxon_id',
        'domestic_classification_id',
        'case_number',
        'provider_type',
        'provider_expert_profile_id',
        'provider_credential_id',
        'provider_identity_status',
        'provider_verified',
        'provider_verified_at',
        'provider_verification_expires_at',
        'status',
        'animal_name',
        'age_description',
        'sex',
        'sterilization_status',
        'vaccination_status',
        'microchip_status',
        'public_location',
        'health_summary',
        'behavior_summary',
        'compatibility_summary',
        'special_requirements',
        'adoption_fee_minor',
        'currency',
        'fee_explanation',
        'transport_options',
        'privacy_level',
        'lock_version',
        'published_at',
        'closed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_type' => AdoptionProviderType::class,
            'provider_identity_status' => AdoptionProviderIdentityStatus::class,
            'provider_verified' => 'boolean',
            'provider_verified_at' => 'immutable_datetime',
            'provider_verification_expires_at' => 'immutable_datetime',
            'status' => AdoptionCaseStatus::class,
            'adoption_fee_minor' => 'integer',
            'transport_options' => 'array',
            'lock_version' => 'integer',
            'published_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Listing, $this> */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class);
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<DomesticClassification, $this> */
    public function domesticClassification(): BelongsTo
    {
        return $this->belongsTo(DomesticClassification::class);
    }

    /** @return BelongsTo<ExpertProfile, $this> */
    public function providerExpertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class, 'provider_expert_profile_id');
    }

    /** @return BelongsTo<Credential, $this> */
    public function providerCredential(): BelongsTo
    {
        return $this->belongsTo(Credential::class, 'provider_credential_id');
    }

    public function effectiveProviderIdentityStatus(): AdoptionProviderIdentityStatus
    {
        if (
            $this->provider_identity_status === AdoptionProviderIdentityStatus::Verified
            && $this->provider_verification_expires_at?->isPast()
        ) {
            return AdoptionProviderIdentityStatus::Expired;
        }

        return $this->provider_identity_status;
    }

    /** @return HasMany<AdoptionApplication, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    /** @return HasMany<AdoptionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AdoptionEvent::class);
    }

    /** @return MorphMany<ForumReport, $this> */
    public function subjectReports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }
}
