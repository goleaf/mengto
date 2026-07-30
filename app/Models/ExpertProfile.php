<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use Database\Factories\ExpertProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property bool $accepts_new_clients
 * @property array<array-key, mixed>|null $accessibility
 * @property array<array-key, mixed>|null $age_groups
 * @property string|null $approach
 * @property-read Collection<int, AvailabilitySlot> $availabilitySlots
 * @property string $availability_status
 * @property string|null $avatar_url
 * @property string $bio
 * @property-read Collection<int, Booking> $bookings
 * @property string|null $boundaries
 * @property string $city
 * @property-read Collection<int, Consultation> $consultations
 * @property bool $contact_verified
 * @property string $country
 * @property string|null $cover_url
 * @property Carbon|null $created_at
 * @property-read Collection<int, Credential> $credentials
 * @property string $currency
 * @property bool $education_verified
 * @property-read Collection<int, ExpertEngagement> $engagements
 * @property array<array-key, mixed> $formats
 * @property-read Collection<int, ForumAnswer> $forumAnswers
 * @property int $forum_answer_count
 * @property string $headline
 * @property int $id
 * @property bool $identity_verified
 * @property array<array-key, mixed> $languages
 * @property string|null $legal_name
 * @property bool $license_verified
 * @property array<array-key, mixed>|null $methods
 * @property Carbon|null $next_available_at
 * @property bool $offers_emergency_care
 * @property bool $organization_verified
 * @property-read User|null $owner
 * @property int|null $owner_id
 * @property string $owner_key
 * @property numeric-string|null $price_from
 * @property string $primary_type
 * @property array<array-key, mixed>|null $professional_interests
 * @property string $public_name
 * @property int $publication_count
 * @property-read Collection<int, Publication> $publications
 * @property bool $qualification_verified
 * @property-read Collection<int, ExpertReport> $reports
 * @property string|null $response_time
 * @property numeric-string $review_average
 * @property int $review_count
 * @property-read Collection<int, Review> $reviews
 * @property string|null $service_area
 * @property-read Collection<int, Service> $services
 * @property string $slug
 * @property array<array-key, mixed> $specializations
 * @property array<array-key, mixed> $species
 * @property ExpertProfileStatus $status
 * @property Carbon|null $updated_at
 * @property Carbon|null $verification_expires_at
 * @property VerificationStatus $verification_status
 * @property int $verified_review_count
 * @property bool $workplace_verified
 * @property array<array-key, mixed>|null $workplaces
 * @property int $years_experience
 */
class ExpertProfile extends Model
{
    /** @use HasFactory<ExpertProfileFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'slug', 'public_name', 'legal_name',
        'primary_type', 'headline', 'bio', 'approach', 'boundaries',
        'years_experience', 'country', 'city', 'service_area',
        'specializations', 'species', 'age_groups', 'languages', 'formats',
        'methods', 'workplaces', 'accessibility', 'professional_interests',
        'availability_status', 'response_time', 'accepts_new_clients',
        'offers_emergency_care', 'price_from', 'currency', 'status',
        'verification_status', 'identity_verified', 'education_verified',
        'qualification_verified', 'license_verified', 'workplace_verified',
        'organization_verified', 'contact_verified', 'verification_expires_at',
        'next_available_at', 'avatar_url', 'cover_url', 'review_average',
        'review_count', 'verified_review_count', 'forum_answer_count',
        'publication_count', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'slug', 'public_name', 'legal_name',
        'primary_type', 'headline', 'bio', 'approach', 'boundaries',
        'years_experience', 'country', 'city', 'service_area',
        'specializations', 'species', 'age_groups', 'languages', 'formats',
        'methods', 'workplaces', 'accessibility', 'professional_interests',
        'availability_status', 'response_time', 'accepts_new_clients',
        'offers_emergency_care', 'price_from', 'currency', 'status',
        'verification_status', 'identity_verified', 'education_verified',
        'qualification_verified', 'license_verified', 'workplace_verified',
        'organization_verified', 'contact_verified', 'verification_expires_at',
        'next_available_at', 'avatar_url', 'cover_url', 'review_average',
        'review_count', 'verified_review_count', 'forum_answer_count',
        'publication_count',
    ];

    protected $hidden = ['legal_name'];

    protected function casts(): array
    {
        return [
            'status' => ExpertProfileStatus::class,
            'verification_status' => VerificationStatus::class,
            'specializations' => 'array',
            'species' => 'array',
            'age_groups' => 'array',
            'languages' => 'array',
            'formats' => 'array',
            'methods' => 'array',
            'workplaces' => 'array',
            'accessibility' => 'array',
            'professional_interests' => 'array',
            'accepts_new_clients' => 'boolean',
            'offers_emergency_care' => 'boolean',
            'identity_verified' => 'boolean',
            'education_verified' => 'boolean',
            'qualification_verified' => 'boolean',
            'license_verified' => 'boolean',
            'workplace_verified' => 'boolean',
            'organization_verified' => 'boolean',
            'contact_verified' => 'boolean',
            'verification_expires_at' => 'datetime',
            'next_available_at' => 'datetime',
            'price_from' => 'decimal:2',
            'review_average' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<\App\Models\Credential, $this>*/
    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    /** @return HasMany<\App\Models\Service, $this>*/
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
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

    /** @return HasMany<\App\Models\Consultation, $this>*/
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /** @return HasMany<\App\Models\Publication, $this>*/
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /** @return HasMany<\App\Models\Review, $this>*/
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<\App\Models\ExpertEngagement, $this>*/
    public function engagements(): HasMany
    {
        return $this->hasMany(ExpertEngagement::class);
    }

    /** @return HasMany<\App\Models\ExpertReport, $this>*/
    public function reports(): HasMany
    {
        return $this->hasMany(ExpertReport::class);
    }

    /** @return HasMany<\App\Models\ForumAnswer, $this>*/
    public function forumAnswers(): HasMany
    {
        return $this->hasMany(ForumAnswer::class);
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select(self::ROUTE_COLUMNS);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ExpertProfileStatus::Published->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $needle = '%'.trim((string) $term).'%';

        return $query->where(function (Builder $search) use ($needle): void {
            $search
                ->where('public_name', 'like', $needle)
                ->orWhere('headline', 'like', $needle)
                ->orWhere('bio', 'like', $needle)
                ->orWhere('city', 'like', $needle)
                ->orWhere('specializations', 'like', $needle)
                ->orWhere('species', 'like', $needle);
        });
    }

    public function scopeForSpecies(Builder $query, ?string $species): Builder
    {
        return filled($species)
            ? $query->whereJsonContains('species', $species)
            : $query;
    }

    public function scopeForSpecialization(Builder $query, ?string $specialization): Builder
    {
        return filled($specialization)
            ? $query->whereJsonContains('specializations', $specialization)
            : $query;
    }

    public function scopeForFormat(Builder $query, ?string $format): Builder
    {
        return filled($format)
            ? $query->whereJsonContains('formats', $format)
            : $query;
    }
}
