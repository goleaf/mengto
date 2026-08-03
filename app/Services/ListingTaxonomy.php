<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ListingType;
use App\Enums\SellerType;

class ListingTaxonomy
{
    /** @return array<string, string> */
    public function types(): array
    {
        return collect(ListingType::cases())
            ->mapWithKeys(fn (ListingType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function categories(): array
    {
        return $this->labels('categories', [
            'food',
            'walking-gear',
            'carriers-travel',
            'beds-home',
            'feeding',
            'grooming-care',
            'training-enrichment',
            'hygiene',
            'clothing',
            'electronics',
            'aquariums',
            'terrariums',
            'rehabilitation',
            'professional-service',
            'pet-service',
            'shelter-supplies',
            'adoption',
            'other',
        ]);
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return $this->labels('species', [
            'dog',
            'cat',
            'bird',
            'rabbit',
            'rodent',
            'reptile',
            'horse',
            'fish',
            'amphibian',
            'other',
        ]);
    }

    /** @return array<string, string> */
    public function conditions(): array
    {
        return $this->labels('conditions', [
            'new',
            'like-new',
            'good',
            'fair',
            'repair',
            'not-applicable',
        ]);
    }

    /** @return array<string, string> */
    public function sellerTypes(): array
    {
        return collect(SellerType::cases())
            ->mapWithKeys(fn (SellerType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function availabilityOptions(): array
    {
        return $this->labels('availability', [
            'in-stock',
            'low-stock',
            'made-to-order',
            'preorder',
            'available-for-rent',
        ]);
    }

    /** @return array<string, string> */
    public function ageGroups(): array
    {
        return $this->labels('age_groups', [
            'young',
            'adult',
            'senior',
            'all',
            'not-applicable',
        ]);
    }

    /** @return array<string, string> */
    public function hygieneStatuses(): array
    {
        return $this->labels('hygiene_statuses', [
            'new-sealed',
            'cleaned',
            'washed',
            'steam-cleaned',
            'needs-cleaning',
            'not-applicable',
        ]);
    }

    /** @return array<string, string> */
    public function deliveryOptions(): array
    {
        return $this->labels('delivery', [
            'meetup',
            'pickup',
            'shipping',
            'online',
            'courier',
            'parcel-locker',
            'shelter-delivery',
        ]);
    }

    /** @return array<string, string> */
    public function priceFilters(): array
    {
        return $this->labels('price_filters', [
            'any',
            'free',
            'under-25',
            'under-100',
        ]);
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return $this->labels('sort_options', [
            'newest',
            'price-low',
            'price-high',
            'popular',
            'verified',
        ]);
    }

    /** @return array<string, string> */
    public function disputeReasons(): array
    {
        return $this->labels('dispute_reasons', [
            'not-delivered',
            'not-as-described',
            'counterfeit',
            'damaged',
            'incomplete',
            'service-not-provided',
            'rental-not-provided',
            'duplicate-charge',
            'refund-missing',
            'fraud',
            'dangerous-product',
            'animal-welfare',
        ]);
    }

    /** @return array<string, string> */
    public function reportReasons(): array
    {
        return $this->labels('report_reasons', [
            'fraud',
            'illegal-sale',
            'animal-welfare',
            'counterfeit',
            'misleading',
            'personal-data',
            'duplicate',
            'other',
        ]);
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    private function labels(string $group, array $keys): array
    {
        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [
                $key => (string) __("marketplace.{$group}.{$key}"),
            ])
            ->all();
    }
}
