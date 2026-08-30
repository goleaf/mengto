<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

final class PetProfileCatalog
{
    public function __construct(private readonly AuthFactory $auth) {}

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        $catalogProfile = match ($slug) {
            'scout' => $this->scout(),
            'nori' => $this->nori(),
            default => null,
        };

        if ($catalogProfile === null) {
            return null;
        }

        $user = $this->auth->guard()->user();
        $user = $user instanceof User ? $user : null;
        $profileQuery = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'breed',
                'visibility',
                'status',
                'profile_data',
            ]);
        $profile = $user === null
            ? null
            : (clone $profileQuery)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('slug', $slug)
                ->first();
        $profile ??= $profileQuery
            ->visibleTo(null)
            ->where('slug', $slug)
            ->first();

        if ($profile === null) {
            return $catalogProfile;
        }

        $profileData = $profile->profile_data ?? [];
        $overrides = array_intersect_key($profileData, array_flip([
            'age',
            'location',
            'member_since',
            'status',
            'story',
            'avatar',
            'profile_image',
            'cover_image',
            'cover_image_small',
            'cover_image_medium',
            'cover_image_alt',
            'card_image',
            'card_image_small',
            'card_image_medium',
            'card_image_alt',
            'traits',
        ]));

        return [
            ...$catalogProfile,
            ...$overrides,
            'slug' => $profile->slug,
            'name' => $profile->name,
            'species' => $profile->species,
            'breed' => $profile->breed ?: $catalogProfile['breed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    public function card(array $pet): array
    {
        $profileUrl = route($pet['route']);

        return [
            'key' => $pet['slug'],
            'name' => $pet['name'],
            'species' => $pet['species'],
            'breed' => $pet['breed'],
            'age' => $pet['age'],
            'owner' => __('messages.mia_carter'),
            'neighborhood' => __('messages.richmond'),
            'status' => $pet['status'],
            'image' => $pet['card_image'],
            'image_small' => $pet['card_image_small'],
            'image_medium' => $pet['card_image_medium'],
            'image_alt' => $pet['card_image_alt'],
            'traits' => $pet['traits'],
            'profile_route' => $pet['route'],
            'profile_parameters' => [],
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('presentation.open_profile', ['name' => $pet['name']]),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function friends(string $slug): array
    {
        return $slug === 'scout'
            ? [
                [
                    'name' => __('messages.mochi'),
                    'species' => __('messages.dog'),
                    'breed' => __('messages.shiba_inu'),
                    'age' => __('messages.3_years'),
                    'owner' => __('messages.ari_jensen'),
                    'neighborhood' => __('messages.pearl_district'),
                    'status' => __('messages.calm_parallel_walk_friend'),
                    'image' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.mochi_a_shiba_inu_standing_outside'),
                    'traits' => [__('messages.parallel_walks'), __('messages.calm_hello')],
                    'profile_route' => null,
                ],
                [
                    'name' => __('messages.juniper'),
                    'species' => __('messages.dog'),
                    'breed' => __('messages.australian_shepherd'),
                    'age' => __('messages.5_years'),
                    'owner' => __('messages.noah_patel'),
                    'neighborhood' => __('messages.sellwood'),
                    'status' => __('messages.trail_companion'),
                    'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.juniper_an_australian_shepherd_sitting_outdoors'),
                    'traits' => [__('messages.trail_walks'), __('messages.high_energy')],
                    'profile_route' => null,
                ],
            ]
            : [
                [
                    'name' => __('messages.pip'),
                    'species' => __('messages.cat'),
                    'breed' => __('messages.domestic_shorthair'),
                    'age' => __('messages.4_years'),
                    'owner' => __('messages.lena_brooks'),
                    'neighborhood' => __('messages.kerns'),
                    'status' => __('messages.window_to_window_friend'),
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.pip_a_cat_looking_up_in_soft_light'),
                    'traits' => ['indoor', __('messages.quiet_company')],
                    'profile_route' => null,
                ],
            ];
    }

    /**
     * @param  array<string, mixed>  $owner
     * @return array<int, array<string, string>>
     */
    public function managers(string $slug, array $owner): array
    {
        $managers = [
            [
                'name' => __('messages.mia_carter'),
                'role' => __('messages.primary_owner'),
                'detail' => __('messages.profile_privacy_care_and_access'),
                'avatar' => $owner['avatar'],
            ],
        ];

        if ($slug === 'scout') {
            $managers[] = [
                'name' => __('messages.alex_carter'),
                'role' => __('messages.caretaker'),
                'detail' => __('messages.walk_and_feeding_reminders'),
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
            ];
        }

        return $managers;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function moments(string $slug): array
    {
        return $slug === 'scout'
            ? $this->scoutMoments()
            : $this->noriMoments();
    }

    /**
     * @return array<string, mixed>
     */
    private function scout(): array
    {
        return [
            'slug' => 'scout',
            'route' => 'pets.scout',
            'name' => __('messages.scout'),
            'handle' => '@mia-carter/scout',
            'role' => __('messages.dog_profile'),
            'species' => __('messages.dog'),
            'breed' => __('messages.border_collie_mix'),
            'age' => __('messages.4_years'),
            'location' => __('messages.richmond_portland_or'),
            'member_since' => __('messages.with_mia_since_2022'),
            'status' => __('messages.available_for_park_walks'),
            'story' => __('messages.scout_is_happiest_when_a_walk_has_a_destination_a_few_new_smells_and_enough_time_to_watch_the_world_at_home_he_settles_quickly_beside_mia_and_takes_his_role_as'),
            'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass'),
            'card_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => __('messages.scout_resting_outside'),
            'traits' => ['friendly', 'active', __('messages.well_trained'), __('messages.cautious_with_cats')],
            'facts' => [
                ['label' => __('messages.species'), 'value' => __('messages.dog')],
                ['label' => __('messages.breed'), 'value' => __('messages.border_collie_mix')],
                ['label' => __('messages.age'), 'value' => __('messages.4_years')],
                ['label' => __('messages.size'), 'value' => __('messages.medium_42_lb')],
                ['label' => __('messages.activity'), 'value' => __('messages.high_with_a_calm_indoor_routine')],
            ],
            'care' => [
                ['label' => __('messages.best_walk'), 'value' => __('messages.45_60_minutes')],
                ['label' => __('messages.vaccinations'), 'value' => __('messages.up_to_date')],
                ['label' => __('messages.food_note'), 'value' => __('messages.chicken_free_treats')],
                ['label' => __('messages.special_care'), 'value' => __('messages.slow_introductions_to_cats')],
            ],
            'compatibility' => [
                ['label' => __('messages.dogs'), 'value' => __('messages.friendly_after_a_calm_hello')],
                ['label' => __('messages.children'), 'value' => __('messages.comfortable_with_older_children')],
                ['label' => __('messages.cats'), 'value' => __('messages.needs_a_slow_introduction')],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=675&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=324&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=506&q=82',
                    'alt' => __('messages.scout_lying_in_grass_behind_a_tennis_ball'),
                    'caption' => __('messages.waiting_for_one_more_throw'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.scout_resting_on_a_wooden_porch'),
                    'caption' => __('messages.settling_in_after_a_neighborhood_walk'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.scout_catching_a_yellow_frisbee_on_grass'),
                    'caption' => __('messages.the_catch_that_ended_fetch_practice'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function nori(): array
    {
        return [
            'slug' => 'nori',
            'route' => 'pets.nori',
            'name' => __('messages.nori'),
            'handle' => '@mia-carter/nori',
            'role' => __('messages.cat_profile'),
            'species' => __('messages.cat'),
            'breed' => __('messages.tabby'),
            'age' => __('messages.2_years'),
            'location' => __('messages.richmond_portland_or'),
            'member_since' => __('messages.with_mia_since_2024'),
            'status' => __('messages.indoor_window_watching_expert'),
            'story' => __('messages.nori_approaches_new_things_from_a_safe_perch_takes_afternoon_sun_very_seriously_and_has_a_quiet_chirp_reserved_for_birds_outside_the_kitchen_window'),
            'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('messages.nori_a_tabby_cat_resting_near_a_bright_window'),
            'card_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera'),
            'traits' => ['calm', 'independent', 'curious', 'indoor'],
            'facts' => [
                ['label' => __('messages.species'), 'value' => __('messages.cat')],
                ['label' => __('messages.breed'), 'value' => __('messages.tabby')],
                ['label' => __('messages.age'), 'value' => __('messages.2_years')],
                ['label' => __('messages.size'), 'value' => __('messages.small_9_lb')],
                ['label' => __('messages.activity'), 'value' => __('messages.quiet_mornings_curious_afternoons')],
            ],
            'care' => [
                ['label' => __('messages.home'), 'value' => __('messages.indoor_only')],
                ['label' => __('messages.food_note'), 'value' => __('messages.small_scheduled_meals')],
                ['label' => __('messages.favorite_routine'), 'value' => __('messages.window_perch_after_breakfast')],
                ['label' => __('messages.special_care'), 'value' => __('messages.needs_a_quiet_room_for_introductions')],
            ],
            'compatibility' => [
                ['label' => __('messages.cats'), 'value' => __('messages.curious_at_a_distance')],
                ['label' => __('messages.dogs'), 'value' => __('messages.prefers_calm_separated_spaces')],
                ['label' => __('messages.children'), 'value' => __('messages.comfortable_with_quiet_older_children')],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.nori_looking_toward_the_camera'),
                    'caption' => __('messages.morning_inspection_complete'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.a_tabby_cat_looking_up_in_soft_light'),
                    'caption' => __('messages.listening_for_the_treat_drawer'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.a_tabby_cat_resting_near_a_window'),
                    'caption' => __('messages.the_preferred_afternoon_office'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function scoutMoments(): array
    {
        return [
            [
                'author' => __('messages.mia_carter'),
                'pet' => __('messages.scout'),
                'time' => __('messages.yesterday'),
                'datetime' => '2026-07-28T17:30:00-07:00',
                'body' => __('messages.scout_locked_onto_the_yellow_frisbee_and_caught_it_on_the_second_try_the_trip_home_was_much_quieter'),
                'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_catching_a_yellow_frisbee_on_the_grass'),
                'tags' => ['fetch', __('messages.scout')],
                'stats' => ['paws' => '94', 'replies' => '16'],
            ],
            [
                'author' => __('messages.mia_carter'),
                'pet' => __('messages.scout'),
                'time' => __('messages.4_days_ago'),
                'datetime' => '2026-07-25T16:00:00-07:00',
                'body' => __('messages.after_a_calm_neighborhood_walk_scout_claimed_the_porch_and_watched_the_trees_until_dinner'),
                'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_resting_on_a_wooden_porch'),
                'tags' => [__('messages.slow_afternoon'), __('messages.small_wins')],
                'stats' => ['paws' => '121', 'replies' => '21'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function noriMoments(): array
    {
        return [
            [
                'author' => __('messages.mia_carter'),
                'pet' => __('messages.nori'),
                'time' => __('messages.2_days_ago'),
                'datetime' => '2026-07-27T14:10:00-07:00',
                'body' => __('messages.nori_found_a_new_stripe_of_afternoon_sun_and_politely_moved_every_notebook_out_of_it'),
                'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.nori_resting_in_a_warm_patch_of_light'),
                'tags' => [__('messages.nori'), __('messages.indoor_life')],
                'stats' => ['paws' => '76', 'replies' => '9'],
            ],
        ];
    }
}
