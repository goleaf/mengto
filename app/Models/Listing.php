<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\ModerationStatus;
use App\Enums\SellerType;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property string|null $age_group
 * @property string|null $area
 * @property array<array-key, mixed>|null $attributes
 * @property string $availability
 * @property string|null $brand
 * @property string|null $business_name
 * @property string $category
 * @property string $city
 * @property Carbon|null $completed_at
 * @property string|null $condition
 * @property string $contact_policy
 * @property string|null $cover_url
 * @property Carbon|null $created_at
 * @property string $currency
 * @property string|null $defects
 * @property array<array-key, mixed>|null $delivery_options
 * @property string $description
 * @property-read Collection<int, ListingEngagement> $engagements
 * @property string|null $exchange_preferences
 * @property Carbon|null $expires_at
 * @property array<array-key, mixed>|null $gallery
 * @property string|null $hygiene_status
 * @property int $id
 * @property bool $is_business
 * @property bool $is_free
 * @property bool $is_verified_seller
 * @property float|null $item_rating
 * @property string|null $material
 * @property string|null $meetup_notes
 * @property string|null $model
 * @property ModerationStatus $moderation_status
 * @property-read Collection<int, Order> $orders
 * @property-read User|null $owner
 * @property int|null $owner_id
 * @property string $owner_initials
 * @property string $owner_key
 * @property string $owner_name
 * @property string|null $pet_size
 * @property numeric-string|null $price
 * @property Carbon|null $published_at
 * @property int $quantity
 * @property-read Collection<int, ListingReport> $reports
 * @property-read Collection<int, Reservation> $reservations
 * @property Carbon|null $reserved_at
 * @property string|null $return_policy
 * @property-read Collection<int, ListingReview> $reviews
 * @property array<array-key, mixed>|null $risk_flags
 * @property string $safety_status
 * @property bool $sealed_package
 * @property SellerType $seller_type
 * @property string $slug
 * @property array<array-key, mixed>|null $species
 * @property ListingStatus $status
 * @property string $title
 * @property ListingType $type
 * @property Carbon|null $updated_at
 * @property string|null $video_url
 * @property int $view_count
 */
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'owner_name', 'owner_initials', 'slug',
        'type', 'category', 'brand', 'model', 'material', 'title', 'description',
        'condition', 'price', 'currency', 'is_free', 'quantity', 'availability',
        'exchange_preferences', 'species', 'pet_size', 'age_group', 'attributes',
        'defects', 'hygiene_status', 'sealed_package', 'city', 'area',
        'delivery_options', 'meetup_notes', 'return_policy', 'cover_url',
        'gallery', 'video_url', 'status', 'safety_status', 'moderation_status',
        'risk_flags', 'is_business', 'business_name', 'seller_type',
        'is_verified_seller', 'contact_policy', 'view_count', 'published_at',
        'reserved_at', 'completed_at', 'expires_at', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'owner_name', 'owner_initials', 'slug', 'type',
        'category', 'brand', 'model', 'material', 'title', 'description',
        'condition', 'price', 'currency', 'is_free', 'quantity', 'availability',
        'exchange_preferences', 'species', 'pet_size', 'age_group', 'attributes',
        'defects', 'hygiene_status', 'sealed_package', 'city', 'area',
        'delivery_options', 'meetup_notes', 'return_policy', 'cover_url',
        'gallery', 'video_url', 'status', 'safety_status', 'moderation_status',
        'risk_flags', 'is_business', 'business_name', 'seller_type',
        'is_verified_seller', 'contact_policy', 'view_count', 'published_at',
        'reserved_at', 'completed_at', 'expires_at',
    ];

    protected $attributes = [
        'quantity' => 1,
        'availability' => 'in-stock',
        'seller_type' => 'private',
        'is_verified_seller' => false,
        'moderation_status' => 'approved',
        'sealed_package' => false,
    ];

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget('listings.directory.stats'));
        static::deleted(fn (): bool => Cache::forget('listings.directory.stats'));
    }

    protected function casts(): array
    {
        return [
            'type' => ListingType::class,
            'status' => ListingStatus::class,
            'seller_type' => SellerType::class,
            'moderation_status' => ModerationStatus::class,
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_verified_seller' => 'boolean',
            'sealed_package' => 'boolean',
            'species' => 'array',
            'attributes' => 'array',
            'delivery_options' => 'array',
            'gallery' => 'array',
            'risk_flags' => 'array',
            'is_business' => 'boolean',
            'published_at' => 'datetime',
            'reserved_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
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

    /** @return HasMany<\App\Models\ListingEngagement, $this>*/
    public function engagements(): HasMany
    {
        return $this->hasMany(ListingEngagement::class);
    }

    /** @return HasMany<\App\Models\Reservation, $this>*/
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** @return HasMany<\App\Models\ListingReport, $this>*/
    public function reports(): HasMany
    {
        return $this->hasMany(ListingReport::class);
    }

    /** @return HasMany<\App\Models\Order, $this>*/
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<\App\Models\ListingReview, $this>*/
    public function reviews(): HasMany
    {
        return $this->hasMany(ListingReview::class);
    }

    /** @return HasOne<AdoptionCase, $this> */
    public function adoptionCase(): HasOne
    {
        return $this->hasOne(AdoptionCase::class);
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select([
            'id', 'owner_key', 'owner_name', 'owner_initials', 'slug', 'type',
            'category', 'brand', 'model', 'title', 'description', 'condition',
            'price', 'currency', 'is_free', 'quantity', 'availability',
            'exchange_preferences', 'species', 'pet_size', 'age_group', 'city',
            'area', 'delivery_options', 'cover_url', 'status', 'safety_status',
            'moderation_status', 'is_business', 'business_name', 'seller_type',
            'is_verified_seller', 'published_at', 'created_at',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ListingStatus::Published->value)
            ->where('moderation_status', ModerationStatus::Approved->value);
    }

    public function scopeWithAdoptionIdentity(Builder $query): Builder
    {
        return $query->with([
            'adoptionCase' => fn ($cases) => $cases->select([
                'id',
                'listing_id',
                'provider_identity_status',
                'provider_verified',
                'provider_verification_expires_at',
            ]),
        ]);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $term = '%'.$search.'%';

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('title', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('category', 'like', $term)
                ->orWhere('brand', 'like', $term)
                ->orWhere('model', 'like', $term)
                ->orWhere('material', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('business_name', 'like', $term);
        });
    }

    public function scopeForType(Builder $query, ?string $type): Builder
    {
        return filled($type) ? $query->where('type', $type) : $query;
    }

    public function scopeInCategory(Builder $query, ?string $category): Builder
    {
        return filled($category) ? $query->where('category', $category) : $query;
    }

    public function scopeInCity(Builder $query, ?string $city): Builder
    {
        return filled($city) ? $query->where('city', $city) : $query;
    }

    public function scopeForSpecies(Builder $query, ?string $species): Builder
    {
        return filled($species) ? $query->whereJsonContains('species', $species) : $query;
    }

    public function scopeWithDelivery(Builder $query, ?string $delivery): Builder
    {
        return filled($delivery) ? $query->whereJsonContains('delivery_options', $delivery) : $query;
    }

    public function scopeForPrice(Builder $query, ?string $price): Builder
    {
        return match ($price) {
            'free' => $query->where('is_free', true),
            'under-25' => $query->where('price', '<=', 25),
            'under-100' => $query->where('price', '<=', 100),
            default => $query,
        };
    }

    public function scopeInCondition(Builder $query, ?string $condition): Builder
    {
        return filled($condition) ? $query->where('condition', $condition) : $query;
    }

    public function scopeFromSellerType(Builder $query, ?string $sellerType): Builder
    {
        return filled($sellerType) ? $query->where('seller_type', $sellerType) : $query;
    }

    public function scopeWithAvailability(Builder $query, ?string $availability): Builder
    {
        return filled($availability) ? $query->where('availability', $availability) : $query;
    }

    /** @return array{available: int, adoption: int, free: int, rental: int, shelter: int, cities: int} */
    public static function directoryStats(): array
    {
        return Cache::remember('listings.directory.stats', now()->addMinutes(5), fn (): array => [
            'available' => self::query()->published()->count(),
            'adoption' => self::query()->published()->where('type', ListingType::Adoption->value)->count(),
            'free' => self::query()->published()->where('is_free', true)->count(),
            'rental' => self::query()->published()->where('type', ListingType::Rental->value)->count(),
            'shelter' => self::query()->published()->where('type', ListingType::ShelterNeed->value)->count(),
            'cities' => self::query()->published()->distinct()->count('city'),
        ]);
    }
}
