<?php

namespace App\Models;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use Database\Factories\ExpertProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(ExpertEngagement::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ExpertReport::class);
    }

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
