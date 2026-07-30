<?php

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
        return [
            'food' => 'Food & treats',
            'walking-gear' => 'Walking gear',
            'carriers-travel' => 'Carriers & travel',
            'beds-home' => 'Beds & home',
            'feeding' => 'Feeding',
            'grooming-care' => 'Grooming & care',
            'training-enrichment' => 'Training & enrichment',
            'hygiene' => 'Hygiene',
            'clothing' => 'Clothing',
            'electronics' => 'GPS & smart devices',
            'aquariums' => 'Aquariums',
            'terrariums' => 'Terrariums',
            'rehabilitation' => 'Rehabilitation equipment',
            'professional-service' => 'Professional services',
            'pet-service' => 'Everyday pet services',
            'shelter-supplies' => 'Shelter supplies',
            'adoption' => 'Adoption',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return [
            'dog' => 'Dogs',
            'cat' => 'Cats',
            'bird' => 'Birds',
            'rabbit' => 'Rabbits',
            'rodent' => 'Rodents',
            'reptile' => 'Reptiles',
            'horse' => 'Horses',
            'fish' => 'Fish',
            'amphibian' => 'Amphibians',
            'other' => 'Other pets',
        ];
    }

    /** @return array<string, string> */
    public function conditions(): array
    {
        return [
            'new' => 'New',
            'like-new' => 'Like new',
            'good' => 'Good',
            'fair' => 'Fair',
            'repair' => 'Needs repair',
            'not-applicable' => 'Not applicable',
        ];
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
        return [
            'in-stock' => 'In stock',
            'low-stock' => 'Low stock',
            'made-to-order' => 'Made to order',
            'preorder' => 'Preorder',
            'available-for-rent' => 'Available for rent',
        ];
    }

    /** @return array<string, string> */
    public function ageGroups(): array
    {
        return [
            'young' => 'Young pet',
            'adult' => 'Adult pet',
            'senior' => 'Senior pet',
            'all' => 'Any age',
            'not-applicable' => 'Not applicable',
        ];
    }

    /** @return array<string, string> */
    public function hygieneStatuses(): array
    {
        return [
            'new-sealed' => 'New and sealed',
            'cleaned' => 'Cleaned',
            'washed' => 'Washed',
            'steam-cleaned' => 'Steam cleaned',
            'needs-cleaning' => 'Needs additional cleaning',
            'not-applicable' => 'Not applicable',
        ];
    }

    /** @return array<string, string> */
    public function deliveryOptions(): array
    {
        return [
            'meetup' => 'Safe public meetup',
            'pickup' => 'Pickup after confirmation',
            'shipping' => 'Shipping',
            'online' => 'Online delivery',
            'courier' => 'Courier delivery',
            'parcel-locker' => 'Parcel locker',
            'shelter-delivery' => 'Deliver to shelter',
        ];
    }

    /** @return array<string, string> */
    public function priceFilters(): array
    {
        return [
            'any' => 'Any price',
            'free' => 'Free',
            'under-25' => 'Under €25',
            'under-100' => 'Under €100',
        ];
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return [
            'newest' => 'Newest',
            'price-low' => 'Lowest price',
            'price-high' => 'Highest price',
            'popular' => 'Most viewed',
            'verified' => 'Verified sellers first',
        ];
    }

    /** @return array<string, string> */
    public function disputeReasons(): array
    {
        return [
            'not-delivered' => 'Item was not delivered',
            'not-as-described' => 'Not as described',
            'counterfeit' => 'Suspected counterfeit',
            'damaged' => 'Arrived damaged',
            'incomplete' => 'Incomplete set',
            'service-not-provided' => 'Service was not provided',
            'rental-not-provided' => 'Rental was not provided',
            'duplicate-charge' => 'Duplicate charge',
            'refund-missing' => 'Refund is missing',
            'fraud' => 'Suspected fraud',
            'dangerous-product' => 'Dangerous product or unsafe instructions',
            'animal-welfare' => 'Animal welfare concern',
        ];
    }

    /** @return array<string, string> */
    public function reportReasons(): array
    {
        return [
            'fraud' => 'Suspected fraud',
            'illegal-sale' => 'Illegal animal sale',
            'animal-welfare' => 'Animal welfare risk',
            'counterfeit' => 'Counterfeit item',
            'misleading' => 'Misleading information',
            'personal-data' => 'Personal data exposed',
            'duplicate' => 'Duplicate listing',
            'other' => 'Other concern',
        ];
    }
}
