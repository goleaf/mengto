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
                'name' => __('messages.scout'),
                'handle' => '@mia-carter/scout',
                'owner' => __('messages.mia_carter'),
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => __('messages.dog'),
                'breed' => __('messages.border_collie_mix'),
                'age' => __('messages.4_years'),
                'size' => __('messages.medium'),
                'location' => __('messages.richmond_portland'),
                'activity' => __('messages.active'),
                'play_style' => __('messages.parallel_walks_and_fetch'),
                'description' => __('messages.focused_trail_walks_calm_introductions_and_structured_play'),
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass'),
                'route_name' => 'pets.scout',
                'route_parameters' => [],
                'intents' => ['walk', 'play', 'training', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-nori' => [
                'id' => 'pet-nori',
                'slug' => 'nori',
                'name' => __('messages.nori'),
                'handle' => '@mia-carter/nori',
                'owner' => __('messages.mia_carter'),
                'owner_handle' => '@mia-carter',
                'owner_conversation' => '',
                'species' => __('messages.cat'),
                'breed' => __('messages.tabby'),
                'age' => __('messages.2_years'),
                'size' => __('messages.small'),
                'location' => __('messages.richmond_portland'),
                'activity' => __('messages.calm'),
                'play_style' => __('messages.quiet_company_at_a_distance'),
                'description' => __('messages.indoor_routines_window_watching_and_slow_introductions'),
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.nori_a_tabby_cat_looking_toward_the_camera'),
                'route_name' => 'pets.nori',
                'route_parameters' => [],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-mochi' => [
                'id' => 'pet-mochi',
                'slug' => 'mochi',
                'name' => __('messages.mochi'),
                'handle' => '@ari-jensen/mochi',
                'owner' => __('messages.ari_jensen'),
                'owner_handle' => '@ari-jensen',
                'owner_conversation' => 'ari',
                'species' => __('messages.dog'),
                'breed' => __('messages.shiba_inu'),
                'age' => __('messages.3_years'),
                'size' => __('messages.medium'),
                'location' => __('messages.pearl_district_portland'),
                'activity' => __('messages.moderate'),
                'play_style' => __('messages.parallel_walks_and_calm_greetings'),
                'description' => __('messages.a_city_dog_who_prefers_steady_routes_and_low_pressure_hellos'),
                'image' => 'https://images.unsplash.com/photo-1769635325695-dead509dc5b3?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.mochi_a_shiba_inu_looking_toward_the_camera'),
                'route_name' => 'neighbors.ari',
                'route_parameters' => [],
                'intents' => ['walk', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-juniper' => [
                'id' => 'pet-juniper',
                'slug' => 'juniper',
                'name' => __('messages.juniper'),
                'handle' => '@noah-and-juniper/juniper',
                'owner' => __('messages.noah_kim'),
                'owner_handle' => '@noah-and-juniper',
                'owner_conversation' => 'noah',
                'species' => __('messages.dog'),
                'breed' => __('messages.australian_shepherd'),
                'age' => __('messages.5_years'),
                'size' => __('messages.medium'),
                'location' => __('messages.sellwood_portland'),
                'activity' => __('messages.active'),
                'play_style' => __('messages.trail_walks_with_careful_introductions'),
                'description' => __('messages.thoughtful_on_first_meetings_and_confident_once_a_routine_is_familiar'),
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.juniper_an_australian_shepherd_sitting_outdoors'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.juniper')],
                'intents' => ['walk', 'training'],
                'private' => true,
                'verified' => false,
            ],
            'pet-luna-labrador' => [
                'id' => 'pet-luna-labrador',
                'slug' => 'luna',
                'name' => __('messages.luna'),
                'handle' => '@zoe-and-luna/luna',
                'owner' => __('messages.zoe_patel'),
                'owner_handle' => '@zoe-and-luna',
                'owner_conversation' => '',
                'species' => __('messages.dog'),
                'breed' => __('messages.labrador_retriever'),
                'age' => __('messages.2_years'),
                'size' => __('messages.large'),
                'location' => __('messages.northwest_portland'),
                'activity' => __('messages.very_active'),
                'play_style' => __('messages.chase_fetch_and_open_space_walks'),
                'description' => __('messages.a_young_social_labrador_looking_for_active_outdoor_companions'),
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.luna_a_yellow_labrador_sitting_outdoors'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.luna')],
                'intents' => ['walk', 'play', 'training'],
                'private' => false,
                'verified' => false,
            ],
            'pet-pip' => [
                'id' => 'pet-pip',
                'slug' => 'pip',
                'name' => __('messages.pip'),
                'handle' => '@lena-brooks/pip',
                'owner' => __('messages.lena_brooks'),
                'owner_handle' => '@lena-brooks',
                'owner_conversation' => 'lena',
                'species' => __('messages.cat'),
                'breed' => __('messages.domestic_shorthair'),
                'age' => __('messages.4_years'),
                'size' => __('messages.small'),
                'location' => __('messages.kerns_portland'),
                'activity' => __('messages.calm'),
                'play_style' => __('messages.quiet_company_and_window_visits'),
                'description' => __('messages.a_relaxed_indoor_cat_who_enjoys_familiar_voices_and_patient_company'),
                'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.pip_a_cat_looking_up_in_soft_light'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.pip')],
                'intents' => ['play', 'neighbor'],
                'private' => false,
                'verified' => true,
            ],
            'pet-olive-rabbit' => [
                'id' => 'pet-olive-rabbit',
                'slug' => 'olive',
                'name' => __('messages.olive'),
                'handle' => '@priya-fosters/olive',
                'owner' => __('messages.priya_shah'),
                'owner_handle' => '@priya-fosters',
                'owner_conversation' => 'priya',
                'species' => __('messages.rabbit'),
                'breed' => __('messages.mini_rex'),
                'age' => __('messages.3_years'),
                'size' => __('messages.small'),
                'location' => __('messages.sellwood_portland'),
                'activity' => __('messages.calm'),
                'play_style' => __('messages.separate_space_enrichment'),
                'description' => __('messages.a_foster_rabbit_whose_social_time_always_uses_protected_separate_spaces'),
                'image' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.olive_a_small_rabbit_sitting_in_grass'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.olive')],
                'intents' => ['play', 'neighbor'],
                'private' => true,
                'verified' => true,
            ],
            'pet-coco-spaniel' => [
                'id' => 'pet-coco-spaniel',
                'slug' => 'coco',
                'name' => __('messages.coco'),
                'handle' => '@maya-and-coco/coco',
                'owner' => __('messages.maya_chen'),
                'owner_handle' => '@maya-and-coco',
                'owner_conversation' => '',
                'species' => __('messages.dog'),
                'breed' => __('messages.english_cocker_spaniel'),
                'age' => __('messages.4_years'),
                'size' => __('messages.medium'),
                'location' => __('messages.richmond_portland'),
                'activity' => __('messages.active'),
                'play_style' => __('messages.sniff_walks_and_gentle_chase'),
                'description' => __('messages.a_local_spaniel_who_likes_structured_greetings_and_woodland_routes'),
                'image' => 'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
                'image_alt' => __('messages.coco_a_brown_spaniel_sitting_outdoors'),
                'route_name' => 'pets.index',
                'route_parameters' => ['q' => __('messages.coco')],
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
                'reason' => __('messages.compatibility_details_are_unavailable'),
                'shared' => [],
                'cautions' => [__('messages.owners_should_review_both_profiles_before_arranging_contact')],
                'score' => 0,
            ];
        }

        $sameSpecies = $sourcePet['species'] === $targetPet['species'];
        $sameLocation = str_contains($sourcePet['location'], __('messages.portland'))
            && str_contains($targetPet['location'], __('messages.portland'));
        $sharedIntents = array_values(array_intersect($sourcePet['intents'], $targetPet['intents']));
        $shared = [];

        if ($sameSpecies) {
            $shared[] = __('presentation.same_species_profiles', ['species' => $sourcePet['species']]);
        }

        if ($sameLocation) {
            $shared[] = __('messages.both_live_in_the_portland_area');
        }

        foreach (array_slice($sharedIntents, 0, 2) as $intent) {
            $shared[] = match ($intent) {
                'walk' => __('messages.both_are_open_to_shared_walks'),
                'play' => __('messages.both_are_open_to_social_play'),
                'training' => __('messages.both_enjoy_structured_training'),
                default => __('messages.both_are_open_to_nearby_friends'),
            };
        }

        $cautions = [];

        if (! $sameSpecies) {
            $cautions[] = __('messages.different_species_need_protected_spaces_and_owner_led_introductions');
        }

        if ($sourcePet['activity'] !== $targetPet['activity']) {
            $cautions[] = __('messages.their_activity_levels_differ_so_start_with_a_short_calm_meeting');
        }

        if ($target === 'pet-juniper') {
            $cautions[] = __('messages.juniper_prefers_extra_distance_during_first_introductions');
        }

        if ($target === 'pet-olive-rabbit') {
            $cautions[] = __('messages.olive_should_never_share_an_unrestricted_play_space_with_another_species');
        }

        $score = min(98, 52 + (count($shared) * 12) - (count($cautions) * 6));

        return [
            'reason' => $sameSpecies
                ? __('messages.their_profiles_share_routines_that_may_support_an_owner_led_introduction')
                : __('messages.their_owners_share_local_interests_but_any_contact_needs_species_appropriate_boundaries'),
            'shared' => $shared,
            'cautions' => $cautions,
            'score' => max(20, $score),
        ];
    }
}
