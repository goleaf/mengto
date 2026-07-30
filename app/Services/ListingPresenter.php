<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListingPresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly ListingTaxonomy $taxonomy,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function directory(array $filters): array
    {
        $query = Listing::query()
            ->forDirectory()
            ->published()
            ->search($filters['q'] ?? null)
            ->forType($filters['type'] ?? null)
            ->inCategory($filters['category'] ?? null)
            ->forSpecies($filters['species'] ?? null)
            ->inCity($filters['city'] ?? null)
            ->withDelivery($filters['delivery'] ?? null)
            ->forPrice($filters['price'] ?? null)
            ->withExists([
                'engagements as is_saved' => fn (Builder $engagements): Builder => $engagements
                    ->where('user_key', $this->actor->key())
                    ->where('is_saved', true),
            ]);

        match ($filters['sort'] ?? 'newest') {
            'price-low' => $query->orderBy('is_free', 'desc')->orderBy('price')->latest('id'),
            'price-high' => $query->orderByDesc('price')->latest('id'),
            'popular' => $query->orderByDesc('view_count')->latest('published_at')->latest('id'),
            default => $query->latest('published_at')->latest('id'),
        };

        $listings = $query->simplePaginate(12)->withQueryString();
        $listings->through(fn (Listing $listing): array => $this->card($listing));

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => 'Marketplace',
            'active_section' => 'marketplace',
            'listings' => $listings,
            'filters' => $filters,
            'types' => $this->taxonomy->types(),
            'categories' => $this->taxonomy->categories(),
            'species_options' => $this->taxonomy->species(),
            'delivery_options' => $this->taxonomy->deliveryOptions(),
            'price_options' => $this->taxonomy->priceFilters(),
            'sort_options' => $this->taxonomy->sortOptions(),
            'stats' => Listing::directoryStats(),
        ];
    }

    /** @return array<string, mixed> */
    public function listing(Listing $listing): array
    {
        $listing->increment('view_count');

        $engagement = ListingEngagement::query()->firstOrCreate(
            ['listing_id' => $listing->id, 'user_key' => $this->actor->key()],
            ['is_saved' => false, 'last_viewed_at' => now()],
        );
        $engagement->update(['last_viewed_at' => now()]);

        $canManage = $listing->owner_key === $this->actor->key();
        $reservations = $canManage
            ? Reservation::query()
                ->select([
                    'id', 'listing_id', 'requester_key', 'requester_name', 'status',
                    'message', 'exchange_method', 'proposed_at', 'expires_at',
                    'responded_at', 'completed_at', 'created_at',
                ])
                ->where('listing_id', $listing->id)
                ->whereIn('status', [
                    ReservationStatus::Requested->value,
                    ReservationStatus::Accepted->value,
                    ReservationStatus::Completed->value,
                ])
                ->latest('created_at')
                ->get()
            : collect();

        $myReservation = $canManage
            ? null
            : Reservation::query()
                ->select([
                    'id', 'listing_id', 'requester_key', 'requester_name', 'status',
                    'message', 'exchange_method', 'proposed_at', 'expires_at',
                    'responded_at', 'completed_at', 'created_at',
                ])
                ->where('listing_id', $listing->id)
                ->where('requester_key', $this->actor->key())
                ->whereIn('status', [
                    ReservationStatus::Requested->value,
                    ReservationStatus::Accepted->value,
                    ReservationStatus::Completed->value,
                ])
                ->latest('created_at')
                ->first();

        $related = Listing::query()
            ->forDirectory()
            ->published()
            ->whereKeyNot($listing->id)
            ->where(function (Builder $builder) use ($listing): void {
                $builder
                    ->where('category', $listing->category)
                    ->orWhere('type', $listing->type->value);
            })
            ->latest('published_at')
            ->limit(4)
            ->get()
            ->map(fn (Listing $relatedListing): array => $this->card($relatedListing))
            ->all();

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $listing->title.' · Marketplace',
            'active_section' => 'marketplace',
            'listing' => $this->detail($listing),
            'engagement' => ['is_saved' => $engagement->is_saved],
            'can_manage' => $canManage,
            'can_request' => ! $canManage
                && $listing->status === ListingStatus::Published
                && $myReservation === null,
            'reservations' => $reservations
                ->map(fn (Reservation $reservation): array => $this->reservationData($reservation))
                ->all(),
            'my_reservation' => $myReservation ? $this->reservationData($myReservation) : null,
            'related' => $related,
            'delivery_options' => $this->taxonomy->deliveryOptions(),
            'report_reasons' => $this->taxonomy->reportReasons(),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /** @return array<string, mixed> */
    public function editor(): array
    {
        return [
            'owner' => $this->profiles->owner(),
            'page_title' => 'Create marketplace listing',
            'active_section' => 'marketplace',
            'types' => $this->taxonomy->types(),
            'categories' => $this->taxonomy->categories(),
            'species_options' => $this->taxonomy->species(),
            'conditions' => $this->taxonomy->conditions(),
            'delivery_options' => $this->taxonomy->deliveryOptions(),
        ];
    }

    /** @return array<string, mixed> */
    private function card(Listing $listing): array
    {
        return [
            'slug' => $listing->slug,
            'title' => $listing->title,
            'excerpt' => Str::limit(strip_tags($listing->description), 138),
            'type' => $listing->type->value,
            'type_label' => $listing->type->label(),
            'type_icon' => $listing->type->icon(),
            'category_label' => $this->taxonomy->categories()[$listing->category] ?? Str::headline($listing->category),
            'price_label' => $this->priceLabel($listing),
            'condition_label' => Str::headline($listing->condition),
            'location_label' => collect([$listing->area, $listing->city])->filter()->join(', '),
            'species_labels' => collect($listing->species)
                ->map(fn (string $species): string => $this->taxonomy->species()[$species] ?? Str::headline($species))
                ->all(),
            'cover_url' => $listing->cover_url,
            'is_saved' => (bool) ($listing->is_saved ?? false),
            'business_name' => $listing->is_business ? $listing->business_name : null,
            'owner_name' => $listing->owner_name,
            'published_label' => $listing->published_at?->diffForHumans(),
        ];
    }

    /** @return array<string, mixed> */
    private function detail(Listing $listing): array
    {
        return [
            ...$this->card($listing),
            'id' => $listing->id,
            'description' => $listing->description,
            'owner_key' => $listing->owner_key,
            'owner_initials' => $listing->owner_initials,
            'exchange_preferences' => $listing->exchange_preferences,
            'pet_size_label' => $listing->pet_size ? Str::headline($listing->pet_size) : null,
            'delivery_labels' => collect($listing->delivery_options)
                ->map(fn (string $option): string => $this->taxonomy->deliveryOptions()[$option] ?? Str::headline($option))
                ->all(),
            'delivery_options' => $listing->delivery_options,
            'meetup_notes' => $listing->meetup_notes,
            'gallery' => $listing->gallery ?? [],
            'status' => $listing->status->value,
            'status_label' => $listing->status->label(),
            'safety_status' => Str::headline($listing->safety_status),
            'contact_policy' => $listing->contact_policy,
            'view_count' => $listing->view_count,
            'published_at' => $listing->published_at?->format('M j, Y'),
            'completed_at' => $listing->completed_at?->format('M j, Y'),
        ];
    }

    /** @return array<string, mixed> */
    private function reservationData(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'requester_name' => $reservation->requester_name,
            'requester_key' => $reservation->requester_key,
            'status' => $reservation->status->value,
            'status_label' => $reservation->status->label(),
            'message' => $reservation->message,
            'exchange_method' => $this->taxonomy->deliveryOptions()[$reservation->exchange_method]
                ?? Str::headline($reservation->exchange_method),
            'proposed_at' => $reservation->proposed_at?->format('M j · H:i'),
            'expires_at' => $reservation->expires_at?->format('M j · H:i'),
            'created_label' => $reservation->created_at?->diffForHumans(),
        ];
    }

    private function priceLabel(Listing $listing): string
    {
        if ($listing->is_free || $listing->type->value === 'adoption') {
            return 'Free';
        }

        if ($listing->type->value === 'exchange' && $listing->price === null) {
            return 'Exchange';
        }

        if ($listing->price === null) {
            return 'Ask owner';
        }

        return '€'.number_format((float) $listing->price, 2);
    }
}
