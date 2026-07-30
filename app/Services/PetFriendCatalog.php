<?php

namespace App\Services;

final class PetFriendCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function records(): array
    {
        return [
            'pet-scout' => [
                'id' => 'pet-scout',
                'slug' => 'scout',
                'name' => __('messages.scout_8a1db462be'),
                'handle' => '@mia-carter/scout',
                'owner' => __('messages.mia_carter_0e5b29cc3b'),
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.border_collie_mix_9b8f12e319'),
                'age' => __('messages.4_years_cfd73a0bc4'),
                'size' => __('messages.medium_8e588cd187'),
                'location' => __('messages.richmond_portland_45cfbdb042'),
                'activity' => __('messages.active_9234069589'),
                'play_style' => __('messages.parallel_walks_and_fetch_19efb2afc5'),
                'description' => __('messages.focused_trail_walks_calm_introductions_and_structured_pl_f9b01cef10'),
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass_4abc84adab'),
                'route_name' => 'pets.scout',
                'route_parameters' => [],
                'intents' => ['walk', 'play', 'training', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-nori' => [
                'id' => 'pet-nori',
                'slug' => 'nori',
                'name' => __('messages.nori_a64203ba20'),
                'handle' => '@mia-carter/nori',
                'owner' => __('messages.mia_carter_0e5b29cc3b'),
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => __('messages.cat_48735c4fae'),
                'breed' => __('messages.tabby_2631668147'),
                'age' => __('messages.2_years_7dab2372ff'),
                'size' => __('messages.small_5263293fc2'),
                'location' => __('messages.richmond_portland_45cfbdb042'),
                'activity' => __('messages.calm_38f6b83e2d'),
                'play_style' => __('messages.quiet_company_at_a_distance_05f5ec63ff'),
                'description' => __('messages.indoor_routines_window_watching_and_slow_introductions_83a83aa19a'),
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera_3f2b66069e'),
                'route_name' => 'pets.nori',
                'route_parameters' => [],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-mochi' => [
                'id' => 'pet-mochi',
                'slug' => 'mochi',
                'name' => __('messages.mochi_95114c81f3'),
                'handle' => '@ari-jensen/mochi',
                'owner' => __('messages.ari_jensen_6c670df410'),
                'owner_handle' => '@ari-jensen',
                'owner_conversation' => 'ari',
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.shiba_inu_7d025987e3'),
                'age' => __('messages.3_years_50a85bc562'),
                'size' => __('messages.medium_8e588cd187'),
                'location' => __('messages.pearl_district_portland_b6573f597e'),
                'activity' => __('messages.moderate_5c42afc7a2'),
                'play_style' => __('messages.parallel_walks_and_calm_greetings_df43eab9cd'),
                'description' => __('messages.a_city_dog_who_prefers_steady_routes_and_low_pressure_he_482d14a187'),
                'image' => 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.mochi_a_shiba_inu_looking_toward_the_camera_f32e0115f6'),
                'route_name' => 'neighbors.ari',
                'route_parameters' => [],
                'intents' => ['walk', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-juniper' => [
                'id' => 'pet-juniper',
                'slug' => 'juniper',
                'name' => __('messages.juniper_fe6a448ec9'),
                'handle' => '@noah-and-juniper/juniper',
                'owner' => __('messages.noah_kim_1ff9787ac4'),
                'owner_handle' => '@noah-and-juniper',
                'owner_conversation' => 'noah',
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.australian_shepherd_de5183c21d'),
                'age' => __('messages.5_years_9d8ee593ed'),
                'size' => __('messages.medium_8e588cd187'),
                'location' => __('messages.sellwood_portland_d5578f4db2'),
                'activity' => __('messages.active_9234069589'),
                'play_style' => __('messages.trail_walks_with_careful_introductions_c33f6637ad'),
                'description' => __('messages.thoughtful_on_first_meetings_and_confident_once_a_routin_d0d9e2d61e'),
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.juniper_an_australian_shepherd_sitting_outdoors_6037efa2e5'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.juniper_fe6a448ec9')],
                'intents' => ['walk', 'training'],
                'private' => true,
                'verified' => false,
            ],
            'pet-luna-labrador' => [
                'id' => 'pet-luna-labrador',
                'slug' => 'luna',
                'name' => __('messages.luna_9d77a24d0f'),
                'handle' => '@zoe-and-luna/luna',
                'owner' => __('messages.zoe_patel_330ba10552'),
                'owner_handle' => '@zoe-and-luna',
                'owner_conversation' => '',
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.labrador_retriever_7b03c276c9'),
                'age' => __('messages.2_years_7dab2372ff'),
                'size' => __('messages.large_ab80540d98'),
                'location' => __('messages.northwest_portland_46903a0541'),
                'activity' => __('messages.very_active_57c2aae337'),
                'play_style' => __('messages.chase_fetch_and_open_space_walks_053083305c'),
                'description' => __('messages.a_young_social_labrador_looking_for_active_outdoor_compa_54aaed7307'),
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.luna_a_yellow_labrador_sitting_outdoors_ba9df14551'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.luna_9d77a24d0f')],
                'intents' => ['walk', 'play', 'training'],
                'private' => false,
                'verified' => false,
            ],
            'pet-pip' => [
                'id' => 'pet-pip',
                'slug' => 'pip',
                'name' => __('messages.pip_cf64881060'),
                'handle' => '@lena-brooks/pip',
                'owner' => __('messages.lena_brooks_ca42e74116'),
                'owner_handle' => '@lena-brooks',
                'owner_conversation' => 'lena',
                'species' => __('messages.cat_48735c4fae'),
                'breed' => __('messages.domestic_shorthair_e704975a6c'),
                'age' => __('messages.4_years_cfd73a0bc4'),
                'size' => __('messages.small_5263293fc2'),
                'location' => __('messages.kerns_portland_b8b85a4ef8'),
                'activity' => __('messages.calm_38f6b83e2d'),
                'play_style' => __('messages.quiet_company_and_window_visits_9a9ebdd8f6'),
                'description' => __('messages.a_relaxed_indoor_cat_who_enjoys_familiar_voices_and_pati_22743ae2f8'),
                'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.pip_a_cat_looking_up_in_soft_light_79909d79bd'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.pip_cf64881060')],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-olive-rabbit' => [
                'id' => 'pet-olive-rabbit',
                'slug' => 'olive',
                'name' => __('messages.olive_3038ab334a'),
                'handle' => '@priya-fosters/olive',
                'owner' => __('messages.priya_shah_8925523814'),
                'owner_handle' => '@priya-fosters',
                'owner_conversation' => 'priya',
                'species' => __('messages.rabbit_4ea93dfb21'),
                'breed' => __('messages.mini_rex_c01b8d65f9'),
                'age' => __('messages.3_years_50a85bc562'),
                'size' => __('messages.small_5263293fc2'),
                'location' => __('messages.sellwood_portland_d5578f4db2'),
                'activity' => __('messages.calm_38f6b83e2d'),
                'play_style' => __('messages.separate_space_enrichment_6c8ee068f6'),
                'description' => __('messages.a_foster_rabbit_whose_social_time_always_uses_protected__d22723deb1'),
                'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.olive_a_small_rabbit_sitting_in_grass_f8ebd3bae6'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.olive_3038ab334a')],
                'intents' => ['play', 'neighbor'],
                'private' => true,
                'verified' => true,
            ],
            'pet-coco-spaniel' => [
                'id' => 'pet-coco-spaniel',
                'slug' => 'coco',
                'name' => __('messages.coco_2a6aef767a'),
                'handle' => '@maya-and-coco/coco',
                'owner' => __('messages.maya_chen_748718a66c'),
                'owner_handle' => '@maya-and-coco',
                'owner_conversation' => '',
                'species' => __('messages.dog_0eb129bf94'),
                'breed' => __('messages.english_cocker_spaniel_2478742162'),
                'age' => __('messages.4_years_cfd73a0bc4'),
                'size' => __('messages.medium_8e588cd187'),
                'location' => __('messages.richmond_portland_45cfbdb042'),
                'activity' => __('messages.active_9234069589'),
                'play_style' => __('messages.sniff_walks_and_gentle_chase_0ce0ca900b'),
                'description' => __('messages.a_local_spaniel_who_likes_structured_greetings_and_woodl_9fb91ffba8'),
                'image' => 'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.coco_a_brown_spaniel_sitting_outdoors_29bc14eab9'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.coco_2a6aef767a')],
                'intents' => ['walk', 'play', 'neighbor'],
                'private' => false,
                'verified' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return $this->records()[$id] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function owned(): array
    {
        return array_intersect_key($this->records(), array_flip(['pet-scout', 'pet-nori']));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function candidates(string $source): array
    {
        $records = $this->records();

        return array_values(array_filter(
            $records,
            static fn (array $record): bool => $record['id'] !== $source
                && ! in_array($record['id'], ['pet-scout', 'pet-nori'], true),
        ));
    }

    /**
     * @return array{reason: string, shared: array<int, string>, cautions: array<int, string>, score: int}
     */
    public function compatibility(string $source, string $target): array
    {
        $sourcePet = $this->find($source);
        $targetPet = $this->find($target);

        if ($sourcePet === null || $targetPet === null) {
            return [
                'reason' => __('messages.compatibility_details_are_unavailable_78df6c14f8'),
                'shared' => [],
                'cautions' => [__('messages.owners_should_review_both_profiles_before_arranging_cont_34e8b57e6d')],
                'score' => 0,
            ];
        }

        $sameSpecies = $sourcePet['species'] === $targetPet['species'];
        $sameLocation = str_contains($sourcePet['location'], __('messages.portland_f514070e53'))
            && str_contains($targetPet['location'], __('messages.portland_f514070e53'));
        $sharedIntents = array_values(array_intersect($sourcePet['intents'], $targetPet['intents']));
        $shared = [];

        if ($sameSpecies) {
            $shared[] = __('presentation.same_species_profiles', ['species' => $sourcePet['species']]);
        }

        if ($sameLocation) {
            $shared[] = __('messages.both_live_in_the_portland_area_479f7f2ea5');
        }

        foreach (array_slice($sharedIntents, 0, 2) as $intent) {
            $shared[] = match ($intent) {
                'walk' => __('messages.both_are_open_to_shared_walks_c0616831b1'),
                'play' => __('messages.both_are_open_to_social_play_a25fefaddb'),
                'training' => __('messages.both_enjoy_structured_training_3bac2c50ac'),
                default => __('messages.both_are_open_to_nearby_friends_d1dec94706'),
            };
        }

        $cautions = [];

        if (! $sameSpecies) {
            $cautions[] = __('messages.different_species_need_protected_spaces_and_owner_led_in_fa99fdc2f1');
        }

        if ($sourcePet['activity'] !== $targetPet['activity']) {
            $cautions[] = __('messages.their_activity_levels_differ_so_start_with_a_short_calm__84be3890f6');
        }

        if ($target === 'pet-juniper') {
            $cautions[] = __('messages.juniper_prefers_extra_distance_during_first_introduction_b28d5a965f');
        }

        if ($target === 'pet-olive-rabbit') {
            $cautions[] = __('messages.olive_should_never_share_an_unrestricted_play_space_with_1660180231');
        }

        $score = min(98, 52 + (count($shared) * 12) - (count($cautions) * 6));

        return [
            'reason' => $sameSpecies
                ? __('messages.their_profiles_share_routines_that_may_support_an_owner__938626b159')
                : __('messages.their_owners_share_local_interests_but_any_contact_needs_1fb324b04b'),
            'shared' => $shared,
            'cautions' => $cautions,
            'score' => max(20, $score),
        ];
    }
}
