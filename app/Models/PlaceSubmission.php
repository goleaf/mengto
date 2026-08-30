<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceType;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceSubmissionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $submitter_user_id
 * @property int|null $canonical_organization_id
 * @property int|null $published_place_id
 * @property int|null $linked_place_id
 * @property int|null $reviewed_by_user_id
 * @property string $stable_key
 * @property string $idempotency_key
 * @property string $payload_fingerprint
 * @property PlaceSubmissionStatus $status
 * @property PlaceSubmissionResolution $resolution
 * @property PlaceSubmissionSource $source_kind
 * @property PlaceLocationPrecision $location_precision
 * @property PlaceType $place_type
 * @property string $name
 * @property string $normalized_name
 * @property string $catalog_category
 * @property string $public_region
 * @property bool $continued_as_distinct
 * @property int $lock_version
 * @property CarbonImmutable $consented_at
 * @property CarbonImmutable|null $observed_at
 * @property CarbonImmutable $submitted_at
 * @property CarbonImmutable|null $reviewed_at
 * @property CarbonImmutable|null $approved_at
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $rejected_at
 * @property CarbonImmutable|null $withdrawn_at
 * @property-read User $submitter
 * @property-read User|null $reviewer
 * @property-read Organization|null $canonicalOrganization
 * @property-read Place|null $publishedPlace
 * @property-read Place|null $linkedPlace
 * @property-read Collection<int, PlaceFact> $facts
 * @property-read Collection<int, PlaceFact> $publishedFacts
 * @property-read Collection<int, PlaceSubmissionRevision> $revisions
 * @property-read Collection<int, PlaceDuplicateCandidate> $duplicateCandidates
 * @property-read Collection<int, PlaceSubmissionEvent> $events
 */
final class PlaceSubmission extends Model
{
    /** @use HasFactory<PlaceSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'submitter_user_id',
        'canonical_organization_id',
        'published_place_id',
        'linked_place_id',
        'reviewed_by_user_id',
        'stable_key',
        'idempotency_key',
        'payload_fingerprint',
        'status',
        'resolution',
        'source_kind',
        'source_reference',
        'relationship_to_place',
        'location_precision',
        'locale',
        'name',
        'normalized_name',
        'catalog_category',
        'place_type',
        'summary',
        'public_region',
        'public_address',
        'normalized_address',
        'public_latitude',
        'public_longitude',
        'exact_address',
        'exact_latitude',
        'exact_longitude',
        'public_phone',
        'normalized_phone',
        'public_email',
        'normalized_email',
        'public_website',
        'normalized_website',
        'identity_hash',
        'submitted_facts',
        'consent_version',
        'consented_at',
        'observed_at',
        'audit_context',
        'continued_as_distinct',
        'lock_version',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'published_at',
        'rejected_at',
        'withdrawn_at',
    ];

    protected $hidden = [
        'idempotency_key',
        'payload_fingerprint',
        'normalized_name',
        'normalized_address',
        'normalized_phone',
        'normalized_email',
        'normalized_website',
        'identity_hash',
        'submitted_facts',
        'source_reference',
        'exact_address',
        'exact_latitude',
        'exact_longitude',
        'audit_context',
    ];

    protected $attributes = [
        'status' => 'submitted',
        'resolution' => 'none',
        'continued_as_distinct' => false,
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => PlaceSubmissionStatus::class,
            'resolution' => PlaceSubmissionResolution::class,
            'source_kind' => PlaceSubmissionSource::class,
            'location_precision' => PlaceLocationPrecision::class,
            'place_type' => PlaceType::class,
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'exact_address' => 'encrypted',
            'exact_latitude' => 'encrypted',
            'exact_longitude' => 'encrypted',
            'source_reference' => 'encrypted',
            'submitted_facts' => 'encrypted:array',
            'audit_context' => 'encrypted:array',
            'continued_as_distinct' => 'boolean',
            'lock_version' => 'integer',
            'consented_at' => 'immutable_datetime',
            'observed_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
            'withdrawn_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function canonicalOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'canonical_organization_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function publishedPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'published_place_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function linkedPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'linked_place_id');
    }

    /** @return HasMany<PlaceFact, $this> */
    public function facts(): HasMany
    {
        return $this->hasMany(PlaceFact::class)->whereNull('place_id');
    }

    /** @return HasMany<PlaceFact, $this> */
    public function publishedFacts(): HasMany
    {
        return $this->hasMany(PlaceFact::class)->whereNotNull('place_id');
    }

    /** @return HasMany<PlaceSubmissionRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(PlaceSubmissionRevision::class);
    }

    /** @return HasMany<PlaceDuplicateCandidate, $this> */
    public function duplicateCandidates(): HasMany
    {
        return $this->hasMany(PlaceDuplicateCandidate::class);
    }

    /** @return HasMany<PlaceSubmissionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceSubmissionEvent::class);
    }
}
