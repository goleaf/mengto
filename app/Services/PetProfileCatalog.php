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
            'owner' => __('messages.mia_carter_0e5b29cc3b'),
            'neighborhood' => __('messages.richmond_128b2a6b11'),
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
                    'name' => __('messages.mochi_95114c81f3'),
                    'species' => __('messages.dog_0eb129bf94'),
                    'breed' => __('messages.shiba_inu_7d025987e3'),
                    'age' => __('messages.3_years_50a85bc562'),
                    'owner' => __('messages.ari_jensen_6c670df410'),
                    'neighborhood' => __('messages.pearl_district_af25f9947a'),
                    'status' => __('messages.calm_parallel_walk_friend_7e5113dc7c'),
                    'image' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1568038759482-09c6b4e3224f?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.mochi_a_shiba_inu_standing_outside_b07003356f'),
                    'traits' => [__('messages.parallel_walks_af01986300'), __('messages.calm_hello_9ac02e9f8e')],
                    'profile_route' => null,
                ],
                [
                    'name' => __('messages.juniper_fe6a448ec9'),
                    'species' => __('messages.dog_0eb129bf94'),
                    'breed' => __('messages.australian_shepherd_de5183c21d'),
                    'age' => __('messages.5_years_9d8ee593ed'),
                    'owner' => __('messages.noah_patel_147a9793ed'),
                    'neighborhood' => __('messages.sellwood_d70a1edd4b'),
                    'status' => __('messages.trail_companion_0a3d2c1c64'),
                    'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.juniper_an_australian_shepherd_sitting_outdoors_6037efa2e5'),
                    'traits' => [__('messages.trail_walks_e65914f579'), __('messages.high_energy_e3873bb814')],
                    'profile_route' => null,
                ],
            ]
            : [
                [
                    'name' => __('messages.pip_cf64881060'),
                    'species' => __('messages.cat_48735c4fae'),
                    'breed' => __('messages.domestic_shorthair_e704975a6c'),
                    'age' => __('messages.4_years_cfd73a0bc4'),
                    'owner' => __('messages.lena_brooks_ca42e74116'),
                    'neighborhood' => __('messages.kerns_f59b072fd3'),
                    'status' => __('messages.window_to_window_friend_4e996cdbf0'),
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'image_alt' => __('messages.pip_a_cat_looking_up_in_soft_light_79909d79bd'),
                    'traits' => ['indoor', __('messages.quiet_company_be15c2dfca')],
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
                'name' => __('messages.mia_carter_0e5b29cc3b'),
                'role' => __('messages.primary_owner_61b49429c7'),
                'detail' => __('messages.profile_privacy_care_and_access_dd4d6c0a80'),
                'avatar' => $owner['avatar'],
            ],
        ];

        if ($slug === 'scout') {
            $managers[] = [
                'name' => __('messages.alex_carter_805f38f620'),
                'role' => __('messages.caretaker_04066ab4ae'),
                'detail' => __('messages.walk_and_feeding_reminders_e3c4bba96a'),
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
            'name' => __('messages.scout_8a1db462be'),
            'handle' => '@mia-carter/scout',
            'role' => __('messages.dog_profile_48b6dcd802'),
            'species' => __('messages.dog_0eb129bf94'),
            'breed' => __('messages.border_collie_mix_9b8f12e319'),
            'age' => __('messages.4_years_cfd73a0bc4'),
            'location' => __('messages.richmond_portland_or_fdcefc3192'),
            'member_since' => __('messages.with_mia_since_2022_4666ddb9e2'),
            'status' => __('messages.available_for_park_walks_0a5d4afdb7'),
            'story' => __('messages.scout_is_happiest_when_a_walk_has_a_destination_a_few_ne_f61d87a2ab'),
            'avatar' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass_4abc84adab'),
            'card_image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => __('messages.scout_resting_outside_2cb5005b17'),
            'traits' => ['friendly', 'active', __('messages.well_trained_7b07dfaf0a'), __('messages.cautious_with_cats_8099907b7c')],
            'facts' => [
                ['label' => __('messages.species_56205e12c2'), 'value' => __('messages.dog_0eb129bf94')],
                ['label' => __('messages.breed_d1ac8a8093'), 'value' => __('messages.border_collie_mix_9b8f12e319')],
                ['label' => __('messages.age_39b7370f30'), 'value' => __('messages.4_years_cfd73a0bc4')],
                ['label' => __('messages.size_1af8519073'), 'value' => __('messages.medium_42_lb_430f5ef830')],
                ['label' => __('messages.activity_38da1505ca'), 'value' => __('messages.high_with_a_calm_indoor_routine_ee3839a6ae')],
            ],
            'care' => [
                ['label' => __('messages.best_walk_c1c14aecbe'), 'value' => __('messages.45_60_minutes_c382b92006')],
                ['label' => __('messages.vaccinations_ed3861e631'), 'value' => __('messages.up_to_date_ce29b7f85b')],
                ['label' => __('messages.food_note_695588bccb'), 'value' => __('messages.chicken_free_treats_50fa808d97')],
                ['label' => __('messages.special_care_63f855dd8f'), 'value' => __('messages.slow_introductions_to_cats_9273df9d22')],
            ],
            'compatibility' => [
                ['label' => __('messages.dogs_246b0deffb'), 'value' => __('messages.friendly_after_a_calm_hello_d0fd9a769b')],
                ['label' => __('messages.children_3583a358a5'), 'value' => __('messages.comfortable_with_older_children_882e58b6f6')],
                ['label' => __('messages.cats_ec05d70c6f'), 'value' => __('messages.needs_a_slow_introduction_ab8e26f4fb')],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=675&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=324&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=506&q=82',
                    'alt' => __('messages.scout_lying_in_grass_behind_a_tennis_ball_e7cfee5e55'),
                    'caption' => __('messages.waiting_for_one_more_throw_24b06fa460'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.scout_resting_on_a_wooden_porch_0fce6ea345'),
                    'caption' => __('messages.settling_in_after_a_neighborhood_walk_e8b52ba9b8'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.scout_catching_a_yellow_frisbee_on_grass_4927a451ce'),
                    'caption' => __('messages.the_catch_that_ended_fetch_practice_61791f46c2'),
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
            'name' => __('messages.nori_a64203ba20'),
            'handle' => '@mia-carter/nori',
            'role' => __('messages.cat_profile_eed6c2d74a'),
            'species' => __('messages.cat_48735c4fae'),
            'breed' => __('messages.tabby_2631668147'),
            'age' => __('messages.2_years_7dab2372ff'),
            'location' => __('messages.richmond_portland_or_fdcefc3192'),
            'member_since' => __('messages.with_mia_since_2024_828d24a791'),
            'status' => __('messages.indoor_window_watching_expert_708a61ad03'),
            'story' => __('messages.nori_approaches_new_things_from_a_safe_perch_takes_after_6652422347'),
            'avatar' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'profile_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'cover_image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('messages.nori_a_tabby_cat_resting_near_a_bright_window_18b3fef25c'),
            'card_image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
            'card_image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
            'card_image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
            'card_image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera_3f2b66069e'),
            'traits' => ['calm', 'independent', 'curious', 'indoor'],
            'facts' => [
                ['label' => __('messages.species_56205e12c2'), 'value' => __('messages.cat_48735c4fae')],
                ['label' => __('messages.breed_d1ac8a8093'), 'value' => __('messages.tabby_2631668147')],
                ['label' => __('messages.age_39b7370f30'), 'value' => __('messages.2_years_7dab2372ff')],
                ['label' => __('messages.size_1af8519073'), 'value' => __('messages.small_9_lb_6cc6ce5e98')],
                ['label' => __('messages.activity_38da1505ca'), 'value' => __('messages.quiet_mornings_curious_afternoons_acd26dcf07')],
            ],
            'care' => [
                ['label' => __('messages.home_3a78695388'), 'value' => __('messages.indoor_only_8df00d3855')],
                ['label' => __('messages.food_note_695588bccb'), 'value' => __('messages.small_scheduled_meals_d5eb159625')],
                ['label' => __('messages.favorite_routine_ad58b293ea'), 'value' => __('messages.window_perch_after_breakfast_cbc21fd45d')],
                ['label' => __('messages.special_care_63f855dd8f'), 'value' => __('messages.needs_a_quiet_room_for_introductions_2dfbd49515')],
            ],
            'compatibility' => [
                ['label' => __('messages.cats_ec05d70c6f'), 'value' => __('messages.curious_at_a_distance_12aefd3198')],
                ['label' => __('messages.dogs_246b0deffb'), 'value' => __('messages.prefers_calm_separated_spaces_4842b5b459')],
                ['label' => __('messages.children_3583a358a5'), 'value' => __('messages.comfortable_with_quiet_older_children_b87ccfaf20')],
            ],
            'gallery' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.nori_looking_toward_the_camera_9e7243fa64'),
                    'caption' => __('messages.morning_inspection_complete_0250ca5d25'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.a_tabby_cat_looking_up_in_soft_light_2e144039c3'),
                    'caption' => __('messages.listening_for_the_treat_drawer_b4f33d2e96'),
                ],
                [
                    'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                    'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                    'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                    'alt' => __('messages.a_tabby_cat_resting_near_a_window_25e472f914'),
                    'caption' => __('messages.the_preferred_afternoon_office_274cf580f0'),
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
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'pet' => __('messages.scout_8a1db462be'),
                'time' => __('messages.yesterday_566181254b'),
                'datetime' => '2026-07-28T17:30:00-07:00',
                'body' => __('messages.scout_locked_onto_the_yellow_frisbee_and_caught_it_on_th_f9a8c62c68'),
                'image' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1625679895477-526b21a77f0c?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_catching_a_yellow_frisbee_on_the_grass_2b0d0e4b11'),
                'tags' => ['fetch', __('messages.scout_8a1db462be')],
                'stats' => ['paws' => '94', 'replies' => '16'],
            ],
            [
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'pet' => __('messages.scout_8a1db462be'),
                'time' => __('messages.4_days_ago_6faa883aa9'),
                'datetime' => '2026-07-25T16:00:00-07:00',
                'body' => __('messages.after_a_calm_neighborhood_walk_scout_claimed_the_porch_a_555d80c9b5'),
                'image' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1621169225409-5de158d10015?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.scout_resting_on_a_wooden_porch_0fce6ea345'),
                'tags' => [__('messages.slow_afternoon_101d3ac946'), __('messages.small_wins_965630ccc8')],
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
                'author' => __('messages.mia_carter_0e5b29cc3b'),
                'pet' => __('messages.nori_a64203ba20'),
                'time' => __('messages.2_days_ago_174ebc0fcf'),
                'datetime' => '2026-07-27T14:10:00-07:00',
                'body' => __('messages.nori_found_a_new_stripe_of_afternoon_sun_and_politely_mo_2b122cb0e1'),
                'image' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=1200&h=900&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=576&h=432&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=900&h=675&q=82',
                'image_alt' => __('messages.nori_resting_in_a_warm_patch_of_light_cd67fe5bc2'),
                'tags' => [__('messages.nori_a64203ba20'), __('messages.indoor_life_f927e2f4a1')],
                'stats' => ['paws' => '76', 'replies' => '9'],
            ],
        ];
    }
}
