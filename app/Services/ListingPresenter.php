<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Enums\ModerationStatus;
use App\Enums\ReservationStatus;
use App\Enums\ReviewStatus;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\ListingReview;
use App\Models\Order;
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
            ->inCondition($filters['condition'] ?? null)
            ->fromSellerType($filters['seller_type'] ?? null)
            ->withAvailability($filters['availability'] ?? null)
            ->withCount([
                'reviews as reviews_count' => fn (Builder $reviews): Builder => $reviews
                    ->where('status', ReviewStatus::Published->value),
            ])
            ->withAvg([
                'reviews as item_rating' => fn (Builder $reviews): Builder => $reviews
                    ->where('status', ReviewStatus::Published->value),
            ], 'item_rating')
            ->withExists([
                'engagements as is_saved' => fn (Builder $engagements): Builder => $engagements
                    ->where('user_key', $this->actor->key())
                    ->where('is_saved', true),
            ]);

        match ($filters['sort'] ?? 'newest') {
            'price-low' => $query->orderBy('is_free', 'desc')->orderBy('price')->latest('id'),
            'price-high' => $query->orderByDesc('price')->latest('id'),
            'popular' => $query->orderByDesc('view_count')->latest('published_at')->latest('id'),
            'verified' => $query->orderByDesc('is_verified_seller')->latest('published_at')->latest('id'),
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
            'conditions' => $this->taxonomy->conditions(),
            'seller_types' => $this->taxonomy->sellerTypes(),
            'availability_options' => $this->taxonomy->availabilityOptions(),
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
                    'request_kind', 'quantity', 'offered_price', 'message',
                    'exchange_method', 'proposed_at', 'rental_starts_at',
                    'rental_ends_at', 'questionnaire', 'terms_accepted',
                    'privacy_accepted', 'expires_at', 'responded_at',
                    'completed_at', 'created_at',
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
                    'request_kind', 'quantity', 'offered_price', 'message',
                    'exchange_method', 'proposed_at', 'rental_starts_at',
                    'rental_ends_at', 'questionnaire', 'terms_accepted',
                    'privacy_accepted', 'expires_at', 'responded_at',
                    'completed_at', 'created_at',
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

        $orders = Order::query()
            ->select([
                'id', 'listing_id', 'reservation_id', 'reference', 'buyer_key',
                'seller_key', 'status', 'payment_status', 'total_amount',
                'currency', 'created_at',
            ])
            ->where('listing_id', $listing->id)
            ->when(
                ! $canManage,
                fn (Builder $query): Builder => $query->where('buyer_key', $this->actor->key()),
            )
            ->latest('created_at')
            ->get()
            ->keyBy('reservation_id');

        $reviews = ListingReview::query()
            ->select([
                'id', 'listing_id', 'reviewer_name', 'is_verified_buyer',
                'item_rating', 'seller_rating', 'delivery_rating', 'body',
                'seller_reply', 'replied_at', 'created_at',
            ])
            ->where('listing_id', $listing->id)
            ->where('status', ReviewStatus::Published->value)
            ->latest('created_at')
            ->limit(8)
            ->get();

        $reviewSummary = Listing::query()
            ->select(['id'])
            ->withCount([
                'reviews as reviews_count' => fn (Builder $query): Builder => $query
                    ->where('status', ReviewStatus::Published->value),
            ])
            ->withAvg([
                'reviews as item_rating' => fn (Builder $query): Builder => $query
                    ->where('status', ReviewStatus::Published->value),
            ], 'item_rating')
            ->findOrFail($listing->id);

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
                && $listing->moderation_status === ModerationStatus::Approved
                && $myReservation === null,
            'reservations' => $reservations
                ->map(fn (Reservation $reservation): array => $this->reservationData(
                    $reservation,
                    $orders->get($reservation->id),
                    $listing,
                ))
                ->all(),
            'my_reservation' => $myReservation
                ? $this->reservationData($myReservation, $orders->get($myReservation->id), $listing)
                : null,
            'reviews' => $reviews->map(fn (ListingReview $review): array => [
                'reviewer_name' => $review->reviewer_name,
                'verified' => $review->is_verified_buyer,
                'item_rating' => $review->item_rating,
                'seller_rating' => $review->seller_rating,
                'delivery_rating' => $review->delivery_rating,
                'body' => $review->body,
                'seller_reply' => $review->seller_reply,
                'created_label' => $review->created_at?->diffForHumans(),
            ])->all(),
            'review_summary' => [
                'count' => (int) $reviewSummary->reviews_count,
                'rating' => $reviewSummary->item_rating !== null
                    ? number_format((float) $reviewSummary->item_rating, 1)
                    : null,
            ],
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
            'seller_types' => $this->taxonomy->sellerTypes(),
            'availability_options' => $this->taxonomy->availabilityOptions(),
            'age_groups' => $this->taxonomy->ageGroups(),
            'hygiene_statuses' => $this->taxonomy->hygieneStatuses(),
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
            'brand_model' => collect([$listing->brand, $listing->model])->filter()->join(' '),
            'quantity' => $listing->quantity,
            'availability' => $listing->availability,
            'availability_label' => $this->taxonomy->availabilityOptions()[$listing->availability]
                ?? Str::headline($listing->availability),
            'location_label' => collect([$listing->area, $listing->city])->filter()->join(', '),
            'species_labels' => collect($listing->species)
                ->map(fn (string $species): string => $this->taxonomy->species()[$species] ?? Str::headline($species))
                ->all(),
            'cover_url' => $listing->cover_url,
            'is_saved' => (bool) ($listing->is_saved ?? false),
            'business_name' => $listing->is_business ? $listing->business_name : null,
            'seller_type' => $listing->seller_type->value,
            'seller_type_label' => $listing->seller_type->label(),
            'seller_verified' => $listing->is_verified_seller,
            'reviews_count' => (int) ($listing->reviews_count ?? 0),
            'item_rating' => $listing->hasAttribute('item_rating')
                && $listing->item_rating !== null
                ? number_format((float) $listing->item_rating, 1)
                : null,
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
            'age_group_label' => $listing->age_group
                ? ($this->taxonomy->ageGroups()[$listing->age_group] ?? Str::headline($listing->age_group))
                : null,
            'material' => $listing->material,
            'attributes' => $listing->attributes ?? [],
            'defects' => $listing->defects,
            'hygiene_status' => $listing->hygiene_status
                ? ($this->taxonomy->hygieneStatuses()[$listing->hygiene_status] ?? Str::headline($listing->hygiene_status))
                : null,
            'sealed_package' => $listing->sealed_package,
            'delivery_labels' => collect($listing->delivery_options)
                ->map(fn (string $option): string => $this->taxonomy->deliveryOptions()[$option] ?? Str::headline($option))
                ->all(),
            'delivery_options' => $listing->delivery_options,
            'meetup_notes' => $listing->meetup_notes,
            'return_policy' => $listing->return_policy,
            'gallery' => $listing->gallery ?? [],
            'video_url' => $listing->video_url,
            'status' => $listing->status->value,
            'status_label' => $listing->status->label(),
            'safety_status' => Str::headline($listing->safety_status),
            'moderation_status' => $listing->moderation_status->value,
            'moderation_label' => $listing->moderation_status->label(),
            'risk_flags' => collect($listing->risk_flags ?? [])->map(fn (string $flag): string => Str::headline($flag))->all(),
            'contact_policy' => $listing->contact_policy,
            'request_label' => $listing->type->requestLabel(),
            'request_kind' => $listing->type->requestKind(),
            'view_count' => $listing->view_count,
            'published_at' => $listing->published_at?->format('M j, Y'),
            'completed_at' => $listing->completed_at?->format('M j, Y'),
        ];
    }

    /** @return array<string, mixed> */
    private function reservationData(Reservation $reservation, ?Order $order, Listing $listing): array
    {
        return [
            'id' => $reservation->id,
            'requester_name' => $reservation->requester_name,
            'requester_key' => $reservation->requester_key,
            'status' => $reservation->status->value,
            'status_label' => $reservation->status->label(),
            'request_kind' => Str::headline($reservation->request_kind),
            'quantity' => $reservation->quantity,
            'offered_price' => $reservation->offered_price !== null
                ? $listing->currency.' '.number_format((float) $reservation->offered_price, 2)
                : null,
            'message' => $reservation->message,
            'exchange_method' => $this->taxonomy->deliveryOptions()[$reservation->exchange_method]
                ?? Str::headline($reservation->exchange_method),
            'proposed_at' => $reservation->proposed_at?->format('M j · H:i'),
            'rental_starts_at' => $reservation->rental_starts_at?->format('M j, Y'),
            'rental_ends_at' => $reservation->rental_ends_at?->format('M j, Y'),
            'questionnaire' => $reservation->questionnaire ?? [],
            'expires_at' => $reservation->expires_at?->format('M j · H:i'),
            'created_label' => $reservation->created_at?->diffForHumans(),
            'order' => $order ? [
                'reference' => $order->reference,
                'status' => $order->status->label(),
                'payment_status' => $order->payment_status->label(),
                'total' => $order->currency.' '.number_format((float) $order->total_amount, 2),
                'url' => route('marketplace.orders.show', [$listing, $order]),
            ] : null,
        ];
    }

    private function priceLabel(Listing $listing): string
    {
        if ($listing->type->value === 'shelter-need') {
            return 'Help requested';
        }

        if ($listing->is_free || in_array($listing->type->value, ['adoption', 'free'], true)) {
            return 'Free';
        }

        if ($listing->type->value === 'exchange' && $listing->price === null) {
            return 'Exchange';
        }

        if ($listing->price === null) {
            return 'Ask owner';
        }

        $suffix = match ($listing->type->value) {
            'rental' => ' / day',
            'service' => ' / service',
            default => '',
        };

        return $listing->currency.' '.number_format((float) $listing->price, 2).$suffix;
    }
}
