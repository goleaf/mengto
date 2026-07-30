<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PawCirclePlacePresenter
{
    public function __construct(
        private readonly PawCirclePlaceCatalog $catalog,
        private readonly PawCirclePlaceContentCatalog $content,
        private readonly PawCirclePlaceState $state,
        private readonly PawCircleProfilePresenter $profiles,
        private readonly PawCircleEventCatalog $events,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public function directory(array $parameters = []): array
    {
        $query = trim((string) ($parameters['q'] ?? ''));
        $parsedQuery = $this->parseNaturalQuery($query);
        $filters = $this->directoryFilters($parameters, $parsedQuery);
        $emergency = (bool) ($parameters['emergency'] ?? false);

        if ($emergency) {
            $filters['category'] = 'emergency-vet';
            $filters['open_now'] = true;
            $filters['layer'] = 'emergency';
            $filters['mode'] = 'emergency';
        }

        $places = array_map(
            fn (array $place): array => $this->decoratePlace($place),
            $this->catalog->all(),
        );
        $places = array_values(array_filter(
            $places,
            fn (array $place): bool => $this->matches($place, $query, $filters),
        ));
        usort($places, fn (array $left, array $right): int => $this->compare($left, $right, $filters['sort']));

        $selectedKey = (string) ($parameters['selected'] ?? ($places[0]['key'] ?? ''));
        $selected = collect($places)->firstWhere('key', $selectedKey) ?? ($places[0] ?? null);
        $location = $this->state->generalizedLocation();
        $summary = $this->directorySummary($places, $filters, $emergency);

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $emergency ? 'Emergency veterinary help | PawCircle' : 'Places map | PawCircle',
            'active_section' => 'places',
            'summary' => $summary,
            'places' => [
                'items' => $places,
                'map_items' => $this->mapItems($places, $emergency),
                'selected' => $selected,
                'query' => $query,
                'parsed_query' => $parsedQuery,
                'filters' => $filters,
                'advanced_filters_active' => $this->advancedFiltersActive($filters),
                'filter_options' => $this->filterOptions(),
                'category_options' => $this->catalog->categoryOptions(),
                'species_options' => $this->catalog->speciesOptions(),
                'size_options' => $this->catalog->sizeOptions(),
                'sort_options' => $this->sortOptions(),
                'view_options' => $this->viewOptions(),
                'mode_options' => $this->modeOptions(),
                'layer_options' => $this->layerOptions(),
                'browse_url' => route('pet-social.places.index'),
                'add_url' => route('pet-social.compose', ['kind' => 'place']),
                'emergency_url' => route('pet-social.places.index', ['emergency' => 1, 'open_now' => 1]),
                'location' => $location,
                'comparison' => array_slice($places, 0, 3),
                'recent' => $this->recentPlaces(),
                'collections' => $this->state->collections(),
                'submissions' => $this->state->submissions(),
                'emergency' => $emergency,
                'empty_message' => $emergency
                    ? 'No suitable open clinic matches every filter. Widen the area and call the nearest capable clinic.'
                    : 'No places match every selected filter. Remove one filter or search another area.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $key, string $tab = 'overview'): ?array
    {
        $place = $this->catalog->find($key);

        if ($place === null) {
            return null;
        }

        $this->state->recordViewed($key);
        $place = $this->decoratePlace($place);
        $tabOptions = $this->tabOptions($place);
        $tab = array_key_exists($tab, $tabOptions) ? $tab : 'overview';
        $content = $this->content->content($place);
        $content['reviews'] = [
            ...$this->state->reviews($key),
            ...$content['reviews'],
        ];
        $content['questions'] = [
            ...$this->state->questions($key),
            ...$content['questions'],
        ];
        $content['events'] = $this->placeEvents($place['events']);
        $content['warnings'] = $this->state->warnings($key, $place['base_warnings']);
        $content['history'] = $this->history($key, $content['updates']);

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $place['name'].' | PawCircle',
            'active_section' => 'places',
            'place' => $place,
            'tabs' => $this->tabs($place, $tab, $tabOptions),
            'active_tab' => $tab,
            'content' => $content,
            'check_in' => $this->state->currentCheckIn($key),
            'collections' => $this->collectionOptions($key),
            'claims' => $this->state->claims($key),
            'corrections' => $this->state->corrections($key),
            'can_manage' => (bool) $place['owner_managed'],
            'report_url' => route('pet-social.compose', [
                'kind' => 'report-place',
                'target' => $key,
            ]),
            'correction_url' => route('pet-social.compose', [
                'kind' => 'place-correction',
                'target' => $key,
            ]),
            'warning_url' => route('pet-social.compose', [
                'kind' => 'place-warning',
                'target' => $key,
            ]),
            'review_url' => route('pet-social.compose', [
                'kind' => 'place-review',
                'target' => $key,
            ]),
            'question_url' => route('pet-social.compose', [
                'kind' => 'place-question',
                'target' => $key,
            ]),
            'claim_url' => route('pet-social.compose', [
                'kind' => 'place-claim',
                'target' => $key,
            ]),
            'event_url' => route('pet-social.compose', [
                'kind' => 'meetup',
                'place' => $key,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function reportContext(string $target): ?array
    {
        $place = $this->catalog->find($target);

        if ($place === null) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $place['name'],
            'route' => 'pet-social.places.show',
            'route_parameters' => ['place' => $target],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function correctionContext(string $target): ?array
    {
        $place = $this->catalog->find($target);

        if ($place === null) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $place['name'],
            'route' => 'pet-social.places.show',
            'route_parameters' => ['place' => $target],
            'place' => $place,
            'fields' => [
                'hours' => 'Opening hours',
                'pet-rules' => 'Pet access rules',
                'address' => 'Address or entrance',
                'contact' => 'Phone or website',
                'services' => 'Services',
                'accessibility' => 'Accessibility',
                'closure' => 'Temporary or permanent closure',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    private function decoratePlace(array $place): array
    {
        $warnings = $this->state->warnings($place['key'], $place['base_warnings']);
        $activeWarnings = array_values(array_filter(
            $warnings,
            static fn (array $warning): bool => ! in_array($warning['status'], ['resolved', 'expired', 'false'], true),
        ));
        $saved = $this->state->isSaved($place['key']);
        $followed = $this->state->isFollowed($place['key']);
        $checkIn = $this->state->currentCheckIn($place['key']);
        $statusTone = match ($place['open_state']) {
            'open', 'appointment-only' => 'positive',
            'closing-soon', 'on-call', 'open-with-warning' => 'warning',
            default => 'neutral',
        };

        return [
            ...$place,
            'detail_url' => route('pet-social.places.show', ['place' => $place['key']]),
            'map_url' => 'https://www.openstreetmap.org/?mlat='.$place['latitude'].'&mlon='.$place['longitude'].'#map=16/'.$place['latitude'].'/'.$place['longitude'],
            'route_url' => 'https://www.openstreetmap.org/directions?engine=fossgis_osrm_foot&route=54.687%2C25.279%3B'.$place['latitude'].'%2C'.$place['longitude'],
            'call_url' => $place['phone'] === null ? null : 'tel:'.preg_replace('/[^+0-9]/', '', $place['phone']),
            'status_tone' => $statusTone,
            'saved' => $saved,
            'followed' => $followed,
            'visited' => $this->state->hasVisited($place['key']),
            'check_in' => $checkIn,
            'warnings' => $warnings,
            'active_warnings' => $activeWarnings,
            'warning_count' => count($activeWarnings),
            'distance_label' => number_format((float) $place['distance_km'], 1).' km',
            'travel_label' => $place['travel_minutes'].' min',
            'rating_label' => number_format((float) $place['rating'], 1).' · '.$place['review_count'].' reviews',
            'pet_fit' => $this->petFit($place),
            'marker_label' => $place['category_label'].', '.$place['name'].', '.$place['open_label'].', '.number_format((float) $place['distance_km'], 1).' kilometers',
            'category_tone' => $this->categoryTone((string) $place['primary_category']),
            'save_action' => [
                'label' => $saved ? 'Saved' : 'Save place',
                'icon' => $saved ? 'bookmark-check' : 'bookmark',
                'active' => $saved,
                'payload' => ['action' => 'toggle-place-save', 'target' => $place['key']],
            ],
            'follow_action' => [
                'label' => $followed ? 'Following updates' : 'Follow updates',
                'icon' => $followed ? 'bell-ring' : 'bell',
                'active' => $followed,
                'payload' => ['action' => 'toggle-place-follow', 'target' => $place['key']],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @param  array<string, mixed>  $filters
     */
    private function matches(array $place, string $query, array $filters): bool
    {
        $haystack = Str::lower(implode(' ', [
            $place['name'],
            $place['short_name'],
            $place['summary'],
            $place['city'],
            $place['neighborhood'],
            $place['address'],
            $place['category_label'],
            implode(' ', $place['categories']),
            implode(' ', $place['services']),
            implode(' ', $place['features']),
            implode(' ', $place['accepted_species']),
        ]));
        $tokens = array_values(array_filter(preg_split('/\s+/', Str::lower($query)) ?: []));
        $queryMatches = $query === '' || collect($tokens)->every(
            static fn (string $token): bool => Str::contains($haystack, $token),
        );
        $categoryMatches = $filters['category'] === 'all'
            || in_array($filters['category'], $place['categories'], true);
        $speciesMatches = $filters['species'] === 'any'
            || in_array($filters['species'], $place['accepted_species'], true);
        $sizeMatches = $filters['size'] === 'any'
            || in_array($filters['size'], $place['accepted_sizes'], true);
        $distanceMatches = $filters['distance'] === 'any'
            || (float) $place['distance_km'] <= (float) $filters['distance'];
        $openMatches = ! $filters['open_now']
            || in_array($place['open_state'], ['open', 'closing-soon', 'open-with-warning', 'on-call', 'appointment-only'], true);
        $leashMatches = match ($filters['leash']) {
            'off-leash' => Str::contains(Str::lower($place['leash_policy']), 'off leash'),
            'fenced' => (bool) $place['fenced'],
            'required' => Str::contains(Str::lower($place['leash_policy']), 'required'),
            default => true,
        };
        $accessMatches = match ($filters['accessibility']) {
            'wheelchair' => (bool) $place['wheelchair_access'],
            'quiet' => (bool) $place['quiet_zone'],
            'parking' => (bool) $place['parking'],
            'lighting' => (bool) $place['lighting'],
            default => true,
        };
        $safetyMatches = match ($filters['safety']) {
            'fenced' => (bool) $place['fenced'],
            'water' => (bool) $place['water'],
            'lighting' => (bool) $place['lighting'],
            'no-warnings' => $place['warning_count'] === 0,
            default => true,
        };
        $priceMatches = $filters['price'] === 'any'
            || $place['price_level'] === $filters['price'];
        $ratingMatches = $filters['rating'] === 'any'
            || (float) $place['rating'] >= (float) $filters['rating'];
        $verificationMatches = match ($filters['verification']) {
            'verified' => in_array($place['verification']['tone'], ['verified', 'demo'], true),
            'community' => $place['verification']['tone'] === 'community',
            'recent' => Str::contains($place['data_freshness'], ['today', 'yesterday', 'hour']),
            default => true,
        };
        $crowdMatches = $filters['crowd'] === 'any'
            || $place['crowd_level'] === $filters['crowd'];
        $timeMatches = match ($filters['visit_time']) {
            'evening', 'night' => (bool) $place['lighting'] && $openMatches,
            'quiet' => (bool) $place['quiet_zone'] && $place['crowd_level'] === 'low',
            default => true,
        };
        $modeMatches = match ($filters['mode']) {
            'favorites' => $place['saved'],
            'visited' => $place['visited'],
            'events' => count($place['events']) > 0,
            'warnings' => $place['warning_count'] > 0,
            'emergency' => (bool) $place['emergency'],
            default => true,
        };

        return $queryMatches
            && $categoryMatches
            && $speciesMatches
            && $sizeMatches
            && $distanceMatches
            && $openMatches
            && $leashMatches
            && $accessMatches
            && $safetyMatches
            && $priceMatches
            && $ratingMatches
            && $verificationMatches
            && $crowdMatches
            && $timeMatches
            && $modeMatches;
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function compare(array $left, array $right, string $sort): int
    {
        return match ($sort) {
            'distance' => $left['distance_km'] <=> $right['distance_km'],
            'travel-time' => $left['travel_minutes'] <=> $right['travel_minutes'],
            'rating' => $right['rating'] <=> $left['rating'],
            'reviews' => $right['review_count'] <=> $left['review_count'],
            'name' => strnatcasecmp($left['name'], $right['name']),
            'freshness' => strcmp($right['verification']['updated_at'], $left['verification']['updated_at']),
            'open' => $this->openRank($left['open_state']) <=> $this->openRank($right['open_state']),
            default => $this->recommendationScore($right) <=> $this->recommendationScore($left),
        };
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>  $parsedQuery
     * @return array<string, mixed>
     */
    private function directoryFilters(array $parameters, array $parsedQuery): array
    {
        return [
            'category' => (string) ($parameters['category'] ?? $parsedQuery['category'] ?? 'all'),
            'species' => (string) ($parameters['species'] ?? $parsedQuery['species'] ?? 'any'),
            'size' => (string) ($parameters['size'] ?? $parsedQuery['size'] ?? 'any'),
            'distance' => (string) ($parameters['distance'] ?? 'any'),
            'open_now' => (bool) ($parameters['open_now'] ?? ($parsedQuery['open_now'] ?? false)),
            'leash' => (string) ($parameters['leash'] ?? $parsedQuery['leash'] ?? 'any'),
            'accessibility' => (string) ($parameters['accessibility'] ?? $parsedQuery['accessibility'] ?? 'any'),
            'safety' => (string) ($parameters['safety'] ?? $parsedQuery['safety'] ?? 'any'),
            'price' => (string) ($parameters['price'] ?? 'any'),
            'rating' => (string) ($parameters['rating'] ?? 'any'),
            'verification' => (string) ($parameters['verification'] ?? 'any'),
            'crowd' => (string) ($parameters['crowd'] ?? $parsedQuery['crowd'] ?? 'any'),
            'visit_time' => (string) ($parameters['visit_time'] ?? $parsedQuery['visit_time'] ?? 'any'),
            'pet' => (string) ($parameters['pet'] ?? 'scout'),
            'sort' => (string) ($parameters['sort'] ?? 'recommended'),
            'view' => (string) ($parameters['view'] ?? 'split'),
            'mode' => (string) ($parameters['mode'] ?? 'browse'),
            'layer' => (string) ($parameters['layer'] ?? 'places'),
            'area' => (string) ($parameters['area'] ?? 'Vilnius'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function advancedFiltersActive(array $filters): bool
    {
        $basicFilters = [
            'category',
            'pet',
            'sort',
            'view',
            'mode',
            'layer',
            'area',
            'open_now',
        ];

        foreach (array_diff_key($filters, array_flip($basicFilters)) as $value) {
            if (! in_array($value, ['any', null, ''], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseNaturalQuery(string $query): array
    {
        $value = Str::lower($query);
        $filters = [];
        $labels = [];

        $rules = [
            [['24 hour', '24-hour', 'emergency', 'srocn', 'kruglosut'], 'category', 'emergency-vet', '24-hour veterinary clinics'],
            [['clinic', 'vet', 'veterinar'], 'category', 'vet', 'veterinary clinics'],
            [['groom', 'grumer'], 'category', 'grooming', 'grooming'],
            [['dog park', 'ploscad'], 'category', 'dog-park', 'dog parks'],
            [['park', 'progulk'], 'category', 'park', 'parks'],
            [['cafe', 'kafe', 'terrace'], 'category', 'pet-cafe', 'pet-friendly cafes'],
            [['bird', 'ptic', 'popug'], 'species', 'bird', 'accepts birds'],
            [['cat', 'kosk'], 'species', 'cat', 'accepts cats'],
            [['large dog', 'krupn'], 'size', 'large', 'large pets'],
            [['small dog', 'malenk'], 'size', 'small', 'small pets'],
            [['quiet', 'tix', 'calm', 'spok'], 'crowd', 'low', 'usually quiet'],
            [['evening', 'vecer'], 'visit_time', 'evening', 'evening visit'],
            [['night', 'noc'], 'visit_time', 'night', 'night visit'],
            [['fenced', 'ogoroz'], 'safety', 'fenced', 'fully fenced'],
            [['water', 'voda'], 'safety', 'water', 'water available'],
            [['light', 'osves'], 'accessibility', 'lighting', 'lighting'],
            [['open now', 'otkryt'], 'open_now', true, 'open now'],
        ];

        foreach ($rules as [$needles, $key, $filter, $label]) {
            if (! Str::contains($value, $needles)) {
                continue;
            }

            if (! isset($filters[$key])) {
                $filters[$key] = $filter;
                $labels[] = $label;
            }
        }

        return [
            ...$filters,
            'labels' => $labels,
            'summary' => $labels === []
                ? null
                : 'We understood: '.implode(' · ', $labels),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $places
     * @return array<int, array<string, mixed>>
     */
    private function mapItems(array $places, bool $emergency): array
    {
        return array_map(
            static fn (array $place, int $index): array => [
                'key' => $place['key'],
                'name' => $place['name'],
                'category' => $place['primary_category'],
                'category_icon' => $place['category_icon'],
                'category_tone' => $place['category_tone'],
                'x' => $place['map_x'],
                'y' => $place['map_y'],
                'cluster' => $emergency ? null : 'cluster-'.floor($place['map_x'] / 25).'-'.floor($place['map_y'] / 25),
                'label' => $place['marker_label'],
                'status' => $place['open_label'],
                'distance' => $place['distance_label'],
                'detail_url' => $place['detail_url'],
                'warning_count' => $place['warning_count'],
                'emergency' => $place['emergency'],
                'position' => $index + 1,
            ],
            $places,
            array_keys($places),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $places
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function directorySummary(array $places, array $filters, bool $emergency): array
    {
        $open = count(array_filter(
            $places,
            static fn (array $place): bool => in_array($place['open_state'], ['open', 'closing-soon', 'open-with-warning', 'on-call', 'appointment-only'], true),
        ));

        return [
            'eyebrow' => $emergency ? 'Urgent veterinary navigator' : 'Map and place catalog',
            'title' => $emergency ? 'Find suitable veterinary help now' : 'Plan the next place with your pet',
            'description' => $emergency
                ? 'Showing open or on-call clinics that match the selected species. Call before travel because intake and clinicians can change.'
                : 'Compare parks, dog runs, routes, clinics, services, shelters, stores, and pet-friendly places without exposing your home or movement history.',
            'count' => count($places).' '.Str::plural('place', count($places)).' · '.$filters['area'],
            'highlights' => [
                ['label' => 'Open now', 'value' => (string) $open, 'detail' => 'based on stored hours'],
                ['label' => 'Selected pet', 'value' => Str::headline($filters['pet']), 'detail' => 'recommendations are editable'],
                ['label' => 'Map privacy', 'value' => 'Generalized', 'detail' => 'no home point published'],
                ['label' => 'Active mode', 'value' => Str::headline($filters['mode']), 'detail' => 'filters stay in place'],
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function filterOptions(): array
    {
        return [
            'distance' => ['any' => 'Any distance', '1' => 'Up to 1 km', '5' => 'Up to 5 km', '10' => 'Up to 10 km'],
            'leash' => ['any' => 'Any leash rule', 'off-leash' => 'Off-leash area', 'fenced' => 'Fenced', 'required' => 'Leash required'],
            'accessibility' => ['any' => 'Any access', 'wheelchair' => 'Step-free or wheelchair access', 'quiet' => 'Quiet zone', 'parking' => 'Parking', 'lighting' => 'Lighting'],
            'safety' => ['any' => 'Any safety features', 'fenced' => 'Fully fenced', 'water' => 'Water', 'lighting' => 'Lighting', 'no-warnings' => 'No active warnings'],
            'price' => ['any' => 'Any price', 'free' => 'Free', 'paid' => 'Paid'],
            'rating' => ['any' => 'Any rating', '4' => '4.0 and above', '4.5' => '4.5 and above'],
            'verification' => ['any' => 'Any source', 'verified' => 'Verified or managed', 'community' => 'Community verified', 'recent' => 'Updated recently'],
            'crowd' => ['any' => 'Any crowd level', 'low' => 'Usually quiet', 'medium' => 'Moderate', 'high' => 'Often busy', 'unknown' => 'No data'],
            'visit_time' => ['any' => 'Any time', 'morning' => 'Morning', 'evening' => 'Evening', 'night' => 'Night', 'quiet' => 'Quiet time'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'recommended' => 'Recommended',
            'distance' => 'Distance',
            'travel-time' => 'Travel time',
            'rating' => 'Rating',
            'reviews' => 'Review count',
            'open' => 'Open status',
            'freshness' => 'Recently updated',
            'name' => 'Name',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function viewOptions(): array
    {
        return [
            'split' => 'Map + list',
            'map' => 'Map',
            'list' => 'List',
            'fullscreen' => 'Fullscreen map',
            'route' => 'Route mode',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function modeOptions(): array
    {
        return [
            'browse' => 'All places',
            'favorites' => 'Favorites',
            'visited' => 'Visited',
            'events' => 'With events',
            'warnings' => 'Warnings',
            'emergency' => 'Emergency clinics',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function layerOptions(): array
    {
        return [
            'places' => 'Places',
            'routes' => 'Walking routes',
            'events' => 'Events',
            'warnings' => 'Warnings',
            'lost-pets' => 'Lost pets',
            'emergency' => 'Emergency clinics',
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, string>
     */
    private function tabOptions(array $place): array
    {
        $tabs = [
            'overview' => 'Overview',
            'photos' => 'Photos',
            'services' => 'Services',
            'rules' => 'Rules',
            'hours' => 'Hours',
            'reviews' => 'Reviews',
            'events' => 'Events',
            'questions' => 'Questions',
            'map' => 'Map',
            'updates' => 'Updates',
            'corrections' => 'Corrections',
        ];

        if (in_array($place['primary_category'], ['vet', 'emergency-vet', 'grooming'], true)) {
            $tabs = [
                ...array_slice($tabs, 0, 5, true),
                'specialists' => 'Specialists',
                ...array_slice($tabs, 5, null, true),
            ];
        }

        return $tabs;
    }

    /**
     * @param  array<string, mixed>  $place
     * @param  array<string, string>  $options
     * @return array<int, array{label: string, href: string, active: bool}>
     */
    private function tabs(array $place, string $active, array $options): array
    {
        return array_map(
            static fn (string $label, string $value): array => [
                'label' => $label,
                'href' => route('pet-social.places.show', ['place' => $place['key'], 'tab' => $value]),
                'active' => $active === $value,
            ],
            array_values($options),
            array_keys($options),
        );
    }

    /**
     * @param  array<int, string>  $eventKeys
     * @return array<int, array<string, mixed>>
     */
    private function placeEvents(array $eventKeys): array
    {
        return array_values(array_filter(array_map(
            function (string $key): ?array {
                $event = $this->events->find($key);

                if ($event === null || $event['privacy'] === 'hidden') {
                    return null;
                }

                return [
                    'key' => $event['key'],
                    'title' => $event['title'],
                    'category' => $event['category'],
                    'starts_at' => $event['starts_at'],
                    'place' => $event['general_location'],
                    'status' => Str::headline($event['status']),
                    'href' => route('pet-social.meetups.show', ['event' => $event['key']]),
                ];
            },
            $eventKeys,
        )));
    }

    /**
     * @param  array<int, array<string, string>>  $updates
     * @return array<int, array<string, string>>
     */
    private function history(string $place, array $updates): array
    {
        $stateHistory = array_map(
            static fn (array $item): array => [
                'title' => $item['message'],
                'body' => 'Recorded in the private place action history.',
                'time' => $item['created_at'],
                'icon' => 'history',
                'status' => 'Account action',
            ],
            $this->state->history($place),
        );

        return [...$stateHistory, ...$updates];
    }

    /**
     * @return array<string, array{name: string, privacy: string, places: array<int, string>, active: bool}>
     */
    private function collectionOptions(string $place): array
    {
        return array_map(
            static fn (array $collection) => [
                ...$collection,
                'active' => in_array($place, $collection['places'], true),
            ],
            $this->state->collections(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentPlaces(): array
    {
        return array_values(array_filter(array_map(
            fn (string $key): ?array => ($place = $this->catalog->find($key)) === null
                ? null
                : $this->decoratePlace($place),
            $this->state->recent(),
        )));
    }

    /**
     * @param  array<string, mixed>  $place
     */
    private function petFit(array $place): array
    {
        if (in_array('dog', $place['accepted_species'], true) && in_array('large', $place['accepted_sizes'], true)) {
            return [
                'label' => $place['quiet_zone'] ? 'May suit Scout' : 'Discuss crowd level',
                'detail' => $place['quiet_zone']
                    ? 'Large dogs and a quiet-space preference are supported.'
                    : 'Large dogs are accepted; current crowd conditions still matter.',
                'tone' => $place['quiet_zone'] ? 'positive' : 'warning',
            ];
        }

        if ($place['primary_category'] === 'grooming' && in_array('cat', $place['accepted_species'], true)) {
            return [
                'label' => 'May suit Nori',
                'detail' => 'Cat handling and a quiet appointment are listed.',
                'tone' => 'positive',
            ];
        }

        return [
            'label' => 'Review pet access',
            'detail' => 'Choose another pet profile or contact the place for current suitability.',
            'tone' => 'neutral',
        ];
    }

    private function categoryTone(string $category): string
    {
        return match ($category) {
            'park', 'route' => 'green',
            'dog-park' => 'blue',
            'vet', 'emergency-vet' => 'red',
            'grooming' => 'violet',
            'pet-cafe' => 'amber',
            'shelter' => 'teal',
            default => 'gray',
        };
    }

    /**
     * @param  array<string, mixed>  $place
     */
    private function recommendationScore(array $place): float
    {
        return ((float) $place['rating'] * 20)
            - ((float) $place['distance_km'] * 2)
            + ($place['open_state'] === 'open' ? 12 : 0)
            + ($place['quiet_zone'] ? 6 : 0)
            + ($place['warning_count'] === 0 ? 4 : -8)
            + ($place['sponsored'] ? 0 : 1);
    }

    private function openRank(string $state): int
    {
        return match ($state) {
            'open' => 0,
            'open-with-warning', 'closing-soon' => 1,
            'appointment-only', 'on-call' => 2,
            default => 3,
        };
    }
}
