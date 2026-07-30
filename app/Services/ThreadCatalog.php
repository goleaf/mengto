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
                    __('messages.jamie_cho_5f313c129b'),
                    __('messages.olive_3038ab334a'),
                    'JC',
                    'mint',
                    __('messages.that_patient_patio_practice_really_shows_we_use_the_quie_e35aed751d'),
                    __('messages.12_min_ago_5a2196199e'),
                    '2026-07-29T09:48:00-07:00',
                ),
                $this->comment(
                    __('messages.mia_carter_0e5b29cc3b'),
                    __('messages.scout_8a1db462be'),
                    'MC',
                    'sun',
                    __('messages.a_full_patio_loop_is_a_serious_win_scout_and_i_would_hap_21d062fb10'),
                    __('messages.6_min_ago_c6f7b962dd'),
                    '2026-07-29T09:54:00-07:00',
                    true,
                ),
            ],
            'Juniper' => [
                $this->comment(
                    __('messages.mia_carter_0e5b29cc3b'),
                    __('messages.scout_8a1db462be'),
                    'MC',
                    'sun',
                    __('messages.thank_you_for_noting_the_shade_is_the_west_entrance_the__057b6ba605'),
                    __('messages.42_min_ago_4a2ff6804a'),
                    '2026-07-29T09:18:00-07:00',
                    true,
                ),
                $this->comment(
                    __('messages.ari_jensen_6c670df410'),
                    __('messages.mochi_95114c81f3'),
                    'AJ',
                    'paper',
                    __('messages.the_west_entrance_stays_quiet_before_five_and_has_a_wate_5491fd69f0'),
                    __('messages.31_min_ago_9cd894a647'),
                    '2026-07-29T09:29:00-07:00',
                ),
            ],
            'Pip' => [
                $this->comment(
                    __('messages.priya_shah_8925523814'),
                    __('messages.clover_a740edd9c1'),
                    'PS',
                    'mint',
                    __('messages.the_snack_clause_is_always_the_decisive_one_clover_accep_a41302e0f6'),
                    __('messages.2_hrs_ago_d7ec83bc13'),
                    '2026-07-29T08:05:00-07:00',
                ),
                $this->comment(
                    __('messages.mia_carter_0e5b29cc3b'),
                    __('messages.scout_8a1db462be'),
                    'MC',
                    'sun',
                    __('messages.that_first_comfortable_session_is_worth_celebrating_pip__728f8e3ec3'),
                    __('messages.1_hr_ago_f98c800e71'),
                    '2026-07-29T08:46:00-07:00',
                    true,
                ),
            ],
            'Scout' => [
                $this->comment(
                    __('messages.ari_jensen_6c670df410'),
                    __('messages.mochi_95114c81f3'),
                    'AJ',
                    'paper',
                    __('messages.excellent_catch_scout_mochi_remains_committed_to_supervi_7cd0ea3ae2'),
                    __('messages.yesterday_566181254b'),
                    '2026-07-28T18:10:00-07:00',
                ),
                $this->comment(
                    __('messages.noah_patel_147a9793ed'),
                    __('messages.juniper_fe6a448ec9'),
                    'NP',
                    'mint',
                    __('messages.that_focused_second_try_is_impressive_the_grass_there_lo_bf9e678e73'),
                    __('messages.yesterday_566181254b'),
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
                'title' => __('messages.lead_with_care_bdc92ec63d'),
                'description' => __('messages.share_context_that_helps_pets_and_people_feel_understood_0c1c743215'),
            ],
            [
                'icon' => 'map-pin',
                'title' => __('messages.keep_it_local_c9de8bcb87'),
                'description' => __('messages.add_useful_route_place_timing_or_accessibility_details_b31b7c7a2a'),
            ],
            [
                'icon' => 'shield-check',
                'title' => __('messages.protect_privacy_58cea86e53'),
                'description' => __('messages.keep_personal_addresses_and_sensitive_care_details_in_di_74e142f6f4'),
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
