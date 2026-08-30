<?php

namespace App\Services;

final class ThreadCatalog
{
    /**
     * @param  array<string, mixed>  $post
     * @return array<int, array{author: string, pet: string, initials: string, tone: string, body: string, time: string, datetime: string, mine: bool}>
     */
    public function comments(array $post): array
    {
        return match ($post['pet']) {
            'Mochi' => [
                $this->comment(
                    __('messages.jamie_cho'),
                    __('messages.olive'),
                    'JC',
                    'mint',
                    __('messages.that_patient_patio_practice_really_shows_we_use_the_quiet_corner_near_the_planters_for_the_same_reason'),
                    __('messages.12_min_ago'),
                    '2026-07-29T09:48:00-07:00',
                ),
                $this->comment(
                    __('messages.mia_carter'),
                    __('messages.scout'),
                    'MC',
                    'sun',
                    __('messages.a_full_patio_loop_is_a_serious_win_scout_and_i_would_happily_join_for_a_calm_practice_walk_next_week'),
                    __('messages.6_min_ago'),
                    '2026-07-29T09:54:00-07:00',
                    true,
                ),
            ],
            'Juniper' => [
                $this->comment(
                    __('messages.mia_carter'),
                    __('messages.scout'),
                    'MC',
                    'sun',
                    __('messages.thank_you_for_noting_the_shade_is_the_west_entrance_the_gentler_start_for_older_dogs'),
                    __('messages.42_min_ago'),
                    '2026-07-29T09:18:00-07:00',
                    true,
                ),
                $this->comment(
                    __('messages.ari_jensen'),
                    __('messages.mochi'),
                    'AJ',
                    'paper',
                    __('messages.the_west_entrance_stays_quiet_before_five_and_has_a_water_fountain_near_the_first_bench'),
                    __('messages.31_min_ago'),
                    '2026-07-29T09:29:00-07:00',
                ),
            ],
            'Pip' => [
                $this->comment(
                    __('messages.priya_shah'),
                    __('messages.clover'),
                    'PS',
                    'mint',
                    __('messages.the_snack_clause_is_always_the_decisive_one_clover_accepted_her_carrier_after_the_same_negotiation'),
                    __('messages.2_hrs_ago'),
                    '2026-07-29T08:05:00-07:00',
                ),
                $this->comment(
                    __('messages.mia_carter'),
                    __('messages.scout'),
                    'MC',
                    'sun',
                    __('messages.that_first_comfortable_session_is_worth_celebrating_pip_looks_wonderfully_focused'),
                    __('messages.1_hr_ago'),
                    '2026-07-29T08:46:00-07:00',
                    true,
                ),
            ],
            'Scout' => [
                $this->comment(
                    __('messages.ari_jensen'),
                    __('messages.mochi'),
                    'AJ',
                    'paper',
                    __('messages.excellent_catch_scout_mochi_remains_committed_to_supervising_fetch_from_a_respectful_distance'),
                    __('messages.yesterday'),
                    '2026-07-28T18:10:00-07:00',
                ),
                $this->comment(
                    __('messages.noah_patel'),
                    __('messages.juniper'),
                    'NP',
                    'mint',
                    __('messages.that_focused_second_try_is_impressive_the_grass_there_looks_perfect_for_a_softer_landing'),
                    __('messages.yesterday'),
                    '2026-07-28T18:24:00-07:00',
                ),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    public function guide(): array
    {
        return [
            [
                'icon' => 'heart-handshake',
                'title' => __('messages.lead_with_care'),
                'description' => __('messages.share_context_that_helps_pets_and_people_feel_understood'),
            ],
            [
                'icon' => 'map-pin',
                'title' => __('messages.keep_it_local'),
                'description' => __('messages.add_useful_route_place_timing_or_accessibility_details'),
            ],
            [
                'icon' => 'shield-check',
                'title' => __('messages.protect_privacy'),
                'description' => __('messages.keep_personal_addresses_and_sensitive_care_details_in_direct_messages'),
            ],
        ];
    }

    /**
     * @return array{author: string, pet: string, initials: string, tone: string, body: string, time: string, datetime: string, mine: bool}
     */
    private function comment(
        string $author,
        string $pet,
        string $initials,
        string $tone,
        string $body,
        string $time,
        string $datetime,
        bool $mine = false,
    ): array {
        return compact('author', 'pet', 'initials', 'tone', 'body', 'time', 'datetime', 'mine');
    }
}
