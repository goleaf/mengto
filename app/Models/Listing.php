<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'owner_name', 'owner_initials', 'slug',
        'type', 'category', 'title', 'description', 'condition', 'price',
        'currency', 'is_free', 'exchange_preferences', 'species', 'pet_size',
        'city', 'area', 'delivery_options', 'meetup_notes', 'cover_url',
        'gallery', 'status', 'safety_status', 'is_business', 'business_name',
        'contact_policy', 'view_count', 'published_at', 'reserved_at',
        'completed_at', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'owner_name', 'owner_initials', 'slug', 'type',
        'category', 'title', 'description', 'condition', 'price', 'currency',
        'is_free', 'exchange_preferences', 'species', 'pet_size', 'city',
        'area', 'delivery_options', 'meetup_notes', 'cover_url', 'gallery',
        'status', 'safety_status', 'is_business', 'business_name',
        'contact_policy', 'view_count', 'published_at', 'reserved_at',
        'completed_at',
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
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'species' => 'array',
            'delivery_options' => 'array',
            'gallery' => 'array',
            'is_business' => 'boolean',
            'published_at' => 'datetime',
            'reserved_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function engagements(): HasMany
    {
        return $this->hasMany(ListingEngagement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ListingReport::class);
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select([
            'id', 'owner_key', 'owner_name', 'owner_initials', 'slug', 'type',
            'category', 'title', 'description', 'condition', 'price', 'currency',
            'is_free', 'exchange_preferences', 'species', 'pet_size', 'city',
            'area', 'delivery_options', 'cover_url', 'status', 'safety_status',
            'is_business', 'business_name', 'published_at', 'created_at',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published->value);
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

    /** @return array{available: int, adoption: int, free: int, cities: int} */
    public static function directoryStats(): array
    {
        return Cache::remember('listings.directory.stats', now()->addMinutes(5), fn (): array => [
            'available' => self::query()->published()->count(),
            'adoption' => self::query()->published()->where('type', ListingType::Adoption->value)->count(),
            'free' => self::query()->published()->where('is_free', true)->count(),
            'cities' => self::query()->published()->distinct()->count('city'),
        ]);
    }
}
