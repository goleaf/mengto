<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class PlacePresenter
{
    private const int PLACES_PER_PAGE = 6;

    public function __construct(
        private readonly PlaceCatalog $catalog,
        private readonly PlaceContentCatalog $content,
        private readonly PlaceQuestionPresenter $questions,
        private readonly PlaceState $state,
        private readonly ProfilePresenter $profiles,
        private readonly EventCatalog $events,
        private readonly LocaleFormatter $formatter,
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

        if ($this->canUseNameSortedPage($filters, $emergency)) {
            $page = $this->catalog->nameSortedPage(
                $query,
                max(1, (int) ($parameters['page'] ?? 1)),
                self::PLACES_PER_PAGE,
            );
            $places = array_map(
                fn (array $place): array => $this->decoratePlace($place),
                $page['items'],
            );
            $pageItems = $places;
            $totalPlaces = $page['total'];
            $currentPage = $page['current_page'];
            $lastPage = $page['last_page'];
            $mapItems = [];
        } else {
            $places = array_map(
                fn (array $place): array => $this->decoratePlace($place),
                $this->catalog->all(),
            );
            $places = array_values(array_filter(
                $places,
                fn (array $place): bool => $this->matches($place, $query, $filters),
            ));
            usort($places, fn (array $left, array $right): int => $this->compare($left, $right, $filters['sort']));

            $totalPlaces = count($places);
            $lastPage = max(1, (int) ceil($totalPlaces / self::PLACES_PER_PAGE));
            $currentPage = min((int) ($parameters['page'] ?? 1), $lastPage);
            $pageItems = array_slice(
                $places,
                ($currentPage - 1) * self::PLACES_PER_PAGE,
                self::PLACES_PER_PAGE,
            );
            $mapItems = $this->mapItems($places, $emergency);
        }

        $selectedKey = (string) ($parameters['selected'] ?? ($pageItems[0]['key'] ?? ''));
        $selected = collect($places)->firstWhere('key', $selectedKey) ?? ($places[0] ?? null);
        $location = $this->state->generalizedLocation();
        $summary = $this->directorySummary($places, $filters, $emergency, $totalPlaces);

        return [
            'page_title' => $emergency
                ? __('place_directory.page.emergency_title')
                : __('place_directory.page.title'),
            'active_section' => 'places',
            'summary' => $summary,
            'places' => [
                'items' => $pageItems,
                'map_items' => $mapItems,
                'selected' => $selected,
                'query' => $query,
                'parsed_query' => $parsedQuery,
                'filters' => $filters,
                'advanced_filters_active' => $this->advancedFiltersActive($filters),
                'filter_options' => $this->filterOptions(),
                'filter_labels' => $this->filterLabels(),
                'category_options' => $this->catalog->categoryOptions(),
                'category_icons' => $this->catalog->iconOptions(),
                'species_options' => $this->catalog->speciesOptions(),
                'pet_options' => $this->petOptions(includeNone: true),
                'size_options' => $this->catalog->sizeOptions(),
                'sort_options' => $this->sortOptions(),
                'view_options' => $this->viewOptions(),
                'mode_options' => $this->modeOptions(),
                'layer_options' => $this->layerOptions(),
                'browse_url' => route('places.index'),
                'add_url' => route('places.submissions.create'),
                'emergency_url' => route('places.index', ['emergency' => 1, 'open_now' => 1]),
                'location' => $location,
                'comparison' => array_slice($places, 0, 3),
                'pagination' => $this->pagination(
                    $parameters,
                    $currentPage,
                    $lastPage,
                    $totalPlaces,
                    count($pageItems),
                ),
                'recent' => $this->recentPlaces(),
                'collections' => $this->state->collections(),
                'submissions' => $this->state->submissions(),
                'emergency' => $emergency,
                'empty_message' => $emergency
                    ? __('place_directory.empty.emergency')
                    : __('place_directory.empty.default'),
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
        $content['reviews'] = array_map(
            static fn (array $review): array => [
                ...$review,
                'criterion_label' => Str::headline((string) $review['criterion']),
            ],
            [
                ...$this->stateReviews($key),
                ...$content['reviews'],
            ],
        );
        $fixtureQuestions = array_map(
            static fn (array $question): array => [
                ...$question,
                'answerable' => false,
                'answer_idempotency_key' => '',
            ],
            $content['questions'],
        );
        $content['questions'] = $tab === 'questions'
            ? [...$this->questions->forPlace($place['key']), ...$fixtureQuestions]
            : $fixtureQuestions;
        $content['events'] = $this->placeEvents($place['events']);
        $content['warnings'] = $this->presentWarnings(
            $this->state->warnings($key, $place['base_warnings']),
        );
        $content['history'] = $this->history($key, $content['updates']);
        $petOptions = $this->petOptions();

        return [
            'page_title' => __('presentation.brand_title', ['title' => $place['name']]),
            'active_section' => 'places',
            'place' => $place,
            'tabs' => $this->tabs($place, $tab, $tabOptions),
            'active_tab' => $tab,
            'content' => $content,
            'check_in' => $this->presentCheckIn($this->state->currentCheckIn($key)),
            'collections' => $this->collectionOptions($key),
            'pet_options' => $petOptions,
            'default_pet_key' => (string) array_key_first($petOptions),
            'claims' => array_map(
                static fn (array $claim): array => [
                    ...$claim,
                    'status_label' => Str::headline((string) $claim['status']),
                ],
                $this->state->claims($key),
            ),
            'corrections' => array_map(
                static fn (array $correction): array => [
                    ...$correction,
                    'field_label' => Str::headline((string) $correction['field']),
                    'status_label' => Str::headline((string) $correction['status']),
                ],
                $this->state->corrections($key),
            ),
            'can_manage' => (bool) $place['owner_managed'],
            'report_url' => route('compose', [
                'kind' => 'report-place',
                'target' => $key,
            ]),
            'correction_url' => route('compose', [
                'kind' => 'place-correction',
                'target' => $key,
            ]),
            'warning_url' => route('compose', [
                'kind' => 'place-warning',
                'target' => $key,
            ]),
            'review_url' => route('compose', [
                'kind' => 'place-review',
                'target' => $key,
            ]),
            'question_url' => route('compose', [
                'kind' => 'place-question',
                'target' => $key,
            ]),
            'claim_url' => route('compose', [
                'kind' => 'place-claim',
                'target' => $key,
            ]),
            'event_url' => route('compose', [
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
            'route' => 'places.show',
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
            'route' => 'places.show',
            'route_parameters' => ['place' => $target],
            'place' => $place,
            'fields' => [
                'hours' => __('messages.opening_hours'),
                'pet-rules' => __('messages.pet_access_rules'),
                'address' => __('messages.address_or_entrance'),
                'contact' => __('messages.phone_or_website'),
                'services' => __('messages.services'),
                'accessibility' => __('messages.accessibility'),
                'closure' => __('messages.temporary_or_permanent_closure'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    private function decoratePlace(array $place): array
    {
        $warnings = $this->presentWarnings(
            $this->state->warnings($place['key'], $place['base_warnings']),
        );
        $activeWarnings = array_values(array_filter(
            $warnings,
            static fn (array $warning): bool => ! in_array($warning['status'], ['resolved', 'expired', 'false'], true),
        ));
        $saved = $this->state->isSaved($place['key']);
        $followed = $this->state->isFollowed($place['key']);
        $checkIn = $this->presentCheckIn(
            $this->state->currentCheckIn($place['key']),
        );
        $statusTone = match ($place['open_state']) {
            'open', 'appointment-only' => 'positive',
            'closing-soon', 'on-call', 'open-with-warning' => 'warning',
            default => 'neutral',
        };
        $hasApproximatePoint = is_float($place['latitude']) || is_int($place['latitude']);
        $hasApproximatePoint = $hasApproximatePoint
            && (is_float($place['longitude']) || is_int($place['longitude']));
        $mapUrl = $hasApproximatePoint
            ? 'https://www.openstreetmap.org/?mlat='.$place['latitude'].'&mlon='.$place['longitude'].'#map=14/'.$place['latitude'].'/'.$place['longitude']
            : null;
        $routeUrl = $hasApproximatePoint
            ? 'https://www.openstreetmap.org/directions?engine=fossgis_osrm_foot&route=54.687%2C25.279%3B'.$place['latitude'].'%2C'.$place['longitude']
            : null;

        return [
            ...$place,
            'detail_url' => route('places.show', ['place' => $place['key']]),
            'map_url' => $mapUrl,
            'route_url' => $routeUrl,
            'call_url' => $place['phone'] === null ? null : 'tel:'.preg_replace('/[^+0-9]/', '', $place['phone']),
            'status_tone' => $statusTone,
            'saved' => $saved,
            'followed' => $followed,
            'visited' => $this->state->hasVisited($place['key']),
            'check_in' => $checkIn,
            'warnings' => $warnings,
            'active_warnings' => $activeWarnings,
            'warning_count' => count($activeWarnings),
            'accepted_species_label' => collect($place['accepted_species'])
                ->map(static fn (string $species): string => Str::headline($species))
                ->implode(' · '),
            'distance_label' => __('presentation.kilometers', [
                'count' => $this->formatter->number((float) $place['distance_km'], 1),
            ]),
            'travel_label' => __('presentation.minutes', [
                'count' => $this->formatter->number((int) $place['travel_minutes']),
            ]),
            'rating_label' => trans_choice('presentation.rating_reviews', (int) $place['review_count'], [
                'rating' => $this->formatter->number((float) $place['rating'], 1, 1),
                'count' => $this->formatter->number((int) $place['review_count']),
            ]),
            'pet_fit' => $this->petFit($place),
            'marker_label' => __('presentation.place_marker', [
                'category' => $place['category_label'],
                'name' => $place['name'],
                'status' => $place['open_label'],
                'distance' => __('presentation.kilometers', [
                    'count' => $this->formatter->number((float) $place['distance_km'], 1),
                ]),
            ]),
            'category_tone' => $this->categoryTone((string) $place['primary_category']),
            'save_action' => [
                'label' => $saved ? __('place_directory.actions.saved') : __('place_directory.actions.save'),
                'icon' => $saved ? 'bookmark-check' : 'bookmark',
                'active' => $saved,
                'payload' => ['action' => 'toggle-place-save', 'target' => $place['key']],
            ],
            'follow_action' => [
                'label' => $followed ? __('messages.following_updates') : __('messages.follow_updates'),
                'icon' => $followed ? 'bell-ring' : 'bell',
                'active' => $followed,
                'payload' => ['action' => 'toggle-place-follow', 'target' => $place['key']],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function stateReviews(string $place): array
    {
        return array_map(function (array $review): array {
            $createdAt = $review['created_at'] ?? null;

            return [
                ...$review,
                'date' => is_string($createdAt)
                    ? $this->formatter->date(CarbonImmutable::parse($createdAt))
                    : ($review['date'] ?? null),
            ];
        }, $this->state->reviews($place));
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
            'off-leash' => Str::contains(Str::lower($place['leash_policy']), __('messages.off_leash')),
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
            'pet' => (string) ($parameters['pet'] ?? 'none'),
            'sort' => (string) ($parameters['sort'] ?? 'recommended'),
            'view' => (string) ($parameters['view'] ?? 'split'),
            'mode' => (string) ($parameters['mode'] ?? 'browse'),
            'layer' => (string) ($parameters['layer'] ?? 'places'),
            'area' => (string) ($parameters['area'] ?? __('messages.vilnius')),
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

    /** @param array<string, mixed> $filters */
    private function canUseNameSortedPage(array $filters, bool $emergency): bool
    {
        return ! $emergency
            && $filters['view'] === 'list'
            && $filters['sort'] === 'name'
            && $filters['category'] === 'all'
            && $filters['species'] === 'any'
            && $filters['size'] === 'any'
            && $filters['distance'] === 'any'
            && $filters['open_now'] === false
            && $filters['leash'] === 'any'
            && $filters['accessibility'] === 'any'
            && $filters['safety'] === 'any'
            && $filters['price'] === 'any'
            && $filters['rating'] === 'any'
            && $filters['verification'] === 'any'
            && $filters['crowd'] === 'any'
            && $filters['visit_time'] === 'any'
            && $filters['mode'] === 'browse'
            && $filters['layer'] === 'places';
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
            [[__('messages.24_hour'), '24-hour', 'emergency', 'srocn', 'kruglosut'], 'category', 'emergency-vet', __('messages.24_hour_veterinary_clinics')],
            [['clinic', 'vet', 'veterinar'], 'category', 'vet', __('messages.veterinary_clinics_lowercase')],
            [['groom', 'grumer'], 'category', 'grooming', 'grooming'],
            [[__('messages.dog_park_lowercase'), 'ploscad'], 'category', 'dog-park', __('messages.dog_parks_lowercase')],
            [['park', 'progulk'], 'category', 'park', 'parks'],
            [['cafe', 'kafe', 'terrace'], 'category', 'pet-cafe', __('messages.pet_friendly_cafes_lowercase')],
            [['bird', 'ptic', 'popug'], 'species', 'bird', __('messages.accepts_birds')],
            [['cat', 'kosk'], 'species', 'cat', __('messages.accepts_cats')],
            [[__('messages.large_dog'), 'krupn'], 'size', 'large', __('messages.large_pets')],
            [[__('messages.small_dog'), 'malenk'], 'size', 'small', __('messages.small_pets_lowercase')],
            [['quiet', 'tix', 'calm', 'spok'], 'crowd', 'low', __('messages.usually_quiet_lowercase')],
            [['evening', 'vecer'], 'visit_time', 'evening', __('messages.evening_visit')],
            [['night', 'noc'], 'visit_time', 'night', __('messages.night_visit')],
            [['fenced', 'ogoroz'], 'safety', 'fenced', __('messages.fully_fenced_lowercase')],
            [['water', 'voda'], 'safety', 'water', __('messages.water_available')],
            [['light', 'osves'], 'accessibility', 'lighting', 'lighting'],
            [[__('messages.open_now_lowercase'), 'otkryt'], 'open_now', true, __('messages.open_now_lowercase')],
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
                : __('presentation.interpreted_filters', ['filters' => implode(' · ', $labels)]),
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
    private function directorySummary(
        array $places,
        array $filters,
        bool $emergency,
        ?int $totalPlaces = null,
    ): array {
        $totalPlaces ??= count($places);
        $open = count(array_filter(
            $places,
            static fn (array $place): bool => in_array($place['open_state'], ['open', 'closing-soon', 'open-with-warning', 'on-call', 'appointment-only'], true),
        ));

        return [
            'eyebrow' => $emergency ? __('place_directory.summary.emergency_eyebrow') : __('place_directory.summary.eyebrow'),
            'title' => $emergency ? __('place_directory.summary.emergency_heading') : __('place_directory.summary.heading'),
            'description' => $emergency
                ? __('place_directory.summary.emergency_description')
                : __('place_directory.summary.description'),
            'count' => __('presentation.places_in_area', [
                'count' => trans_choice('presentation.places_count', $totalPlaces, ['count' => $totalPlaces]),
                'area' => $filters['area'],
            ]),
            'highlights' => [
                ['label' => __('place_directory.summary.items.open.label'), 'value' => (string) $open, 'detail' => __('place_directory.summary.items.open.detail')],
                ['label' => __('place_directory.summary.items.selected_pet.label'), 'value' => $this->petLabel($filters['pet']), 'detail' => __('place_directory.summary.items.selected_pet.detail')],
                ['label' => __('place_directory.summary.items.map_privacy.label'), 'value' => __('place_directory.summary.items.map_privacy.value'), 'detail' => __('place_directory.summary.items.map_privacy.detail')],
                ['label' => __('place_directory.summary.items.active_mode.label'), 'value' => $this->modeOptions()[$filters['mode']] ?? $this->modeOptions()['browse'], 'detail' => __('place_directory.summary.items.active_mode.detail')],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{
     *     current_page: int,
     *     last_page: int,
     *     total: int,
     *     summary: string,
     *     previous_url: string|null,
     *     next_url: string|null,
     *     pages: array<int, array{label: string, url: string, current: bool}>
     * }
     */
    private function pagination(
        array $parameters,
        int $currentPage,
        int $lastPage,
        int $total,
        int $pageCount,
    ): array {
        $query = array_filter(
            $parameters,
            static fn (mixed $value, string $key): bool => $key !== 'page'
                && $value !== null
                && $value !== '',
            ARRAY_FILTER_USE_BOTH,
        );
        $from = $total === 0 ? 0 : (($currentPage - 1) * self::PLACES_PER_PAGE) + 1;
        $to = $from + max(0, $pageCount - 1);

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'total' => $total,
            'summary' => __('places.pagination.summary', [
                'from' => $from,
                'to' => $to,
                'total' => $total,
            ]),
            'previous_url' => $currentPage > 1
                ? route('places.index', [...$query, 'page' => $currentPage - 1])
                : null,
            'next_url' => $currentPage < $lastPage
                ? route('places.index', [...$query, 'page' => $currentPage + 1])
                : null,
            'pages' => array_map(
                static fn (int $page): array => [
                    'label' => __('places.pagination.page', ['page' => $page]),
                    'url' => route('places.index', [...$query, 'page' => $page]),
                    'current' => $page === $currentPage,
                ],
                $this->paginationWindow($currentPage, $lastPage),
            ),
        ];
    }

    /** @return list<int> */
    private function paginationWindow(int $currentPage, int $lastPage): array
    {
        $pages = [1, $lastPage];

        for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++) {
            $pages[] = $page;
        }

        $pages = array_values(array_unique($pages));
        sort($pages);

        return $pages;
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    private function filterOptions(): array
    {
        return [
            'distance' => __('place_directory.options.filters.distance'),
            'leash' => __('place_directory.options.filters.leash'),
            'accessibility' => __('place_directory.options.filters.accessibility'),
            'safety' => __('place_directory.options.filters.safety'),
            'price' => __('place_directory.options.filters.price'),
            'rating' => __('place_directory.options.filters.rating'),
            'verification' => __('place_directory.options.filters.verification'),
            'crowd' => __('place_directory.options.filters.crowd'),
            'visit_time' => __('place_directory.options.filters.visit_time'),
        ];
    }

    /** @return array<string, string> */
    private function filterLabels(): array
    {
        return __('place_directory.options.filter_labels');
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return __('place_directory.options.sort');
    }

    /**
     * @return array<string, string>
     */
    private function viewOptions(): array
    {
        return __('place_directory.options.views');
    }

    /**
     * @return array<string, string>
     */
    private function modeOptions(): array
    {
        return __('place_directory.options.modes');
    }

    /**
     * @return array<string, string>
     */
    private function layerOptions(): array
    {
        return __('place_directory.options.layers');
    }

    private function petLabel(string $pet): string
    {
        $profile = $pet !== 'none' ? $this->profiles->pet($pet) : null;

        return $profile['name'] ?? __('place_directory.search.no_pet');
    }

    /** @return array<string, string> */
    private function petOptions(bool $includeNone = false): array
    {
        $options = collect($this->profiles->pets())->mapWithKeys(
            static fn (array $pet): array => [$pet['profile_key'] => $pet['name']],
        )->all();

        return $includeNone
            ? [...$options, 'none' => __('place_directory.search.no_pet')]
            : $options;
    }

    /**
     * @param  list<array<string, mixed>>  $warnings
     * @return list<array<string, mixed>>
     */
    private function presentWarnings(array $warnings): array
    {
        return array_map(
            static fn (array $warning): array => [
                ...$warning,
                'status_label' => Str::headline((string) $warning['status']),
            ],
            $warnings,
        );
    }

    /**
     * @param  array<string, mixed>|null  $checkIn
     * @return array<string, mixed>|null
     */
    private function presentCheckIn(?array $checkIn): ?array
    {
        if ($checkIn === null) {
            return null;
        }

        return [
            ...$checkIn,
            'visibility_label' => Str::headline((string) $checkIn['visibility']),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, string>
     */
    private function tabOptions(array $place): array
    {
        $tabs = [
            'overview' => __('messages.overview'),
            'photos' => __('messages.photos'),
            'services' => __('messages.services'),
            'rules' => __('messages.rules'),
            'hours' => __('messages.hours'),
            'reviews' => __('messages.reviews'),
            'events' => __('messages.events'),
            'questions' => __('messages.questions'),
            'map' => __('messages.map'),
            'updates' => __('messages.updates'),
            'corrections' => __('messages.corrections'),
        ];

        if (in_array($place['primary_category'], ['vet', 'emergency-vet', 'grooming'], true)) {
            $tabs = [
                ...array_slice($tabs, 0, 5, true),
                'specialists' => __('messages.specialists'),
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
                'href' => route('places.show', ['place' => $place['key'], 'tab' => $value]),
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
                    'category_label' => Str::headline($event['category']),
                    'starts_at' => $event['starts_at'],
                    'place' => $event['general_location'],
                    'status' => Str::headline($event['status']),
                    'href' => route('meetups.show', ['event' => $event['key']]),
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
                'body' => __('messages.recorded_in_the_private_place_action_history'),
                'time' => $item['created_at'],
                'icon' => 'history',
                'status' => __('messages.account_action'),
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
                'label' => $place['quiet_zone'] ? __('messages.review_pet_access') : __('messages.discuss_crowd_level'),
                'detail' => $place['quiet_zone']
                    ? __('messages.large_dogs_and_a_quiet_space_preference_are_supported')
                    : __('messages.large_dogs_are_accepted_current_crowd_conditions_still_matter'),
                'tone' => $place['quiet_zone'] ? 'positive' : 'warning',
            ];
        }

        if ($place['primary_category'] === 'grooming' && in_array('cat', $place['accepted_species'], true)) {
            return [
                'label' => __('messages.review_pet_access'),
                'detail' => __('messages.cat_handling_and_a_quiet_appointment_are_listed'),
                'tone' => 'positive',
            ];
        }

        return [
            'label' => __('messages.review_pet_access'),
            'detail' => __('messages.choose_another_pet_profile_or_contact_the_place_for_current_suitability'),
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
