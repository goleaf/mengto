<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlaceAccessibilityStatus;
use App\Enums\PlaceType;
use App\Enums\PlacePublicLocationPrecision;
use App\Enums\PlaceVerificationStatus;
use App\Models\Place;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        $records = $this->withCanonicalAuthority($this->records());

        if (isset($records[$key])) {
            return $records[$key];
        }

        return collect($records)->firstWhere('slug', $key);
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
    private function withCanonicalAuthority(array $records): array
    {
        $authoritative = [];
        $user = $this->user();

        foreach ($this->canonicalPlaces() as $stableKey => $place) {
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
            ])
            ->limit(500)
            ->orderBy('id');

        if ($user === null) {
            $query->publiclyDiscoverable();
        } else {
            $query->accessibleTo($user);
        }

        $this->canonicalPlaces = $query
            ->get()
            ->keyBy('stable_key')
            ->all();

        return $this->canonicalPlaces;
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
                'name' => __('messages.vingis_park_quiet_loop_8a4ed2ee1c'),
                'short_name' => __('messages.vingis_park_c6d231fa8d'),
                'primary_category' => 'park',
                'categories' => ['park', 'route'],
                'category_label' => __('messages.park_and_walking_route_05623f7bb6'),
                'category_icon' => 'trees',
                'summary' => __('messages.a_broad_tree_lined_loop_with_calmer_outer_paths_water_po_f8c46f4691'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.naujamiestis_17a26d0ce9'),
                'address' => __('messages.m_k_iurlionio_g_100_vilnius_d84301a200'),
                'general_location' => __('messages.vingis_park_western_entrances_0b37c75744'),
                'latitude' => 54.6834,
                'longitude' => 25.2368,
                'map_x' => 38,
                'map_y' => 45,
                'coordinate_accuracy' => __('messages.main_public_entrance_cd6b44e9ab'),
                'distance_km' => 3.8,
                'travel_minutes' => 14,
                'open_state' => 'open',
                'open_label' => __('messages.open_now_14b67e6207'),
                'closes_at' => __('messages.open_all_day_b6c0d9ecd2'),
                'hours_summary' => __('messages.public_paths_open_all_day_lighting_varies_by_entrance_60f9ac45c8'),
                'special_hours' => __('messages.event_closures_are_posted_as_temporary_updates_e24e56e5cb'),
                'phone' => null,
                'website' => 'https://vilnius.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_outside_signed_off_leash_areas_f96b17ce5a'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.usually_calmer_before_9_00_am_and_after_8_00_pm_ec66e5eaa4'),
                'noise_level' => __('messages.mixed_quieter_on_the_western_loop_2804940de6'),
                'rules' => [
                    __('messages.keep_pets_leashed_outside_signed_areas_cb7c7f0642'),
                    __('messages.use_extra_distance_near_cyclists_and_event_crowds_5a5025bd28'),
                    __('messages.group_events_must_respect_municipal_park_rules_640f1c951e'),
                ],
                'features' => [__('messages.wide_paths_77c501f716'), 'water', 'shade', 'benches', 'bins', __('messages.evening_lighting_3c0f22cde4')],
                'accessibility' => [__('messages.step_free_routes_dcde485c65'), __('messages.wide_paths_77c501f716'), __('messages.accessible_parking_c3b5e74625'), __('messages.rest_areas_4861779b62')],
                'safety' => [__('messages.separate_from_main_roads_6838021756'), __('messages.several_early_exit_points_9cb5557450'), __('messages.nearby_public_transport_56d09ac320')],
                'services' => [__('messages.4_6_km_quiet_loop_5f20e8cefa'), __('messages.2_1_km_shortcut_c8a15f4461'), __('messages.water_points_57b6574f9b'), __('messages.event_meeting_areas_0e4569e05b')],
                'pricing' => ['Park access' => __('messages.free_f411a1fb62')],
                'rating' => 4.7,
                'review_count' => 184,
                'verified_review_count' => 61,
                'verification' => [
                    'label' => __('messages.rules_checked_from_a_public_source_c08ec7f854'),
                    'scope' => __('messages.address_and_general_park_rules_85f50e41e3'),
                    'updated_at' => '2026-07-27',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.community_conditions_checked_2_hours_ago_54e4f1ba1b'),
                'recommendation_reason' => __('messages.best_match_for_a_calm_evening_walk_with_scout_b8db2113fb'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.broad_shaded_path_through_a_green_city_park_daf9d8d412'),
                'route' => [
                    'distance' => __('messages.4_6_km_a3c02c501c'),
                    'duration' => __('messages.65_85_min_1f306dcb19'),
                    'difficulty' => __('messages.easy_d6915875de'),
                    'surface' => __('messages.paved_and_compact_gravel_5a330d5d30'),
                    'elevation' => __('messages.mostly_level_19b14dabde'),
                    'shortcuts' => [__('messages.2_1_km_riverside_return_1f81601896'), __('messages.bus_stop_exit_after_3_km_dea31a26f9')],
                    'hazards' => [__('messages.busy_cycle_crossing_near_the_main_avenue_7080ec2f4d'), __('messages.mud_on_unpaved_edges_after_rain_40bc7ef250')],
                    'home_privacy' => __('messages.starts_at_a_public_park_entrance_no_home_point_is_stored_61016d11b5'),
                ],
                'events' => ['small-dog-social'],
                'base_warnings' => [],
            ],
            'bernardine-evening-park' => [
                'key' => 'bernardine-evening-park',
                'name' => __('messages.bernardine_garden_evening_paths_229f19b90a'),
                'short_name' => __('messages.bernardine_garden_ec60242ea2'),
                'primary_category' => 'park',
                'categories' => ['park'],
                'category_label' => __('messages.city_park_e979ccb315'),
                'category_icon' => 'trees',
                'summary' => __('messages.a_central_well_lit_garden_with_smooth_paths_and_easy_tra_afadd3ece1'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.old_town_9a9e4acaf8'),
                'address' => __('messages.b_radvilait_s_g_8a_vilnius_fe106583b6'),
                'general_location' => __('messages.bernardine_garden_old_town_2fd328ebef'),
                'latitude' => 54.6849,
                'longitude' => 25.2951,
                'map_x' => 66,
                'map_y' => 36,
                'coordinate_accuracy' => __('messages.main_public_entrance_cd6b44e9ab'),
                'distance_km' => 1.6,
                'travel_minutes' => 8,
                'open_state' => 'closing-soon',
                'open_label' => __('messages.closes_at_10_00_pm_582736507d'),
                'closes_at' => __('messages.10_00_pm_873f21fa5b'),
                'hours_summary' => __('messages.daily_7_00_am_10_00_pm_8108a264d3'),
                'special_hours' => __('messages.seasonal_closing_time_may_change_d02b49408e'),
                'phone' => null,
                'website' => 'https://vilnius.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_throughout_the_garden_657844f615'),
                'fenced' => false,
                'water' => false,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'high',
                'crowd_label' => __('messages.often_busy_with_families_and_cyclists_before_sunset_aa2b618f1b'),
                'noise_level' => __('messages.moderate_to_busy_5829c8a690'),
                'rules' => [
                    __('messages.pets_stay_on_leash_28e54b1f4c'),
                    __('messages.keep_away_from_planted_beds_and_playground_entrances_ab1db8a1f8'),
                    __('messages.bring_drinking_water_for_your_pet_574c36ee0e'),
                ],
                'features' => ['lighting', __('messages.smooth_paths_6d024f23ab'), 'benches', 'bins', __('messages.public_transport_1415b3e0aa')],
                'accessibility' => [__('messages.step_free_main_paths_06f3a656ca'), __('messages.frequent_benches_8bfdef9c16'), __('messages.nearby_bus_stops_ec2bf8f121')],
                'safety' => [__('messages.good_path_visibility_4e0ec78656'), __('messages.staffed_during_opening_hours_642f33fc00')],
                'services' => [__('messages.1_3_km_garden_loop_be34103e41'), __('messages.rest_areas_4861779b62'), __('messages.nearby_pet_friendly_cafes_3d26b24f81')],
                'pricing' => ['Park access' => __('messages.free_f411a1fb62')],
                'rating' => 4.5,
                'review_count' => 129,
                'verified_review_count' => 44,
                'verification' => [
                    'label' => __('messages.official_location_7fbc3a7f09'),
                    'scope' => __('messages.address_and_opening_hours_9595743fa7'),
                    'updated_at' => '2026-07-25',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.hours_checked_5_days_ago_c0e22c1fae'),
                'recommendation_reason' => __('messages.closest_well_lit_option_but_bring_water_and_expect_more__4f945f6652'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.formal_city_garden_with_broad_paved_walking_paths_7d5fbc0e77'),
                'events' => [],
                'base_warnings' => [],
            ],
            'pavilniai-calm-trail' => [
                'key' => 'pavilniai-calm-trail',
                'name' => __('messages.pavilniai_calm_forest_trail_0375a20bab'),
                'short_name' => __('messages.pavilniai_trail_83681f6dfa'),
                'primary_category' => 'park',
                'categories' => ['park', 'route'],
                'category_label' => __('messages.forest_park_and_route_83d70c4476'),
                'category_icon' => 'trees',
                'summary' => __('messages.the_quietest_option_with_wide_forest_sections_and_shade__fd4f303937'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.pavilniai_595c89fbe6'),
                'address' => __('messages.belmonto_g_vilnius_8ddfb43818'),
                'general_location' => __('messages.pavilniai_regional_park_belmontas_side_8a9629bfc4'),
                'latitude' => 54.6817,
                'longitude' => 25.3577,
                'map_x' => 84,
                'map_y' => 48,
                'coordinate_accuracy' => __('messages.general_trail_entrance_6579cce2fd'),
                'distance_km' => 7.4,
                'travel_minutes' => 22,
                'open_state' => 'open',
                'open_label' => __('messages.open_now_14b67e6207'),
                'closes_at' => __('messages.natural_area_5959550dcc'),
                'hours_summary' => __('messages.open_access_daylight_visits_recommended_dfa2649ad2'),
                'special_hours' => __('messages.trails_may_close_during_storms_or_maintenance_7236a1b686'),
                'phone' => null,
                'website' => 'https://saugoma.lt/',
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_required_around_wildlife_and_shared_paths_5d3ce5a984'),
                'fenced' => false,
                'water' => true,
                'lighting' => false,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => false,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.usually_quiet_outside_weekend_afternoons_4a4dc8742a'),
                'noise_level' => __('messages.low_f793de205e'),
                'rules' => [
                    __('messages.keep_pets_controlled_near_wildlife_ff4c9f5efe'),
                    __('messages.stay_on_marked_trails_0fd36f66eb'),
                    __('messages.carry_water_and_a_charged_phone_45c437ce05'),
                ],
                'features' => [__('messages.forest_shade_54e0099b83'), __('messages.natural_water_f11ead7d83'), __('messages.quiet_paths_0d4d763ae3'), 'parking', __('messages.shortcut_loop_c0c2eedfc6')],
                'accessibility' => [__('messages.uneven_natural_surface_80fcf7a832'), __('messages.limited_step_free_access_0abe9927cf')],
                'safety' => [__('messages.mobile_reception_varies_71e7a4291c'), __('messages.unlit_after_dusk_beb7b3e47b'), __('messages.seasonal_tick_activity_24781dd529')],
                'services' => [__('messages.5_2_km_forest_loop_9f09f3df5a'), __('messages.2_8_km_shortcut_403c65b816'), 'viewpoints'],
                'pricing' => ['Trail access' => __('messages.free_f411a1fb62')],
                'rating' => 4.8,
                'review_count' => 76,
                'verified_review_count' => 23,
                'verification' => [
                    'label' => __('messages.community_mapped_2e636068f7'),
                    'scope' => __('messages.trail_entrance_and_conditions_a41179f68c'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.trail_conditions_checked_yesterday_bd16047c8a'),
                'recommendation_reason' => __('messages.calmest_match_with_shade_and_water_but_farther_and_unlit_5ff4340693'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('park'),
                'image_alt' => __('messages.quiet_forest_path_beneath_tall_green_trees_fa0622955d'),
                'route' => [
                    'distance' => __('messages.5_2_km_0ee56d850a'),
                    'duration' => __('messages.80_100_min_26e42c8dec'),
                    'difficulty' => __('messages.moderate_5c42afc7a2'),
                    'surface' => __('messages.forest_soil_roots_and_compact_gravel_a69978ea3c'),
                    'elevation' => __('messages.several_short_climbs_b2d34b6a38'),
                    'shortcuts' => [__('messages.2_8_km_lower_loop_ae28de6c88'), __('messages.turnaround_viewpoint_at_1_7_km_a979c0e51f')],
                    'hazards' => ['ticks', __('messages.mud_after_rain_932ac9282d'), __('messages.limited_lighting_ccc4915bca'), __('messages.variable_mobile_signal_636a4478a3')],
                    'home_privacy' => __('messages.the_shared_route_starts_at_a_public_trail_entrance_fef34234ec'),
                ],
                'events' => [],
                'base_warnings' => [
                    [
                        'key' => 'pavilniai-ticks',
                        'title' => __('messages.seasonal_tick_activity_e4b8b03522'),
                        'category' => 'parasites',
                        'status' => 'official-source',
                        'detail' => __('messages.check_pets_after_leaving_tall_grass_and_forest_edges_75f3a3c7cb'),
                        'reported_at' => '2026-07-28T09:00:00+03:00',
                        'expires_at' => '2026-09-30T23:59:00+03:00',
                        'confirmations' => 12,
                        'source' => __('messages.seasonal_public_guidance_a0b72819b9'),
                    ],
                ],
            ],
            'zverynas-small-dog-run' => [
                'key' => 'zverynas-small-dog-run',
                'name' => __('messages.v_rynas_neighborhood_dog_run_fd4f015527'),
                'short_name' => __('messages.v_rynas_dog_run_88fabddb45'),
                'primary_category' => 'dog-park',
                'categories' => ['dog-park'],
                'category_label' => __('messages.fenced_dog_park_9f6b0c41d7'),
                'category_icon' => 'fence',
                'summary' => __('messages.a_free_fenced_run_with_separate_small_dog_space_double_g_3eb4ca1988'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.v_rynas_76cb91baf6'),
                'address' => __('messages.birut_s_g_green_area_vilnius_8a52b45e63'),
                'general_location' => __('messages.v_rynas_birut_s_street_green_area_74b9a2d5e7'),
                'latitude' => 54.6952,
                'longitude' => 25.2474,
                'map_x' => 43,
                'map_y' => 28,
                'coordinate_accuracy' => __('messages.community_confirmed_entrance_912af9c24b'),
                'distance_km' => 2.7,
                'travel_minutes' => 11,
                'open_state' => 'open-with-warning',
                'open_label' => __('messages.open_active_warning_3c8ea097cc'),
                'closes_at' => __('messages.10_00_pm_873f21fa5b'),
                'hours_summary' => __('messages.daily_7_00_am_10_00_pm_8108a264d3'),
                'special_hours' => __('messages.lighting_is_limited_after_9_00_pm_b967eb5f47'),
                'phone' => null,
                'website' => null,
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.off_leash_inside_the_signed_fenced_zones_41025fe7a1'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.a_few_dogs_reported_35_minutes_ago_af42d1a023'),
                'noise_level' => __('messages.usually_calm_before_workdays_a4ad3feaff'),
                'rules' => [
                    __('messages.use_the_zone_that_matches_your_dog_s_size_and_comfort_6b3834f49d'),
                    __('messages.keep_the_entrance_clear_and_close_both_gates_5e7551bc4f'),
                    __('messages.remove_food_if_it_creates_tension_be59ee4d22'),
                ],
                'features' => [__('messages.fully_fenced_3be36389ac'), __('messages.double_gate_152e845b18'), __('messages.small_dog_zone_bbabcd95c1'), __('messages.quiet_zone_c1e15d55df'), 'water', 'benches'],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.firm_surface_to_the_small_dog_zone_c266a164ef')],
                'safety' => [__('messages.1_5_m_fence_4908df64fe'), __('messages.double_gate_152e845b18'), __('messages.separate_size_zones_ad3aa0aaad')],
                'services' => [__('messages.small_dog_zone_bbabcd95c1'), __('messages.general_run_386eaf0d7c'), __('messages.training_corner_6879032ee2'), __('messages.water_point_48f9d63826')],
                'pricing' => ['General access' => __('messages.free_f411a1fb62')],
                'rating' => 4.4,
                'review_count' => 58,
                'verified_review_count' => 19,
                'verification' => [
                    'label' => __('messages.community_verified_5f4632da31'),
                    'scope' => __('messages.entrance_fence_zones_and_water_cb903f5556'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.entrance_status_checked_3_hours_ago_13296d6646'),
                'recommendation_reason' => __('messages.matches_a_small_dog_double_gate_water_and_quiet_zone_sea_ee18d2957b'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('dog-park'),
                'image_alt' => __('messages.small_dog_standing_on_grass_inside_a_fenced_exercise_are_c376723f9d'),
                'events' => [],
                'base_warnings' => [
                    [
                        'key' => 'zverynas-gate-latch',
                        'title' => __('messages.small_dog_gate_latch_is_loose_b6e0677d15'),
                        'category' => 'damaged-fence',
                        'status' => 'confirmed',
                        'detail' => __('messages.hold_the_inner_latch_closed_while_another_person_enters_fa3a40b50c'),
                        'reported_at' => '2026-07-30T08:15:00+03:00',
                        'expires_at' => '2026-08-02T08:15:00+03:00',
                        'confirmations' => 4,
                        'source' => __('messages.visitor_photo_and_community_confirmations_e3f4f42a0f'),
                    ],
                ],
            ],
            'naujininkai-secure-dog-field' => [
                'key' => 'naujininkai-secure-dog-field',
                'name' => __('messages.naujininkai_secure_dog_field_900541ebd2'),
                'short_name' => __('messages.naujininkai_dog_field_6c4231da1f'),
                'primary_category' => 'dog-park',
                'categories' => ['dog-park'],
                'category_label' => __('messages.fenced_dog_park_9f6b0c41d7'),
                'category_icon' => 'fence',
                'summary' => __('messages.a_quieter_fully_fenced_field_with_double_gates_separate__dc3d7fa3e0'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.naujininkai_7d9898e383'),
                'address' => __('messages.dz_k_g_green_area_vilnius_3b3d0930cb'),
                'general_location' => __('messages.naujininkai_dz_k_street_0bfeec6487'),
                'latitude' => 54.6595,
                'longitude' => 25.2782,
                'map_x' => 58,
                'map_y' => 73,
                'coordinate_accuracy' => __('messages.community_confirmed_entrance_912af9c24b'),
                'distance_km' => 4.9,
                'travel_minutes' => 17,
                'open_state' => 'open',
                'open_label' => __('messages.open_now_14b67e6207'),
                'closes_at' => __('messages.9_00_pm_915881c885'),
                'hours_summary' => __('messages.daily_7_00_am_9_00_pm_ed6357afba'),
                'special_hours' => __('messages.water_may_be_turned_off_during_freezing_weather_548629f3fc'),
                'phone' => null,
                'website' => null,
                'email' => null,
                'accepted_species' => ['dog'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.off_leash_inside_closed_zones_cefb768597'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.usually_quiet_no_recent_crowd_report_4e10e51088'),
                'noise_level' => __('messages.low_to_moderate_14b4a3db3a'),
                'rules' => [
                    __('messages.use_separate_size_zones_fa5abf8750'),
                    __('messages.close_both_gates_before_removing_the_leash_103778496a'),
                    __('messages.take_toys_away_if_another_dog_guards_them_9bea3bc762'),
                ],
                'features' => [__('messages.fully_fenced_3be36389ac'), __('messages.double_gate_152e845b18'), __('messages.small_dog_zone_bbabcd95c1'), 'water', 'lighting', 'parking'],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.firm_central_path_b4556969fb'), __('messages.nearby_parking_6fe811dd32')],
                'safety' => [__('messages.1_7_m_fence_21a95a533a'), __('messages.double_gate_152e845b18'), __('messages.no_active_warnings_04a7767a88')],
                'services' => [__('messages.small_dog_zone_bbabcd95c1'), __('messages.large_dog_zone_db41906850'), __('messages.training_equipment_dba3b0272c'), __('messages.water_point_48f9d63826')],
                'pricing' => ['General access' => __('messages.free_f411a1fb62')],
                'rating' => 4.6,
                'review_count' => 34,
                'verified_review_count' => 14,
                'verification' => [
                    'label' => __('messages.community_verified_5f4632da31'),
                    'scope' => __('messages.fence_entrances_zones_and_facilities_c41a4bc0ce'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'community',
                ],
                'data_freshness' => __('messages.no_hazards_reported_in_the_last_7_days_70787ddf12'),
                'recommendation_reason' => __('messages.safer_current_alternative_with_the_same_small_dog_featur_de6d2459be'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => false,
                'emergency' => false,
                ...$this->media->primary('dog-park'),
                'image_alt' => __('messages.dogs_standing_in_a_spacious_grassy_fenced_field_21549f867a'),
                'events' => ['small-dog-social'],
                'base_warnings' => [],
            ],
            'paws-24-veterinary-center' => [
                'key' => 'paws-24-veterinary-center',
                'name' => __('messages.paws_24_veterinary_center_8fd860644c'),
                'short_name' => __('messages.paws_24_28ba7c3aa0'),
                'primary_category' => 'emergency-vet',
                'categories' => ['vet', 'emergency-vet', 'pet-store'],
                'category_label' => __('messages.24_hour_veterinary_center_cf80014c7b'),
                'category_icon' => 'siren',
                'summary' => __('messages.a_fictional_demo_emergency_clinic_accepting_birds_and_co_bcd7ff36c2'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.nipi_k_s_abe540e342'),
                'address' => __('messages.demo_address_konstitucijos_pr_vilnius_daefb2ca1a'),
                'general_location' => __('messages.nipi_k_s_konstitucijos_avenue_fb9ce4e6be'),
                'latitude' => 54.7015,
                'longitude' => 25.2685,
                'map_x' => 51,
                'map_y' => 21,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 2.2,
                'travel_minutes' => 9,
                'open_state' => 'open',
                'open_label' => __('messages.open_24_hours_d955c52c16'),
                'closes_at' => __('messages.open_all_night_e6e6ee9120'),
                'hours_summary' => __('messages.emergency_reception_24_7_call_before_travel_ccd29f4258'),
                'special_hours' => __('messages.overnight_diagnostics_and_surgery_depend_on_the_on_call__f310aa1c87'),
                'phone' => '+370 600 00024',
                'website' => null,
                'email' => 'demo-emergency@pawcircle.example',
                'accepted_species' => $allCompanions,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_birds_and_small_pets_in_secure_carrier_b28f9bc1a0'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.approximate_wait_call_for_current_triage_status_040ee7c93f'),
                'noise_level' => __('messages.separate_quiet_waiting_area_available_df17e3d01e'),
                'rules' => [
                    __('messages.call_before_travel_when_possible_0185184f84'),
                    __('messages.use_a_secure_carrier_for_birds_and_small_animals_b018857695'),
                    __('messages.emergency_cases_are_triaged_by_clinical_need_3bdd199412'),
                ],
                'features' => [__('messages.24_hour_clinician_eeab63593f'), __('messages.overnight_diagnostics_f139e697aa'), 'surgery', __('messages.inpatient_care_5f9cb38e47'), __('messages.separate_waiting_d29a32bb0a')],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.accessible_parking_c3b5e74625'), __('messages.wide_doors_ced4bf2529'), __('messages.wait_in_car_option_fa5f993cd4')],
                'safety' => [__('messages.separate_dog_and_cat_waiting_4e3e5b30ab'), __('messages.isolation_room_0411a8300d'), __('messages.on_site_inpatient_team_c1ce699f00')],
                'services' => [__('messages.emergency_triage_e36e82f732'), 'x-ray', 'ultrasound', 'laboratory', 'surgery', __('messages.avian_care_c830c55b41'), __('messages.inpatient_care_5f9cb38e47')],
                'pricing' => ['Emergency assessment' => __('messages.from_65_c80bc6ca83'), 'Night surcharge' => __('messages.shown_before_treatment_3dc7b616a6')],
                'rating' => 4.6,
                'review_count' => 92,
                'verified_review_count' => 37,
                'verification' => [
                    'label' => __('messages.demo_business_profile_5d38c6866f'),
                    'scope' => __('messages.prototype_contact_hours_and_species_data_640bd60ebc'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.emergency_hours_confirmed_in_demo_data_today_5cc98bdcea'),
                'recommendation_reason' => __('messages.open_now_accepts_birds_and_is_the_fastest_suitable_emerg_399bcf913b'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => true,
                ...$this->media->primary('emergency-vet'),
                'image_alt' => __('messages.veterinary_clinician_examining_a_dog_in_a_bright_treatme_65f5e83045'),
                'events' => ['travel-ready-webinar'],
                'base_warnings' => [],
            ],
            'night-paw-clinic' => [
                'key' => 'night-paw-clinic',
                'name' => __('messages.night_paw_veterinary_clinic_6b5bad7625'),
                'short_name' => __('messages.night_paw_clinic_34631f4a99'),
                'primary_category' => 'emergency-vet',
                'categories' => ['vet', 'emergency-vet'],
                'category_label' => __('messages.overnight_veterinary_clinic_cf55d9907c'),
                'category_icon' => 'siren',
                'summary' => __('messages.a_fictional_demo_overnight_clinic_for_dogs_and_cats_it_d_ba00254312'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.naujamiestis_17a26d0ce9'),
                'address' => __('messages.demo_address_vitrigailos_g_vilnius_8c466cba35'),
                'general_location' => __('messages.naujamiestis_vitrigailos_street_f557ddf175'),
                'latitude' => 54.6714,
                'longitude' => 25.2684,
                'map_x' => 50,
                'map_y' => 58,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 1.9,
                'travel_minutes' => 7,
                'open_state' => 'on-call',
                'open_label' => __('messages.emergency_intake_by_phone_92b30419d9'),
                'closes_at' => __('messages.on_call_overnight_a900f4ecb4'),
                'hours_summary' => __('messages.8_00_pm_8_00_am_phone_confirmation_required_d6777a58a1'),
                'special_hours' => __('messages.only_dogs_and_cats_are_accepted_overnight_a79732e452'),
                'phone' => '+370 600 00091',
                'website' => null,
                'email' => 'demo-night@pawcircle.example',
                'accepted_species' => $commonPets,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_in_secure_carriers_da505765d3'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'unknown',
                'crowd_label' => __('messages.no_current_wait_information_5435e58d95'),
                'noise_level' => __('messages.shared_waiting_area_e1bfb97f62'),
                'rules' => [__('messages.call_before_travel_c02970e2c6'), __('messages.birds_and_exotic_pets_are_not_accepted_3667fae920'), __('messages.emergency_cases_are_triaged_56f24b6482')],
                'features' => [__('messages.overnight_intake_270b9c97ea'), 'x-ray', 'laboratory', __('messages.surgery_on_call_0780694ef6')],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.parking_close_to_entrance_814f5a6744')],
                'safety' => [__('messages.secure_carrier_required_for_cats_01806c7bb1')],
                'services' => [__('messages.emergency_triage_e36e82f732'), 'x-ray', 'laboratory', __('messages.basic_surgery_7b9f3d6840')],
                'pricing' => ['Night assessment' => __('messages.from_55_0152589e9c')],
                'rating' => 4.2,
                'review_count' => 47,
                'verified_review_count' => 15,
                'verification' => [
                    'label' => __('messages.demo_contact_confirmed_12647bf53c'),
                    'scope' => __('messages.prototype_hours_and_accepted_species_0730cd7dc1'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.on_call_schedule_checked_yesterday_20e2498426'),
                'recommendation_reason' => __('messages.closer_but_unsuitable_for_birds_and_requires_phone_confi_f50d6b81ac'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => true,
                ...$this->media->primary('emergency-vet'),
                'image_alt' => __('messages.dog_waiting_calmly_in_a_veterinary_reception_area_6a569c19d3'),
                'events' => [],
                'base_warnings' => [],
            ],
            'green-paw-neighborhood-clinic' => [
                'key' => 'green-paw-neighborhood-clinic',
                'name' => __('messages.green_paw_neighborhood_clinic_e122777ea7'),
                'short_name' => __('messages.green_paw_clinic_c16879cc1c'),
                'primary_category' => 'vet',
                'categories' => ['vet'],
                'category_label' => __('messages.veterinary_clinic_05e9176e96'),
                'category_icon' => 'stethoscope',
                'summary' => __('messages.a_fictional_demo_daytime_clinic_with_online_request_boun_97511fb672'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.antakalnis_f8c9d66b06'),
                'address' => __('messages.demo_address_antakalnio_g_vilnius_7af2e8d3cc'),
                'general_location' => __('messages.antakalnis_near_the_clinic_district_9e88995389'),
                'latitude' => 54.7068,
                'longitude' => 25.3147,
                'map_x' => 72,
                'map_y' => 18,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 4.4,
                'travel_minutes' => 16,
                'open_state' => 'closed',
                'open_label' => __('messages.closed_opens_8_00_am_e299832303'),
                'closes_at' => __('messages.opens_8_00_am_9989f785d2'),
                'hours_summary' => __('messages.mon_fri_8_00_am_8_00_pm_sat_9_00_am_3_00_pm_45a8b4d50b'),
                'special_hours' => __('messages.appointments_are_recommended_b2dca3d37d'),
                'phone' => '+370 600 00118',
                'website' => null,
                'email' => 'demo-clinic@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit', 'rodent'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_other_pets_in_secure_carriers_7d5074b0ce'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.appointments_reduce_waiting_live_availability_is_not_con_7a07ee3cf1'),
                'noise_level' => __('messages.separate_cat_waiting_room_32c62d7d9f'),
                'rules' => [__('messages.book_or_call_before_arrival_f4e3f72884'), __('messages.bring_only_selected_documents_e438c75ec6'), __('messages.use_a_secure_carrier_when_appropriate_e41fc46a80')],
                'features' => [__('messages.separate_waiting_d29a32bb0a'), 'rehabilitation', 'laboratory', 'dentistry', 'parking'],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.wide_doors_ced4bf2529'), __('messages.accessible_parking_c3b5e74625'), __('messages.wait_in_car_option_fa5f993cd4')],
                'safety' => [__('messages.separate_dog_and_cat_waiting_4e3e5b30ab'), __('messages.isolation_room_0411a8300d')],
                'services' => ['consultation', 'vaccination', 'laboratory', 'ultrasound', 'dentistry', 'rehabilitation'],
                'pricing' => ['Consultation' => '€35–€55', 'Ultrasound' => __('messages.from_45_daa0615558')],
                'rating' => 4.8,
                'review_count' => 66,
                'verified_review_count' => 31,
                'verification' => [
                    'label' => __('messages.demo_business_profile_5d38c6866f'),
                    'scope' => __('messages.prototype_address_hours_and_services_e14871c60f'),
                    'updated_at' => '2026-07-28',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.services_checked_2_days_ago_fdcde9456c'),
                'recommendation_reason' => __('messages.highly_rated_daytime_option_with_a_quiet_waiting_area_d54e0e91e8'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('vet'),
                'image_alt' => __('messages.veterinary_professional_listening_to_a_dog_with_a_stetho_999be2beff'),
                'events' => ['travel-ready-webinar'],
                'base_warnings' => [],
            ],
            'quiet-whiskers-grooming' => [
                'key' => 'quiet-whiskers-grooming',
                'name' => __('messages.quiet_whiskers_grooming_studio_3a47efcf8a'),
                'short_name' => __('messages.quiet_whiskers_7d2429683b'),
                'primary_category' => 'grooming',
                'categories' => ['grooming'],
                'category_label' => __('messages.low_stress_grooming_studio_7d4397f092'),
                'category_icon' => 'scissors',
                'summary' => __('messages.a_fictional_individual_appointment_studio_for_cats_and_d_c2ba70868d'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.irm_nai_bb81f438fe'),
                'address' => __('messages.demo_address_irm_n_g_vilnius_d5615ebe16'),
                'general_location' => __('messages.irm_nai_near_the_river_352d6a6544'),
                'latitude' => 54.7182,
                'longitude' => 25.3014,
                'map_x' => 66,
                'map_y' => 10,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 4.1,
                'travel_minutes' => 15,
                'open_state' => 'open',
                'open_label' => __('messages.open_next_demo_slot_thu_d3ef5ba08a'),
                'closes_at' => __('messages.7_00_pm_cefc73bd6a'),
                'hours_summary' => __('messages.tue_sat_9_00_am_7_00_pm_individual_appointments_efcec78798'),
                'special_hours' => __('messages.quiet_cat_appointments_are_held_before_noon_24a2fe3852'),
                'phone' => '+370 600 00207',
                'website' => null,
                'email' => 'demo-grooming@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.dogs_leashed_cats_and_rabbits_in_secure_carriers_4bd49d9b0c'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.one_family_at_a_time_885efb9f10'),
                'noise_level' => __('messages.quiet_dryer_and_no_force_drying_options_54bffc1e81'),
                'rules' => [__('messages.share_handling_concerns_privately_efa9b5612c'), __('messages.public_before_and_after_photos_require_separate_consent_e44fe56c5d'), __('messages.breaks_are_available_edbf1baee0')],
                'features' => [__('messages.cat_grooming_ba537a5b6a'), __('messages.quiet_dryer_2d85714241'), __('messages.individual_appointments_eae513beed'), 'breaks', __('messages.senior_pet_handling_b1dac5c8ac')],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.parking_nearby_b9c86c877c'), __('messages.seated_handover_area_1c6b140a70')],
                'safety' => [__('messages.one_family_at_a_time_2ae10b3ad2'), __('messages.private_handling_notes_d428027644'), __('messages.owner_can_remain_by_agreement_9af3ae2a2e')],
                'services' => [__('messages.cat_grooming_ba537a5b6a'), __('messages.bath_and_brush_f3b2a00fc0'), __('messages.nail_care_59179918c7'), 'de-shedding', __('messages.quiet_drying_9f1f01efb3'), __('messages.senior_pet_care_681d0d243f')],
                'pricing' => ['Cat grooming' => __('messages.from_45_daa0615558'), 'Quiet-dry session' => __('messages.from_55_0152589e9c')],
                'rating' => 4.9,
                'review_count' => 41,
                'verified_review_count' => 29,
                'verification' => [
                    'label' => __('messages.demo_specialist_profile_5d7623be39'),
                    'scope' => __('messages.prototype_identity_services_and_studio_access_0258ce3805'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.demo_availability_updated_yesterday_7ef8d152b7'),
                'recommendation_reason' => __('messages.works_with_cats_offers_quiet_drying_and_schedules_one_fa_a0423d65b9'),
                'sponsored' => false,
                'allow_events' => false,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('grooming'),
                'image_alt' => __('messages.quiet_pet_grooming_workspace_with_clean_equipment_0f581fa9e5'),
                'events' => [],
                'base_warnings' => [],
            ],
            'old-town-pet-cafe' => [
                'key' => 'old-town-pet-cafe',
                'name' => __('messages.courtyard_paws_cafe_2c48e0d28d'),
                'short_name' => __('messages.courtyard_paws_9f106e5f73'),
                'primary_category' => 'pet-cafe',
                'categories' => ['pet-cafe'],
                'category_label' => __('messages.pet_friendly_cafe_a146ead573'),
                'category_icon' => 'coffee',
                'summary' => __('messages.a_fictional_quiet_hours_cafe_where_pets_are_currently_al_9580eef06b'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.old_town_9a9e4acaf8'),
                'address' => __('messages.demo_address_pilies_g_vilnius_9e048e3a8b'),
                'general_location' => __('messages.old_town_pilies_street_courtyard_3b6e8ab382'),
                'latitude' => 54.6812,
                'longitude' => 25.2894,
                'map_x' => 63,
                'map_y' => 48,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 1.3,
                'travel_minutes' => 7,
                'open_state' => 'open',
                'open_label' => __('messages.open_terrace_until_9_00_pm_4362f67181'),
                'closes_at' => __('messages.9_00_pm_915881c885'),
                'hours_summary' => __('messages.daily_8_00_am_9_00_pm_5e8d3dc4e0'),
                'special_hours' => __('messages.pets_are_terrace_only_after_the_rule_change_on_july_30_defbd721ba'),
                'phone' => '+370 600 00316',
                'website' => null,
                'email' => 'demo-cafe@pawcircle.example',
                'accepted_species' => ['dog', 'cat'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leash_or_secure_carrier_required_1e0985e9b3'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => false,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.quietest_8_00_10_00_am_and_after_7_30_pm_ba50d109b8'),
                'noise_level' => __('messages.low_during_marked_quiet_hours_34e7a63fef'),
                'rules' => [__('messages.pets_are_allowed_on_the_terrace_only_c31e6b182b'), __('messages.keep_pets_on_leash_or_in_a_carrier_c05fa490f8'), __('messages.do_not_feed_unfamiliar_pets_b054bf9b97')],
                'features' => ['terrace', __('messages.water_bowls_b8c1fe899a'), __('messages.quiet_hours_40398cdb5b'), __('messages.step_free_courtyard_e345f83ba5')],
                'accessibility' => [__('messages.step_free_terrace_0915e3bd78'), __('messages.accessible_restroom_nearby_eeffcc2966'), __('messages.seating_with_aisle_space_f17f1cd54e')],
                'safety' => [__('messages.separate_corner_tables_27809fd63b'), __('messages.no_pet_food_presented_as_veterinary_diet_af5db2c449')],
                'services' => [__('messages.table_service_9463b6bdc2'), __('messages.water_bowls_b8c1fe899a'), __('messages.pet_treats_with_ingredients_shown_92b4f2bd74')],
                'pricing' => ['Pet water' => __('messages.free_f411a1fb62'), 'Pet treat plate' => '€4'],
                'rating' => 4.5,
                'review_count' => 73,
                'verified_review_count' => 26,
                'verification' => [
                    'label' => __('messages.owner_confirmed_rule_2ee30ba727'),
                    'scope' => __('messages.pet_access_hours_and_terrace_policy_be7d68aadf'),
                    'updated_at' => '2026-07-30',
                    'tone' => 'verified',
                ],
                'data_freshness' => __('messages.pet_access_rule_corrected_today_2f1405db2b'),
                'recommendation_reason' => __('messages.quiet_terrace_with_water_close_to_the_city_center_2098df3acb'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('pet-cafe'),
                'image_alt' => __('messages.quiet_cafe_terrace_with_tables_beneath_a_covered_courtya_0e53635e17'),
                'events' => [],
                'base_warnings' => [],
            ],
            'city-pet-market' => [
                'key' => 'city-pet-market',
                'name' => __('messages.city_pet_market_and_pharmacy_dc62a9328f'),
                'short_name' => __('messages.city_pet_market_1b0a04afac'),
                'primary_category' => 'pet-store',
                'categories' => ['pet-store'],
                'category_label' => __('messages.pet_store_15a6b4bd60'),
                'category_icon' => 'shopping-bag',
                'summary' => __('messages.a_fictional_pet_shop_with_pickup_donation_point_veterina_b8776a1d98'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.naujamiestis_17a26d0ce9'),
                'address' => __('messages.demo_address_kauno_g_vilnius_ae68cbd03d'),
                'general_location' => __('messages.naujamiestis_kauno_street_2b51dfeefc'),
                'latitude' => 54.6744,
                'longitude' => 25.2749,
                'map_x' => 53,
                'map_y' => 55,
                'coordinate_accuracy' => __('messages.demo_business_entrance_f84f2fafdb'),
                'distance_km' => 1.8,
                'travel_minutes' => 8,
                'open_state' => 'open',
                'open_label' => __('messages.open_until_8_00_pm_28fea9219a'),
                'closes_at' => __('messages.8_00_pm_4190a74434'),
                'hours_summary' => __('messages.mon_sat_9_00_am_8_00_pm_sun_10_00_am_6_00_pm_1ed76772f9'),
                'special_hours' => __('messages.inventory_is_not_connected_live_call_before_travel_0019123e4d'),
                'phone' => '+370 600 00425',
                'website' => null,
                'email' => 'demo-store@pawcircle.example',
                'accepted_species' => $allCompanions,
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.leashed_pets_and_secure_carriers_welcome_6d92378291'),
                'fenced' => false,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => false,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'paid',
                'crowd_level' => 'medium',
                'crowd_label' => __('messages.usually_quieter_on_weekday_mornings_83a191990c'),
                'noise_level' => __('messages.typical_retail_environment_59fdd33bb2'),
                'rules' => [__('messages.leash_or_secure_carrier_required_83c0fecef5'), __('messages.prescription_products_require_appropriate_verification_e334828bdb'), __('messages.live_stock_is_not_guaranteed_a9ae3727ec')],
                'features' => [__('messages.pickup_counter_22cd63614c'), __('messages.donation_point_2454bdf153'), __('messages.packaging_recycling_f7b6ca00e8'), __('messages.no_live_animal_sales_4a04be2e32')],
                'accessibility' => [__('messages.step_free_entrance_756d2a7149'), __('messages.wide_aisles_800b8631cf'), __('messages.accessible_parking_c3b5e74625')],
                'safety' => [__('messages.staffed_pickup_desk_2ea0fb5ff1'), __('messages.prescription_boundary_40915df818')],
                'services' => ['food', __('messages.veterinary_diets_de383c7d45'), 'carriers', __('messages.mobility_aids_70bed9eeb0'), __('messages.gps_collars_969ee2e61f'), __('messages.donation_collection_4ff96d797c')],
                'pricing' => ['Pickup reservation' => __('messages.no_fee_64e9e650e5')],
                'rating' => 4.4,
                'review_count' => 88,
                'verified_review_count' => 33,
                'verification' => [
                    'label' => __('messages.demo_business_profile_5d38c6866f'),
                    'scope' => __('messages.prototype_hours_and_service_categories_ab845343ce'),
                    'updated_at' => '2026-07-27',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.hours_checked_3_days_ago_inventory_not_live_a84d6e0660'),
                'recommendation_reason' => __('messages.accessible_pickup_and_a_shelter_donation_point_nearby_d96d612ad8'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('pet-store'),
                'image_alt' => __('messages.pet_care_products_arranged_on_bright_retail_shelving_c11356cac0'),
                'events' => [],
                'base_warnings' => [],
            ],
            'vilnius-animal-aid' => [
                'key' => 'vilnius-animal-aid',
                'name' => __('messages.vilnius_animal_aid_center_2690b52423'),
                'short_name' => __('messages.animal_aid_center_cf0a4ca6a2'),
                'primary_category' => 'shelter',
                'categories' => ['shelter'],
                'category_label' => __('messages.shelter_and_adoption_center_516fe1f1a4'),
                'category_icon' => 'house-heart',
                'summary' => __('messages.a_fictional_verified_shelter_profile_with_scheduled_visi_7444472289'),
                'city' => __('messages.vilnius_c283e0869a'),
                'neighborhood' => __('messages.naujoji_vilnia_fc3940b273'),
                'address' => __('messages.demo_address_parko_g_vilnius_ef772ab6d7'),
                'general_location' => __('messages.naujoji_vilnia_parko_street_9d3468dd73'),
                'latitude' => 54.6947,
                'longitude' => 25.4166,
                'map_x' => 92,
                'map_y' => 31,
                'coordinate_accuracy' => __('messages.demo_visitor_entrance_c6f86bd865'),
                'distance_km' => 11.8,
                'travel_minutes' => 29,
                'open_state' => 'appointment-only',
                'open_label' => __('messages.visits_by_appointment_940e22b22f'),
                'closes_at' => __('messages.office_closes_6_00_pm_d8575d7a3b'),
                'hours_summary' => __('messages.visitor_appointments_tue_sun_11_00_am_6_00_pm_182b80691e'),
                'special_hours' => __('messages.do_not_bring_resident_pets_unless_the_shelter_confirms_a_3722abea23'),
                'phone' => '+370 600 00534',
                'website' => null,
                'email' => 'demo-shelter@pawcircle.example',
                'accepted_species' => ['dog', 'cat', 'rabbit', 'rodent'],
                'accepted_sizes' => $dogSizes,
                'leash_policy' => __('messages.resident_pets_only_by_approved_appointment_f2e7c10db9'),
                'fenced' => true,
                'water' => true,
                'lighting' => true,
                'quiet_zone' => true,
                'parking' => true,
                'wheelchair_access' => true,
                'price_level' => 'free',
                'crowd_level' => 'low',
                'crowd_label' => __('messages.timed_visits_keep_introductions_calm_49bb298af0'),
                'noise_level' => __('messages.quiet_introduction_rooms_available_b0c45dee06'),
                'rules' => [__('messages.book_before_visiting_c5398b3092'), __('messages.do_not_bring_a_resident_pet_without_approval_afcf8c33b4'), __('messages.photography_follows_animal_and_visitor_consent_435bbb4ea8')],
                'features' => [__('messages.adoption_rooms_846e722754'), __('messages.volunteer_desk_8dee7595ed'), __('messages.donation_point_2454bdf153'), __('messages.foster_coordination_d1c35962d4'), __('messages.microchip_checks_ed66377f7b')],
                'accessibility' => [__('messages.step_free_visitor_entrance_e362c324a9'), __('messages.accessible_parking_c3b5e74625'), __('messages.seated_meeting_room_fbd8bb3688')],
                'safety' => [__('messages.controlled_introductions_ed7af8b146'), __('messages.separate_species_areas_2a5d08e1c2'), __('messages.staff_escort_9a0471f830')],
                'services' => ['adoption', 'fostering', 'volunteering', 'donations', __('messages.microchip_checks_ed66377f7b'), __('messages.transport_coordination_ea71d53447')],
                'pricing' => ['Visitor appointment' => __('messages.free_f411a1fb62'), 'Donations' => __('messages.voluntary_9e4fa9ab26')],
                'rating' => 4.8,
                'review_count' => 52,
                'verified_review_count' => 28,
                'verification' => [
                    'label' => __('messages.demo_organization_profile_39ac5f0ed7'),
                    'scope' => __('messages.prototype_identity_visits_and_services_1b2e2350fb'),
                    'updated_at' => '2026-07-29',
                    'tone' => 'demo',
                ],
                'data_freshness' => __('messages.visitor_rules_checked_yesterday_dff43323ff'),
                'recommendation_reason' => __('messages.verified_volunteer_and_adoption_programs_with_accessible_193e27c1e7'),
                'sponsored' => false,
                'allow_events' => true,
                'owner_managed' => true,
                'emergency' => false,
                ...$this->media->primary('shelter'),
                'image_alt' => __('messages.volunteer_sitting_calmly_with_a_shelter_dog_outdoors_7baa9c21f4'),
                'events' => ['shelter-open-house'],
                'base_warnings' => [],
            ],
        ];
    }
}
