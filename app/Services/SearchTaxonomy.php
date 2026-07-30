<?php

namespace App\Services;

use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;

class SearchTaxonomy
{
    /** @return array<string, string> */
    public function types(): array
    {
        return collect(SearchCaseType::cases())
            ->mapWithKeys(fn (SearchCaseType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return collect(SearchStatus::cases())
            ->mapWithKeys(fn (SearchStatus $status): array => [$status->value => $status->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function directoryStatuses(): array
    {
        return [
            'active' => 'Active search',
            'possible-sighting' => 'Possible sighting',
            'possible-found' => 'Possibly found',
            'safe' => 'Animal is safe',
            'identity-confirmed' => 'Identity confirmed',
            'long-term' => 'Long-term search',
            'returned' => 'Returned',
        ];
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return [
            'dog' => 'Dog',
            'cat' => 'Cat',
            'bird' => 'Bird',
            'rabbit' => 'Rabbit',
            'rodent' => 'Small mammal',
            'reptile' => 'Reptile',
            'amphibian' => 'Amphibian',
            'horse' => 'Horse',
            'farm-animal' => 'Farm animal',
            'exotic' => 'Exotic animal',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public function sizes(): array
    {
        return [
            'very-small' => 'Very small',
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large',
            'very-large' => 'Very large',
            'unknown' => 'Unknown',
        ];
    }

    /** @return array<string, string> */
    public function microchipStatuses(): array
    {
        return [
            'present' => 'Microchip present',
            'possible' => 'May have a microchip',
            'absent' => 'No microchip',
            'unknown' => 'Unknown',
        ];
    }

    /** @return array<string, string> */
    public function confidenceOptions(): array
    {
        return [
            'exact' => 'I am certain',
            'very-similar' => 'Very similar',
            'possible' => 'Possibly similar',
            'uncertain' => 'Not sure',
        ];
    }

    /** @return array<string, string> */
    public function contactStatuses(): array
    {
        return [
            'seen-only' => 'Only saw the animal',
            'approached' => 'Animal approached',
            'fed' => 'Offered food',
            'ran-away' => 'Animal moved away',
            'secured' => 'Secured in a safe place',
            'with-reporter' => 'Animal is with me',
            'transferred' => 'Transferred to another person or organization',
            'service-called' => 'Called a service',
        ];
    }

    /** @return array<string, string> */
    public function taskTypes(): array
    {
        return [
            'search-area' => 'Check an area',
            'posters' => 'Place posters',
            'call-clinics' => 'Call clinics',
            'call-shelters' => 'Call shelters',
            'check-cameras' => 'Request camera review',
            'transport' => 'Provide transport',
            'equipment' => 'Deliver equipment',
            'translation' => 'Translate the alert',
            'online-coordination' => 'Coordinate online',
        ];
    }

    /** @return array<string, string> */
    public function volunteerCapabilities(): array
    {
        return [
            'walking-search' => 'Search on foot',
            'posters' => 'Place or remove posters',
            'phone-calls' => 'Call clinics and shelters',
            'camera-review' => 'Help review camera footage',
            'transport' => 'Provide transport',
            'equipment' => 'Provide a carrier or equipment',
            'translation' => 'Translate',
            'online-coordination' => 'Coordinate online',
            'temporary-care' => 'Provide temporary safe care',
        ];
    }

    /** @return array<string, string> */
    public function reportReasons(): array
    {
        return [
            'false-report' => 'False report',
            'stolen-photos' => 'Stolen photos',
            'scam' => 'Scam or suspicious payment request',
            'outdated' => 'No longer current',
            'personal-data' => 'Personal data exposed',
            'animal-danger' => 'Animal is in danger',
            'cruelty' => 'Cruel treatment',
            'illegal-animal' => 'Illegal ownership or species',
            'threat' => 'Threat or extortion',
            'hidden-sale' => 'Hidden sale',
            'other' => 'Other',
        ];
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return [
            'latest-sighting' => 'Latest sighting',
            'newest' => 'Newest report',
            'urgent' => 'Urgent first',
            'nearest' => 'Nearest area',
        ];
    }
}
