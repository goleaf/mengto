<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlacePublicLocationPrecision;
use App\Enums\PlaceType;
use App\Enums\PlaceVerificationStatus;
use App\Models\Place;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

final class PlaceCatalog
{
    /** @var array<string, Place>|null */
    private ?array $canonicalPlaces = null;

    public function __construct(
        private readonly LocaleFormatter $formatter,
        private readonly AuthFactory $auth,
        private readonly PlaceMediaCatalog $media,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return array_values($this->withCanonicalAuthority($this->records()));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        $place = $this->canonicalQuery()
            ->where(function (Builder $query) use ($key): void {
                $query
                    ->where('stable_key', $key)
                    ->orWhere('slug', $key);
            })
            ->first();

        if (! $place instanceof Place) {
            return null;
        }

        return $this->withCanonicalAuthority($this->records(), [$place])[$place->stable_key];
    }

    /**
     * Database-backed list pagination for the stable name-sorted directory.
     *
     * @return array{items: list<array<string, mixed>>, total: int, current_page: int, last_page: int}
     */
    public function nameSortedPage(string $search, int $page, int $perPage): array
    {
        $query = $this->canonicalQuery();

        foreach (array_values(array_filter(preg_split('/\s+/', Str::lower(trim($search))) ?: [])) as $token) {
            $term = '%'.$token.'%';
            $query->where(function (Builder $searchQuery) use ($term): void {
                $searchQuery
                    ->where('normalized_name', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('public_region', 'like', $term)
                    ->orWhere('public_address', 'like', $term)
                    ->orWhere('catalog_category', 'like', $term);
            });
        }

        $paginator = $query
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => array_values($this->withCanonicalAuthority(
                $this->records(),
                $paginator->getCollection(),
            )),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function forCategories(array $categories, int $limit): array
    {
        $places = $this->canonicalQuery()
            ->whereIn('catalog_category', $categories)
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        return array_values($this->withCanonicalAuthority($this->records(), $places));
    }

    /**
     * Stable localized fixtures used by the environment-gated demo seeder.
     *
     * @return array<string, array<string, mixed>>
     */
    public function demoRecords(): array
    {
        return $this->records();
    }

    /**
     * @return array<string, string>
     */
    public function categoryOptions(): array
    {
        return [
            'all' => __('place_directory.options.categories.all'),
            'park' => __('place_directory.options.categories.park'),
            'dog-park' => __('place_directory.options.categories.dog-park'),
            'route' => __('place_directory.options.categories.route'),
            'vet' => __('place_directory.options.categories.vet'),
            'emergency-vet' => __('place_directory.options.categories.emergency-vet'),
            'pet-store' => __('place_directory.options.categories.pet-store'),
            'grooming' => __('place_directory.options.categories.grooming'),
            'shelter' => __('place_directory.options.categories.shelter'),
            'pet-cafe' => __('place_directory.options.categories.pet-cafe'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function speciesOptions(): array
    {
        return [
            'any' => __('place_directory.options.species.any'),
            'dog' => __('place_directory.options.species.dog'),
            'cat' => __('place_directory.options.species.cat'),
            'bird' => __('place_directory.options.species.bird'),
            'rabbit' => __('place_directory.options.species.rabbit'),
            'rodent' => __('place_directory.options.species.rodent'),
            'reptile' => __('place_directory.options.species.reptile'),
            'exotic' => __('place_directory.options.species.exotic'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function sizeOptions(): array
    {
        return [
            'any' => __('place_directory.options.sizes.any'),
            'very-small' => __('place_directory.options.sizes.very-small'),
            'small' => __('place_directory.options.sizes.small'),
            'medium' => __('place_directory.options.sizes.medium'),
            'large' => __('place_directory.options.sizes.large'),
            'very-large' => __('place_directory.options.sizes.very-large'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function iconOptions(): array
    {
        return [
            'all' => 'layout-grid',
            'park' => 'trees',
            'dog-park' => 'fence',
            'route' => 'route',
            'vet' => 'stethoscope',
            'emergency-vet' => 'siren',
            'pet-store' => 'shopping-bag',
            'grooming' => 'scissors',
            'shelter' => 'house-heart',
            'pet-cafe' => 'coffee',
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $records
     * @return array<string, array<string, mixed>>
     */
    private function withCanonicalAuthority(array $records, ?iterable $places = null): array
    {
        $authoritative = [];
        $user = $this->user();

        foreach ($places ?? $this->canonicalPlaces() as $place) {
            $stableKey = $place->stable_key;
            $record = $records[$stableKey] ?? $this->defaultRecord($place);
            $record['city'] = $place->public_region;
            $record['general_location'] = $place->public_region;
            $record['address'] = $place->public_address ?? $place->public_region;
            $record['name'] = $place->name;
            $record['summary'] = $place->summary ?? $record['summary'];
            $record['primary_category'] = $place->catalog_category
                ?? $this->categoryForType($place->type);
            $record['categories'] = array_values(array_unique([
                $record['primary_category'],
                ...($record['categories'] ?? []),
            ]));
            $record['category_label'] = $this->categoryOptions()[$record['primary_category']]
                ?? $place->type->label();
            $record['category_icon'] = $this->iconOptions()[$record['primary_category']] ?? 'map-pin';
            $record['phone'] = $place->public_phone;
            $record['website'] = $place->public_website;
            $record['email'] = $place->public_email;
            $hasApproximatePoint = $place->public_location_precision
                === PlacePublicLocationPrecision::ApproximatePoint
                && $place->public_latitude !== null
                && $place->public_longitude !== null;
            $record['latitude'] = $hasApproximatePoint ? (float) $place->public_latitude : null;
            $record['longitude'] = $hasApproximatePoint ? (float) $place->public_longitude : null;
            $record['public_location_precision'] = $hasApproximatePoint
                ? PlacePublicLocationPrecision::ApproximatePoint->value
                : PlacePublicLocationPrecision::Region->value;
            $record['accepted_species'] = $place->species_rules === null
                || $place->species_rules === []
                    ? $record['accepted_species']
                    : array_values($place->species_rules);
            $record['wheelchair_access'] = $place->accessibility_status
                === PlaceAccessibilityStatus::Confirmed;
            $record['owner_managed'] = $user !== null && $place->isManagedBy($user);
            $record['slug'] = $place->slug;
            $record['verification'] = [
                'label' => $place->verification_status->label(),
                'scope' => __('places.presentation.public_information_scope'),
                'updated_at' => $place->updated_at->toDateString(),
                'tone' => match ($place->verification_status) {
                    PlaceVerificationStatus::Verified => 'verified',
                    PlaceVerificationStatus::OrganizerProvided,
                    PlaceVerificationStatus::VenueConfirmed,
                    PlaceVerificationStatus::OrganizationConfirmed => 'community',
                    default => 'neutral',
                },
            ];
            $record['data_freshness'] = __('places.presentation.information_current', [
                'date' => $this->formatter->date($place->updated_at),
            ]);
            $authoritative[$stableKey] = $record;
        }

        return $authoritative;
    }

    /**
     * @return array<string, Place>
     */
    private function canonicalPlaces(): array
    {
        if ($this->canonicalPlaces !== null) {
            return $this->canonicalPlaces;
        }

        $query = $this->canonicalQuery()
            ->limit(500)
            ->orderBy('id');

        $this->canonicalPlaces = $query
            ->get()
            ->keyBy('stable_key')
            ->all();

        return $this->canonicalPlaces;
    }

    /** @return Builder<Place> */
    private function canonicalQuery(): Builder
    {
        $user = $this->user();
        $query = Place::query()
            ->select([
                'id',
                'owner_user_id',
                'organization_id',
                'stable_key',
                'slug',
                'name',
                'summary',
                'type',
                'catalog_category',
                'visibility',
                'status',
                'public_region',
                'public_address',
                'public_phone',
                'public_website',
                'public_email',
                'public_latitude',
                'public_longitude',
                'public_location_precision',
                'is_indoor',
                'verification_status',
                'accessibility_status',
                'accessibility_facts',
                'parking_information',
                'pet_rules',
                'species_rules',
                'archived_at',
                'updated_at',
            ])
            ->with([
                'organization:id,status,archived_at',
                'organization.memberships' => static function (Relation $memberships) use ($user): void {
                    $memberships
                        ->select([
                            'id',
                            'organization_id',
                            'user_id',
                            'role',
                            'status',
                            'expires_at',
                            'removed_at',
                        ])
                        ->where('user_id', $user?->id);
                },
            ]);

        if ($user === null) {
            $query->publiclyDiscoverable();
        } else {
            $query->accessibleTo($user);
        }

        return $query;
    }

    /** @return array<string, mixed> */
    private function defaultRecord(Place $place): array
    {
        $category = $place->catalog_category ?? $this->categoryForType($place->type);
        $rules = filled($place->pet_rules)
            ? [trim((string) $place->pet_rules)]
            : [__('places.presentation.rules_pending')];
        $species = $place->species_rules === null || $place->species_rules === []
            ? ['dog', 'cat']
            : array_values($place->species_rules);

        return [
            'key' => $place->stable_key,
            'slug' => $place->slug,
            'name' => $place->name,
            'short_name' => $place->name,
            'primary_category' => $category,
            'categories' => [$category],
            'category_label' => $this->categoryOptions()[$category] ?? $place->type->label(),
            'category_icon' => $this->iconOptions()[$category] ?? 'map-pin',
            'summary' => $place->summary ?? __('places.presentation.summary_pending'),
            'city' => $place->public_region,
            'neighborhood' => $place->public_region,
            'address' => $place->public_address ?? $place->public_region,
            'general_location' => $place->public_region,
            'latitude' => $place->public_location_precision === PlacePublicLocationPrecision::ApproximatePoint
                && $place->public_latitude !== null
                ? (float) $place->public_latitude
                : null,
            'longitude' => $place->public_location_precision === PlacePublicLocationPrecision::ApproximatePoint
                && $place->public_longitude !== null
                ? (float) $place->public_longitude
                : null,
            'public_location_precision' => $place->public_location_precision?->value
                ?? PlacePublicLocationPrecision::Region->value,
            'map_x' => 50,
            'map_y' => 50,
            'coordinate_accuracy' => $place->public_location_precision !== PlacePublicLocationPrecision::ApproximatePoint
                ? __('places.presentation.manual_public_location')
                : __('places.presentation.approximate_public_coordinates'),
            'distance_km' => 0.0,
            'travel_minutes' => 0,
            'open_state' => 'unknown',
            'open_label' => __('places.presentation.hours_unconfirmed'),
            'closes_at' => null,
            'hours_summary' => __('places.presentation.hours_unconfirmed'),
            'special_hours' => __('places.presentation.call_before_travel'),
            'phone' => $place->public_phone,
            'website' => $place->public_website,
            'email' => $place->public_email,
            'accepted_species' => $species,
            'accepted_sizes' => ['very-small', 'small', 'medium', 'large', 'very-large'],
            'leash_policy' => $rules[0],
            'fenced' => false,
            'water' => false,
            'lighting' => false,
            'quiet_zone' => false,
            'parking' => filled($place->parking_information),
            'wheelchair_access' => $place->accessibility_status === PlaceAccessibilityStatus::Confirmed,
            'price_level' => 'free',
            'crowd_level' => 'unknown',
            'crowd_label' => __('places.presentation.crowd_unknown'),
            'noise_level' => __('places.presentation.not_assessed'),
            'rules' => $rules,
            'features' => array_values($place->accessibility_facts ?? []),
            'accessibility' => array_values($place->accessibility_facts ?? []),
            'safety' => [__('places.presentation.conditions_unconfirmed')],
            'services' => [],
            'pricing' => [],
            'rating' => 0.0,
            'review_count' => 0,
            'verified_review_count' => 0,
            'verification' => [],
            'data_freshness' => '',
            'recommendation_reason' => __('places.presentation.community_submission_reason'),
            'sponsored' => false,
            'allow_events' => true,
            'owner_managed' => false,
            'emergency' => $category === 'emergency-vet',
            ...$this->media->primary($category),
            'image_alt' => __('places.presentation.default_image_alt', ['name' => $place->name]),
            'route' => null,
            'events' => [],
            'base_warnings' => [],
        ];
    }

    private function categoryForType(PlaceType $type): string
    {
        return match ($type) {
            PlaceType::Park => 'park',
            PlaceType::WalkingRoute => 'route',
            PlaceType::VeterinaryClinic => 'vet',
            PlaceType::Shelter => 'shelter',
            default => 'park',
        };
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function records(): array
    {
        $dogSizes = ['very-small', 'small', 'medium', 'large', 'very-large'];
        $commonPets = ['dog', 'cat'];
        $allCompanions = ['dog', 'cat', 'bird', 'rabbit', 'rodent', 'reptile', 'exotic'];

        return [
            'vingis-quiet-loop' => [
                'key' => 'vingis-quiet-loop',
                'name' => __('messages.vingis_park_quiet_loop'),
                'short_name' => __('messages.vingis_park'),
                'primary_category' => 'park',
                'categories' => ['park', 'route'],
                'category_label' => __('messages.park_and_walking_route'),
                'category_icon' => 'trees',
                'summary' => __('messages.a_broad_tree_lined_loop_with_calmer_outer_paths_water_points_and_room_for_parallel_walking'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.naujamiestis'),
                'address' => __('messages.m_k_čiurlionio_g_100_vilnius'),
                'general_location' => __('messages.vingis_park_western_entrances'),
                'latitude' => 54.6834,
                'longitude' => 25.2368,
                'map_x' => 38,
                'map_y' => 45,
                'coordinate_accuracy' => __('messages.main_public_entrance'),
                'distance_km' => 3.8,
                'travel_minutes' => 14,
                'open_state' => 'open',
                'open_label' => __('messages.open_now'),
                'closes_at' => __('messages.open_all_day'),
                'hours_summary' => __('messages.public_paths_open_all_day_lighting_varies_by_entrance'),
                'special_hours' => __('messages.event_closures_are_posted_as_temporary_updates'),
                'phone' => null,
                'website' => 'https://vilnius.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_outside_signed_off_leash_areas'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.usually_calmer_before_9_00_am_and_after_8_00_pm'),
                'noise_level' => __('messages.mixed_quieter_on_the_western_loop'),
                'rules' => [
                    __('messages.keep_pets_leashed_outside_signed_areas'),
                    __('messages.use_extra_distance_near_cyclists_and_event_crowds'),
                    __('messages.group_events_must_respect_municipal_park_rules'),
                ],
                'features' => [__('messages.wide_paths'), 'water', 'shade', 'benches', 'bins', __('messages.evening_lighting')],
                'accessibility' => [__('messages.step_free_routes'), __('messages.wide_paths'), __('messages.accessible_parking'), __('messages.rest_areas')],
                'safety' => [__('messages.separate_from_main_roads'), __('messages.several_early_exit_points'), __('messages.nearby_public_transport')],
                'services' => [__('messages.4_6_km_quiet_loop'), __('messages.2_1_km_shortcut'), __('messages.water_points'), __('messages.event_meeting_areas')],
                'pricing' => ['Park access' => __('messages.free')],
                'rating' => 4.7,
                'review_count' => 184,
                'verified_review_count' => 61,
                'verification' => [
                    'label' => __('messages.rules_checked_from_a_public_source'),
                    'scope' => __('messages.address_and_general_park_rules'),
                    'updated_at' => '2026-07-27',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.community_conditions_checked_2_hours_ago'),
                'recommendation_reason' => __('messages.best_match_for_a_calm_evening_walk_with_scout'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.broad_shaded_path_through_a_green_city_park'),
                'route' => [
                    'distance' => __('messages.4_6_km'),
                    'duration' => __('messages.65_85_min'),
                    'difficulty' => __('messages.easy'),
                    'surface' => __('messages.paved_and_compact_gravel'),
                    'elevation' => __('messages.mostly_level'),
                    'shortcuts' => [__('messages.2_1_km_riverside_return'), __('messages.bus_stop_exit_after_3_km')],
                    'hazards' => [__('messages.busy_cycle_crossing_near_the_main_avenue'), __('messages.mud_on_unpaved_edges_after_rain')],
                    'home_privacy' => __('messages.starts_at_a_public_park_entrance_no_home_point_is_stored'),
                ],
                'events' => ['small-dog-social'],
                'base_warnings' => [],
            ],
            'bernardine-evening-park' => [
                'key' => 'bernardine-evening-park',
                'name' => __('messages.bernardine_garden_evening_paths'),
                'short_name' => __('messages.bernardine_garden'),
                'primary_category' => 'park',
                'categories' => ['park'],
                'category_label' => __('messages.city_park'),
                'category_icon' => 'trees',
                'summary' => __('messages.a_central_well_lit_garden_with_smooth_paths_and_easy_transit_but_more_visitors_and_no_dedicated_dog_water'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.old_town'),
                'address' => __('messages.b_radvilaitės_g_8a_vilnius'),
                'general_location' => __('messages.bernardine_garden_old_town'),
                'latitude' => 54.6849,
                'longitude' => 25.2951,
                'map_x' => 66,
                'map_y' => 36,
                'coordinate_accuracy' => __('messages.main_public_entrance'),
                'distance_km' => 1.6,
                'travel_minutes' => 8,
                'open_state' => 'closing-soon',
                'open_label' => __('messages.closes_at_10_00_pm'),
                'closes_at' => __('messages.10_00_pm'),
                'hours_summary' => __('messages.daily_7_00_am_10_00_pm'),
                'special_hours' => __('messages.seasonal_closing_time_may_change'),
                'phone' => null,
                'website' => 'https://vilnius.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_throughout_the_garden'),
                'fenced' => false,
                'water' => false,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'high',
                'crowd_label' => __('messages.often_busy_with_families_and_cyclists_before_sunset'),
                'noise_level' => __('messages.moderate_to_busy'),
                'rules' => [
                    __('messages.pets_stay_on_leash'),
                    __('messages.keep_away_from_planted_beds_and_playground_entrances'),
                    __('messages.bring_drinking_water_for_your_pet'),
                ],
                'features' => ['lighting', __('messages.smooth_paths'), 'benches', 'bins', __('messages.public_transport_lowercase')],
                'accessibility' => [__('messages.step_free_main_paths'), __('messages.frequent_benches'), __('messages.nearby_bus_stops')],
                'safety' => [__('messages.good_path_visibility'), __('messages.staffed_during_opening_hours')],
                'services' => [__('messages.1_3_km_garden_loop'), __('messages.rest_areas'), __('messages.nearby_pet_friendly_cafes')],
                'pricing' => ['Park access' => __('messages.free')],
                'rating' => 4.5,
                'review_count' => 129,
                'verified_review_count' => 44,
                'verification' => [
                    'label' => __('messages.official_location'),
                    'scope' => __('messages.address_and_opening_hours'),
                    'updated_at' => '2026-07-25',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.hours_checked_5_days_ago'),
                'recommendation_reason' => __('messages.closest_well_lit_option_but_bring_water_and_expect_more_people'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.formal_city_garden_with_broad_paved_walking_paths'),
                'events' => [],
                'base_warnings' => [],
            ],
            'pavilniai-calm-trail' => [
                'key' => 'pavilniai-calm-trail',
                'name' => __('messages.pavilniai_calm_forest_trail'),
                'short_name' => __('messages.pavilniai_trail'),
                'primary_category' => 'park',
                'categories' => ['park', 'route'],
                'category_label' => __('messages.forest_park_and_route'),
                'category_icon' => 'trees',
                'summary' => __('messages.the_quietest_option_with_wide_forest_sections_and_shade_though_it_is_farther_away_and_less_consistently_lit'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.pavilniai'),
                'address' => __('messages.belmonto_g_vilnius'),
                'general_location' => __('messages.pavilniai_regional_park_belmontas_side'),
                'latitude' => 54.6817,
                'longitude' => 25.3577,
                'map_x' => 84,
                'map_y' => 48,
                'coordinate_accuracy' => __('messages.general_trail_entrance'),
                'distance_km' => 7.4,
                'travel_minutes' => 22,
                'open_state' => 'open',
                'open_label' => __('messages.open_now'),
                'closes_at' => __('messages.natural_area'),
                'hours_summary' => __('messages.open_access_daylight_visits_recommended'),
                'special_hours' => __('messages.trails_may_close_during_storms_or_maintenance'),
                'phone' => null,
                'website' => 'https://saugoma.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_around_wildlife_and_shared_paths'),
                'fenced' => false,
                'water' => true,
                'lighting' => false,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => false,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.usually_quiet_outside_weekend_afternoons'),
                'noise_level' => __('messages.low'),
                'rules' => [
                    __('messages.keep_pets_controlled_near_wildlife'),
                    __('messages.stay_on_marked_trails'),
                    __('messages.carry_water_and_a_charged_phone'),
                ],
                'features' => [__('messages.forest_shade'), __('messages.natural_water'), __('messages.quiet_paths'), 'parking', __('messages.shortcut_loop')],
                'accessibility' => [__('messages.uneven_natural_surface'), __('messages.limited_step_free_access')],
                'safety' => [__('messages.mobile_reception_varies'), __('messages.unlit_after_dusk'), __('messages.seasonal_tick_activity_lowercase')],
                'services' => [__('messages.5_2_km_forest_loop'), __('messages.2_8_km_shortcut'), 'viewpoints'],
                'pricing' => ['Trail access' => __('messages.free')],
                'rating' => 4.8,
                'review_count' => 76,
                'verified_review_count' => 23,
                'verification' => [
                    'label' => __('messages.community_mapped'),
                    'scope' => __('messages.trail_entrance_and_conditions'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.trail_conditions_checked_yesterday'),
                'recommendation_reason' => __('messages.calmest_match_with_shade_and_water_but_farther_and_unlit'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.quiet_forest_path_beneath_tall_green_trees'),
                'route' => [
                    'distance' => __('messages.5_2_km'),
                    'duration' => __('messages.80_100_min'),
                    'difficulty' => __('messages.moderate'),
                    'surface' => __('messages.forest_soil_roots_and_compact_gravel'),
                    'elevation' => __('messages.several_short_climbs'),
                    'shortcuts' => [__('messages.2_8_km_lower_loop'), __('messages.turnaround_viewpoint_at_1_7_km')],
                    'hazards' => ['ticks', __('messages.mud_after_rain'), __('messages.limited_lighting'), __('messages.variable_mobile_signal')],
                    'home_privacy' => __('messages.the_shared_route_starts_at_a_public_trail_entrance'),
                ],
                'events' => [],
                'base_warnings' => [
                    [
                        'key' => 'pavilniai-ticks',
                        'title' => __('messages.seasonal_tick_activity'),
                        'category' => 'parasites',
                        'status' => 'official-source',
                        'detail' => __('messages.check_pets_after_leaving_tall_grass_and_forest_edges'),
                        'reported_at' => '2026-07-28T09:00:00+03:00',
                        'expires_at' => '2026-09-30T23:59:00+03:00',
                        'confirmations' => 12,
                        'source' => __('messages.seasonal_public_guidance'),
                    ],
                ],
            ],
            'zverynas-small-dog-run' => [
                'key' => 'zverynas-small-dog-run',
                'name' => __('messages.žvėrynas_neighborhood_dog_run'),
                'short_name' => __('messages.žvėrynas_dog_run'),
                'primary_category' => 'dog-park',
                'categories' => ['dog-park'],
                'category_label' => __('messages.fenced_dog_park'),
                'category_icon' => 'fence',
                'summary' => __('messages.a_free_fenced_run_with_separate_small_dog_space_double_gate_water_and_a_temporary_latch_warning'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.žvėrynas'),
                'address' => __('messages.birutės_g_green_area_vilnius'),
                'general_location' => __('messages.žvėrynas_birutės_street_green_area'),
                'latitude' => 54.6952,
                'longitude' => 25.2474,
                'map_x' => 43,
                'map_y' => 28,
                'coordinate_accuracy' => __('messages.community_confirmed_entrance'),
                'distance_km' => 2.7,
                'travel_minutes' => 11,
                'open_state' => 'open-with-warning',
                'open_label' => __('messages.open_active_warning'),
                'closes_at' => __('messages.10_00_pm'),
                'hours_summary' => __('messages.daily_7_00_am_10_00_pm'),
                'special_hours' => __('messages.lighting_is_limited_after_9_00_pm'),
                'phone' => null,
                'website' => null,
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.off_leash_inside_the_signed_fenced_zones'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.a_few_dogs_reported_35_minutes_ago'),
                'noise_level' => __('messages.usually_calm_before_workdays'),
                'rules' => [
                    __('messages.use_the_zone_that_matches_your_dog_s_size_and_comfort'),
                    __('messages.keep_the_entrance_clear_and_close_both_gates'),
                    __('messages.remove_food_if_it_creates_tension'),
                ],
                'features' => [__('messages.fully_fenced_lowercase'), __('messages.double_gate'), __('messages.small_dog_zone'), __('messages.quiet_zone_lowercase'), 'water', 'benches'],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.firm_surface_to_the_small_dog_zone')],
                'safety' => [__('messages.1_5_m_fence'), __('messages.double_gate'), __('messages.separate_size_zones')],
                'services' => [__('messages.small_dog_zone'), __('messages.general_run'), __('messages.training_corner'), __('messages.water_point')],
                'pricing' => ['General access' => __('messages.free')],
                'rating' => 4.4,
                'review_count' => 58,
                'verified_review_count' => 19,
                'verification' => [
                    'label' => __('messages.community_verified'),
                    'scope' => __('messages.entrance_fence_zones_and_water'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.entrance_status_checked_3_hours_ago'),
                'recommendation_reason' => __('messages.matches_a_small_dog_double_gate_water_and_quiet_zone_search'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('dog-park'),
                'image_alt' => __('messages.small_dog_standing_on_grass_inside_a_fenced_exercise_area'),
                'events' => [],
                'base_warnings' => [
                    [
                        'key' => 'zverynas-gate-latch',
                        'title' => __('messages.small_dog_gate_latch_is_loose'),
                        'category' => 'damaged-fence',
                        'status' => 'confirmed',
                        'detail' => __('messages.hold_the_inner_latch_closed_while_another_person_enters'),
                        'reported_at' => '2026-07-30T08:15:00+03:00',
                        'expires_at' => '2026-08-02T08:15:00+03:00',
                        'confirmations' => 4,
                        'source' => __('messages.visitor_photo_and_community_confirmations'),
                    ],
                ],
            ],
            'naujininkai-secure-dog-field' => [
                'key' => 'naujininkai-secure-dog-field',
                'name' => __('messages.naujininkai_secure_dog_field'),
                'short_name' => __('messages.naujininkai_dog_field'),
                'primary_category' => 'dog-park',
                'categories' => ['dog-park'],
                'category_label' => __('messages.fenced_dog_park'),
                'category_icon' => 'fence',
                'summary' => __('messages.a_quieter_fully_fenced_field_with_double_gates_separate_zones_working_water_and_no_active_hazards'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.naujininkai'),
                'address' => __('messages.dzūkų_g_green_area_vilnius'),
                'general_location' => __('messages.naujininkai_dzūkų_street'),
                'latitude' => 54.6595,
                'longitude' => 25.2782,
                'map_x' => 58,
                'map_y' => 73,
                'coordinate_accuracy' => __('messages.community_confirmed_entrance'),
                'distance_km' => 4.9,
                'travel_minutes' => 17,
                'open_state' => 'open',
                'open_label' => __('messages.open_now'),
                'closes_at' => __('messages.9_00_pm'),
                'hours_summary' => __('messages.daily_7_00_am_9_00_pm'),
                'special_hours' => __('messages.water_may_be_turned_off_during_freezing_weather'),
                'phone' => null,
                'website' => null,
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.off_leash_inside_closed_zones'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.usually_quiet_no_recent_crowd_report'),
                'noise_level' => __('messages.low_to_moderate'),
                'rules' => [
                    __('messages.use_separate_size_zones'),
                    __('messages.close_both_gates_before_removing_the_leash'),
                    __('messages.take_toys_away_if_another_dog_guards_them'),
                ],
                'features' => [__('messages.fully_fenced_lowercase'), __('messages.double_gate'), __('messages.small_dog_zone'), 'water', 'lighting', 'parking'],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.firm_central_path'), __('messages.nearby_parking_lowercase')],
                'safety' => [__('messages.1_7_m_fence'), __('messages.double_gate'), __('messages.no_active_warnings_lowercase')],
                'services' => [__('messages.small_dog_zone'), __('messages.large_dog_zone'), __('messages.training_equipment'), __('messages.water_point')],
                'pricing' => ['General access' => __('messages.free')],
                'rating' => 4.6,
                'review_count' => 34,
                'verified_review_count' => 14,
                'verification' => [
                    'label' => __('messages.community_verified'),
                    'scope' => __('messages.fence_entrances_zones_and_facilities'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.no_hazards_reported_in_the_last_7_days'),
                'recommendation_reason' => __('messages.safer_current_alternative_with_the_same_small_dog_features'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('dog-park'),
                'image_alt' => __('messages.dogs_standing_in_a_spacious_grassy_fenced_field'),
                'events' => ['small-dog-social'],
                'base_warnings' => [],
            ],
            'paws-24-veterinary-center' => [
                'key' => 'paws-24-veterinary-center',
                'name' => __('messages.paws_24_veterinary_center'),
                'short_name' => __('messages.paws_24'),
                'primary_category' => 'emergency-vet',
                'categories' => ['vet', 'emergency-vet', 'pet-store'],
                'category_label' => __('messages.24_hour_veterinary_center'),
                'category_icon' => 'siren',
                'summary' => __('messages.a_fictional_demo_emergency_clinic_accepting_birds_and_companion_animals_with_overnight_diagnostics_and_an_on_site_clinician'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.šnipiškės'),
                'address' => __('messages.demo_address_konstitucijos_pr_vilnius'),
                'general_location' => __('messages.šnipiškės_konstitucijos_avenue'),
                'latitude' => 54.7015,
                'longitude' => 25.2685,
                'map_x' => 51,
                'map_y' => 21,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 2.2,
                'travel_minutes' => 9,
                'open_state' => 'open',
                'open_label' => __('messages.open_24_hours'),
                'closes_at' => __('messages.open_all_night'),
                'hours_summary' => __('messages.emergency_reception_24_7_call_before_travel'),
                'special_hours' => __('messages.overnight_diagnostics_and_surgery_depend_on_the_on_call_team'),
                'phone' => '+370 600 00024',
                'website' => null,
                'email' => 'demo-emergency@pawcircle.example',
                'accepted_species' => $allCompanions,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_birds_and_small_pets_in_secure_carriers'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.approximate_wait_call_for_current_triage_status'),
                'noise_level' => __('messages.separate_quiet_waiting_area_available'),
                'rules' => [
                    __('messages.call_before_travel_when_possible'),
                    __('messages.use_a_secure_carrier_for_birds_and_small_animals'),
                    __('messages.emergency_cases_are_triaged_by_clinical_need'),
                ],
                'features' => [__('messages.24_hour_clinician'), __('messages.overnight_diagnostics'), 'surgery', __('messages.inpatient_care'), __('messages.separate_waiting')],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.accessible_parking'), __('messages.wide_doors'), __('messages.wait_in_car_option')],
                'safety' => [__('messages.separate_dog_and_cat_waiting'), __('messages.isolation_room'), __('messages.on_site_inpatient_team')],
                'services' => [__('messages.emergency_triage_lowercase'), 'x-ray', 'ultrasound', 'laboratory', 'surgery', __('messages.avian_care'), __('messages.inpatient_care')],
                'pricing' => ['Emergency assessment' => __('messages.from_65'), 'Night surcharge' => __('messages.shown_before_treatment')],
                'rating' => 4.6,
                'review_count' => 92,
                'verified_review_count' => 37,
                'verification' => [
                    'label' => __('messages.demo_business_profile'),
                    'scope' => __('messages.prototype_contact_hours_and_species_data'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.emergency_hours_confirmed_in_demo_data_today'),
                'recommendation_reason' => __('messages.open_now_accepts_birds_and_is_the_fastest_suitable_emergency_option'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => true,
                ...$this->media->primary('emergency-vet'),
                'image_alt' => __('messages.veterinary_clinician_examining_a_dog_in_a_bright_treatment_room'),
                'events' => ['travel-ready-webinar'],
                'base_warnings' => [],
            ],
            'night-paw-clinic' => [
                'key' => 'night-paw-clinic',
                'name' => __('messages.night_paw_veterinary_clinic'),
                'short_name' => __('messages.night_paw_clinic'),
                'primary_category' => 'emergency-vet',
                'categories' => ['vet', 'emergency-vet'],
                'category_label' => __('messages.overnight_veterinary_clinic'),
                'category_icon' => 'siren',
                'summary' => __('messages.a_fictional_demo_overnight_clinic_for_dogs_and_cats_it_does_not_accept_birds_or_exotic_pets'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.naujamiestis'),
                'address' => __('messages.demo_address_švitrigailos_g_vilnius'),
                'general_location' => __('messages.naujamiestis_švitrigailos_street'),
                'latitude' => 54.6714,
                'longitude' => 25.2684,
                'map_x' => 50,
                'map_y' => 58,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 1.9,
                'travel_minutes' => 7,
                'open_state' => 'on-call',
                'open_label' => __('messages.emergency_intake_by_phone'),
                'closes_at' => __('messages.on_call_overnight'),
                'hours_summary' => __('messages.8_00_pm_8_00_am_phone_confirmation_required'),
                'special_hours' => __('messages.only_dogs_and_cats_are_accepted_overnight'),
                'phone' => '+370 600 00091',
                'website' => null,
                'email' => 'demo-night@pawcircle.example',
                'accepted_species' => $commonPets,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_in_secure_carriers'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'unknown',
                'crowd_label' => __('messages.no_current_wait_information'),
                'noise_level' => __('messages.shared_waiting_area'),
                'rules' => [__('messages.call_before_travel'), __('messages.birds_and_exotic_pets_are_not_accepted'), __('messages.emergency_cases_are_triaged')],
                'features' => [__('messages.overnight_intake'), 'x-ray', 'laboratory', __('messages.surgery_on_call')],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.parking_close_to_entrance')],
                'safety' => [__('messages.secure_carrier_required_for_cats')],
                'services' => [__('messages.emergency_triage_lowercase'), 'x-ray', 'laboratory', __('messages.basic_surgery')],
                'pricing' => ['Night assessment' => __('messages.from_55')],
                'rating' => 4.2,
                'review_count' => 47,
                'verified_review_count' => 15,
                'verification' => [
                    'label' => __('messages.demo_contact_confirmed'),
                    'scope' => __('messages.prototype_hours_and_accepted_species'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.on_call_schedule_checked_yesterday'),
                'recommendation_reason' => __('messages.closer_but_unsuitable_for_birds_and_requires_phone_confirmation'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => true,
                ...$this->media->primary('emergency-vet'),
                'image_alt' => __('messages.dog_waiting_calmly_in_a_veterinary_reception_area'),
                'events' => [],
                'base_warnings' => [],
            ],
            'green-paw-neighborhood-clinic' => [
                'key' => 'green-paw-neighborhood-clinic',
                'name' => __('messages.green_paw_neighborhood_clinic'),
                'short_name' => __('messages.green_paw_clinic'),
                'primary_category' => 'vet',
                'categories' => ['vet'],
                'category_label' => __('messages.veterinary_clinic'),
                'category_icon' => 'stethoscope',
                'summary' => __('messages.a_fictional_demo_daytime_clinic_with_online_request_boundary_separate_waiting_spaces_and_clear_price_ranges'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.antakalnis'),
                'address' => __('messages.demo_address_antakalnio_g_vilnius'),
                'general_location' => __('messages.antakalnis_near_the_clinic_district'),
                'latitude' => 54.7068,
                'longitude' => 25.3147,
                'map_x' => 72,
                'map_y' => 18,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 4.4,
                'travel_minutes' => 16,
                'open_state' => 'closed',
                'open_label' => __('messages.closed_opens_8_00_am'),
                'closes_at' => __('messages.opens_8_00_am'),
                'hours_summary' => __('messages.mon_fri_8_00_am_8_00_pm_sat_9_00_am_3_00_pm'),
                'special_hours' => __('messages.appointments_are_recommended'),
                'phone' => '+370 600 00118',
                'website' => null,
                'email' => 'demo-clinic@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit', 'rodent'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_other_pets_in_secure_carriers'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.appointments_reduce_waiting_live_availability_is_not_connected'),
                'noise_level' => __('messages.separate_cat_waiting_room'),
                'rules' => [__('messages.book_or_call_before_arrival'), __('messages.bring_only_selected_documents'), __('messages.use_a_secure_carrier_when_appropriate')],
                'features' => [__('messages.separate_waiting'), 'rehabilitation', 'laboratory', 'dentistry', 'parking'],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.wide_doors'), __('messages.accessible_parking'), __('messages.wait_in_car_option')],
                'safety' => [__('messages.separate_dog_and_cat_waiting'), __('messages.isolation_room')],
                'services' => ['consultation', 'vaccination', 'laboratory', 'ultrasound', 'dentistry', 'rehabilitation'],
                'pricing' => ['Consultation' => '€35–€55', 'Ultrasound' => __('messages.from_45')],
                'rating' => 4.8,
                'review_count' => 66,
                'verified_review_count' => 31,
                'verification' => [
                    'label' => __('messages.demo_business_profile'),
                    'scope' => __('messages.prototype_address_hours_and_services'),
                    'updated_at' => '2026-07-28',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.services_checked_2_days_ago'),
                'recommendation_reason' => __('messages.highly_rated_daytime_option_with_a_quiet_waiting_area'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('vet'),
                'image_alt' => __('messages.veterinary_professional_listening_to_a_dog_with_a_stethoscope'),
                'events' => ['travel-ready-webinar'],
                'base_warnings' => [],
            ],
            'quiet-whiskers-grooming' => [
                'key' => 'quiet-whiskers-grooming',
                'name' => __('messages.quiet_whiskers_grooming_studio'),
                'short_name' => __('messages.quiet_whiskers'),
                'primary_category' => 'grooming',
                'categories' => ['grooming'],
                'category_label' => __('messages.low_stress_grooming_studio'),
                'category_icon' => 'scissors',
                'summary' => __('messages.a_fictional_individual_appointment_studio_for_cats_and_dogs_with_quiet_drying_breaks_and_private_care_notes'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.žirmūnai'),
                'address' => __('messages.demo_address_žirmūnų_g_vilnius'),
                'general_location' => __('messages.žirmūnai_near_the_river'),
                'latitude' => 54.7182,
                'longitude' => 25.3014,
                'map_x' => 66,
                'map_y' => 10,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 4.1,
                'travel_minutes' => 15,
                'open_state' => 'open',
                'open_label' => __('messages.open_next_demo_slot_thu'),
                'closes_at' => __('messages.7_00_pm'),
                'hours_summary' => __('messages.tue_sat_9_00_am_7_00_pm_individual_appointments'),
                'special_hours' => __('messages.quiet_cat_appointments_are_held_before_noon'),
                'phone' => '+370 600 00207',
                'website' => null,
                'email' => 'demo-grooming@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_and_rabbits_in_secure_carriers'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.one_family_at_a_time'),
                'noise_level' => __('messages.quiet_dryer_and_no_force_drying_options'),
                'rules' => [__('messages.share_handling_concerns_privately'), __('messages.public_before_and_after_photos_require_separate_consent'), __('messages.breaks_are_available')],
                'features' => [__('messages.cat_grooming'), __('messages.quiet_dryer'), __('messages.individual_appointments'), 'breaks', __('messages.senior_pet_handling')],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.parking_nearby'), __('messages.seated_handover_area')],
                'safety' => [__('messages.one_family_at_a_time_lowercase'), __('messages.private_handling_notes'), __('messages.owner_can_remain_by_agreement')],
                'services' => [__('messages.cat_grooming'), __('messages.bath_and_brush'), __('messages.nail_care'), 'de-shedding', __('messages.quiet_drying'), __('messages.senior_pet_care')],
                'pricing' => ['Cat grooming' => __('messages.from_45'), 'Quiet-dry session' => __('messages.from_55')],
                'rating' => 4.9,
                'review_count' => 41,
                'verified_review_count' => 29,
                'verification' => [
                    'label' => __('messages.demo_specialist_profile'),
                    'scope' => __('messages.prototype_identity_services_and_studio_access'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.demo_availability_updated_yesterday'),
                'recommendation_reason' => __('messages.works_with_cats_offers_quiet_drying_and_schedules_one_family_at_a_time'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('grooming'),
                'image_alt' => __('messages.quiet_pet_grooming_workspace_with_clean_equipment'),
                'events' => [],
                'base_warnings' => [],
            ],
            'old-town-pet-cafe' => [
                'key' => 'old-town-pet-cafe',
                'name' => __('messages.courtyard_paws_cafe'),
                'short_name' => __('messages.courtyard_paws'),
                'primary_category' => 'pet-cafe',
                'categories' => ['pet-cafe'],
                'category_label' => __('messages.pet_friendly_cafe'),
                'category_icon' => 'coffee',
                'summary' => __('messages.a_fictional_quiet_hours_cafe_where_pets_are_currently_allowed_on_the_terrace_only'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.old_town'),
                'address' => __('messages.demo_address_pilies_g_vilnius'),
                'general_location' => __('messages.old_town_pilies_street_courtyard'),
                'latitude' => 54.6812,
                'longitude' => 25.2894,
                'map_x' => 63,
                'map_y' => 48,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 1.3,
                'travel_minutes' => 7,
                'open_state' => 'open',
                'open_label' => __('messages.open_terrace_until_9_00_pm'),
                'closes_at' => __('messages.9_00_pm'),
                'hours_summary' => __('messages.daily_8_00_am_9_00_pm'),
                'special_hours' => __('messages.pets_are_terrace_only_after_the_rule_change_on_july_30'),
                'phone' => '+370 600 00316',
                'website' => null,
                'email' => 'demo-cafe@pawcircle.example',
                'accepted_species' => ['dog', 'cat'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_or_secure_carrier_required'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.quietest_8_00_10_00_am_and_after_7_30_pm'),
                'noise_level' => __('messages.low_during_marked_quiet_hours'),
                'rules' => [__('messages.pets_are_allowed_on_the_terrace_only'), __('messages.keep_pets_on_leash_or_in_a_carrier'), __('messages.do_not_feed_unfamiliar_pets')],
                'features' => ['terrace', __('messages.water_bowls'), __('messages.quiet_hours'), __('messages.step_free_courtyard')],
                'accessibility' => [__('messages.step_free_terrace'), __('messages.accessible_restroom_nearby'), __('messages.seating_with_aisle_space')],
                'safety' => [__('messages.separate_corner_tables'), __('messages.no_pet_food_presented_as_veterinary_diet')],
                'services' => [__('messages.table_service'), __('messages.water_bowls'), __('messages.pet_treats_with_ingredients_shown')],
                'pricing' => ['Pet water' => __('messages.free'), 'Pet treat plate' => '€4'],
                'rating' => 4.5,
                'review_count' => 73,
                'verified_review_count' => 26,
                'verification' => [
                    'label' => __('messages.owner_confirmed_rule'),
                    'scope' => __('messages.pet_access_hours_and_terrace_policy'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.pet_access_rule_corrected_today'),
                'recommendation_reason' => __('messages.quiet_terrace_with_water_close_to_the_city_center'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('pet-cafe'),
                'image_alt' => __('messages.quiet_cafe_terrace_with_tables_beneath_a_covered_courtyard'),
                'events' => [],
                'base_warnings' => [],
            ],
            'city-pet-market' => [
                'key' => 'city-pet-market',
                'name' => __('messages.city_pet_market_and_pharmacy'),
                'short_name' => __('messages.city_pet_market'),
                'primary_category' => 'pet-store',
                'categories' => ['pet-store'],
                'category_label' => __('messages.pet_store'),
                'category_icon' => 'shopping-bag',
                'summary' => __('messages.a_fictional_pet_shop_with_pickup_donation_point_veterinary_diets_and_no_live_animal_sales'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.naujamiestis'),
                'address' => __('messages.demo_address_kauno_g_vilnius'),
                'general_location' => __('messages.naujamiestis_kauno_street'),
                'latitude' => 54.6744,
                'longitude' => 25.2749,
                'map_x' => 53,
                'map_y' => 55,
                'coordinate_accuracy' => __('messages.demo_business_entrance'),
                'distance_km' => 1.8,
                'travel_minutes' => 8,
                'open_state' => 'open',
                'open_label' => __('messages.open_until_8_00_pm'),
                'closes_at' => __('messages.8_00_pm'),
                'hours_summary' => __('messages.mon_sat_9_00_am_8_00_pm_sun_10_00_am_6_00_pm'),
                'special_hours' => __('messages.inventory_is_not_connected_live_call_before_travel'),
                'phone' => '+370 600 00425',
                'website' => null,
                'email' => 'demo-store@pawcircle.example',
                'accepted_species' => $allCompanions,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leashed_pets_and_secure_carriers_welcome'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.usually_quieter_on_weekday_mornings'),
                'noise_level' => __('messages.typical_retail_environment'),
                'rules' => [__('messages.leash_or_secure_carrier_required_sentence'), __('messages.prescription_products_require_appropriate_verification'), __('messages.live_stock_is_not_guaranteed')],
                'features' => [__('messages.pickup_counter'), __('messages.donation_point'), __('messages.packaging_recycling'), __('messages.no_live_animal_sales')],
                'accessibility' => [__('messages.step_free_entrance'), __('messages.wide_aisles'), __('messages.accessible_parking')],
                'safety' => [__('messages.staffed_pickup_desk'), __('messages.prescription_boundary')],
                'services' => ['food', __('messages.veterinary_diets'), 'carriers', __('messages.mobility_aids'), __('messages.gps_collars'), __('messages.donation_collection')],
                'pricing' => ['Pickup reservation' => __('messages.no_fee')],
                'rating' => 4.4,
                'review_count' => 88,
                'verified_review_count' => 33,
                'verification' => [
                    'label' => __('messages.demo_business_profile'),
                    'scope' => __('messages.prototype_hours_and_service_categories'),
                    'updated_at' => '2026-07-27',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.hours_checked_3_days_ago_inventory_not_live'),
                'recommendation_reason' => __('messages.accessible_pickup_and_a_shelter_donation_point_nearby'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('pet-store'),
                'image_alt' => __('messages.pet_care_products_arranged_on_bright_retail_shelving'),
                'events' => [],
                'base_warnings' => [],
            ],
            'vilnius-animal-aid' => [
                'key' => 'vilnius-animal-aid',
                'name' => __('messages.vilnius_animal_aid_center'),
                'short_name' => __('messages.animal_aid_center'),
                'primary_category' => 'shelter',
                'categories' => ['shelter'],
                'category_label' => __('messages.shelter_and_adoption_center'),
                'category_icon' => 'house-heart',
                'summary' => __('messages.a_fictional_verified_shelter_profile_with_scheduled_visits_fostering_donations_volunteer_events_and_microchip_help'),
                'city' => __('messages.vilnius'),
                'neighborhood' => __('messages.naujoji_vilnia'),
                'address' => __('messages.demo_address_parko_g_vilnius'),
                'general_location' => __('messages.naujoji_vilnia_parko_street'),
                'latitude' => 54.6947,
                'longitude' => 25.4166,
                'map_x' => 92,
                'map_y' => 31,
                'coordinate_accuracy' => __('messages.demo_visitor_entrance'),
                'distance_km' => 11.8,
                'travel_minutes' => 29,
                'open_state' => 'appointment-only',
                'open_label' => __('messages.visits_by_appointment'),
                'closes_at' => __('messages.office_closes_6_00_pm'),
                'hours_summary' => __('messages.visitor_appointments_tue_sun_11_00_am_6_00_pm'),
                'special_hours' => __('messages.do_not_bring_resident_pets_unless_the_shelter_confirms_an_introduction'),
                'phone' => '+370 600 00534',
                'website' => null,
                'email' => 'demo-shelter@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit', 'rodent'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.resident_pets_only_by_approved_appointment'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.timed_visits_keep_introductions_calm'),
                'noise_level' => __('messages.quiet_introduction_rooms_available'),
                'rules' => [__('messages.book_before_visiting'), __('messages.do_not_bring_a_resident_pet_without_approval'), __('messages.photography_follows_animal_and_visitor_consent')],
                'features' => [__('messages.adoption_rooms'), __('messages.volunteer_desk'), __('messages.donation_point'), __('messages.foster_coordination'), __('messages.microchip_checks')],
                'accessibility' => [__('messages.step_free_visitor_entrance'), __('messages.accessible_parking'), __('messages.seated_meeting_room')],
                'safety' => [__('messages.controlled_introductions'), __('messages.separate_species_areas'), __('messages.staff_escort')],
                'services' => ['adoption', 'fostering', 'volunteering', 'donations', __('messages.microchip_checks'), __('messages.transport_coordination')],
                'pricing' => ['Visitor appointment' => __('messages.free'), 'Donations' => __('messages.voluntary')],
                'rating' => 4.8,
                'review_count' => 52,
                'verified_review_count' => 28,
                'verification' => [
                    'label' => __('messages.demo_organization_profile'),
                    'scope' => __('messages.prototype_identity_visits_and_services'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.visitor_rules_checked_yesterday'),
                'recommendation_reason' => __('messages.verified_volunteer_and_adoption_programs_with_accessible_visits'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('shelter'),
                'image_alt' => __('messages.volunteer_sitting_calmly_with_a_shelter_dog_outdoors'),
                'events' => ['shelter-open-house'],
                'base_warnings' => [],
            ],
        ];
    }
}
