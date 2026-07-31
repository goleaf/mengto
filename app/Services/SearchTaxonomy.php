<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;

final class SearchTaxonomy
{
    /** @return array<string, string> */
    public function types(): array
    {
        return collect(SearchCaseType::cases())
            ->mapWithKeys(fn (SearchCaseType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /** @return array<string, string> */
    public function typeDescriptions(): array
    {
        return $this->labels('type_description', array_map(
            static fn (SearchCaseType $type): string => $type->value,
            SearchCaseType::cases(),
        ));
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
        return $this->labels('status', [
            'active',
            'possible-sighting',
            'possible-found',
            'safe',
            'identity-confirmed',
            'long-term',
            'returned',
            'reunited',
        ]);
    }

    /** @return array<string, string> */
    public function species(): array
    {
        return $this->labels('species', [
            'dog', 'cat', 'bird', 'rabbit', 'rodent', 'reptile', 'amphibian',
            'horse', 'farm-animal', 'exotic', 'other',
        ]);
    }

    /** @return array<string, string> */
    public function sizes(): array
    {
        return $this->labels('size', [
            'very-small', 'small', 'medium', 'large', 'very-large', 'unknown',
        ]);
    }

    /** @return array<string, string> */
    public function microchipStatuses(): array
    {
        return $this->labels('microchip', ['present', 'possible', 'absent', 'unknown']);
    }

    /** @return array<string, string> */
    public function confidenceOptions(): array
    {
        return $this->labels('confidence', ['exact', 'very-similar', 'possible', 'uncertain']);
    }

    /** @return array<string, string> */
    public function contactStatuses(): array
    {
        return $this->labels('contact_status', [
            'seen-only', 'approached', 'fed', 'ran-away', 'secured',
            'with-reporter', 'transferred', 'service-called',
        ]);
    }

    /** @return array<string, string> */
    public function taskTypes(): array
    {
        return $this->labels('task_type', [
            'search-area', 'posters', 'call-clinics', 'call-shelters',
            'check-cameras', 'transport', 'equipment', 'translation',
            'online-coordination',
        ]);
    }

    /** @return array<string, string> */
    public function volunteerCapabilities(): array
    {
        return $this->labels('volunteer_capability', [
            'walking-search', 'posters', 'phone-calls', 'camera-review',
            'transport', 'equipment', 'translation', 'online-coordination',
            'temporary-care',
        ]);
    }

    /** @return array<string, string> */
    public function reportReasons(): array
    {
        return $this->labels('report_reason', [
            'false-lost-animal-sighting',
            'reward-scam',
            'lost-animal-scam',
            'stolen-image',
            'outdated-critical-information',
            'private-address-exposure',
            'animal-cruelty',
            'prohibited-animal-sale',
            'threats',
            'other',
        ]);
    }

    /** @return array<string, string> */
    public function sortOptions(): array
    {
        return $this->labels('sort', ['latest-sighting', 'newest', 'urgent', 'nearest']);
    }

    /** @return array<string, string> */
    public function relayPurposes(): array
    {
        return $this->labels('relay_purpose', [
            'sighting',
            'identity-evidence',
            'safe-custody',
            'safety',
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
                $key => __("lost_found.{$group}.{$key}"),
            ])
            ->all();
    }
}
