<?php

namespace App\Services;

use App\Enums\ListingType;

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
            'walking-gear' => 'Walking gear',
            'carriers-travel' => 'Carriers & travel',
            'beds-home' => 'Beds & home',
            'feeding' => 'Feeding',
            'grooming-care' => 'Grooming & care',
            'training-enrichment' => 'Training & enrichment',
            'pet-service' => 'Pet services',
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
