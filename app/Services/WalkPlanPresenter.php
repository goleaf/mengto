<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;

final class WalkPlanPresenter
{
    private const FILTER_VALUES = ['upcoming', 'drafts', 'completed', 'cancelled'];

    public function __construct(
        private readonly PrototypeState $state,
        private readonly LocaleFormatter $formatter,
    ) {}

    /**
     * @param  array<string, mixed>  $owner
     * @return array<string, mixed>
     */
    public function present(string $filter, array $owner): array
    {
        $activeFilter = $this->activeFilter($filter);
        $plans = $this->plans();
        $visiblePlans = array_values(array_filter(
            $plans,
            fn (array $plan): bool => $this->matchesFilter($plan, $activeFilter),
        ));

        return [
            'owner' => $owner,
            'summary' => $this->summary($plans),
            'filters' => $this->filters(),
            'activeFilter' => $activeFilter,
            'plans' => $visiblePlans,
            'hasPlans' => $plans !== [],
            'starterItems' => $this->starterItems(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function conversationKeys(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (array $plan): string => in_array($plan['status'], ['draft', 'confirmed'], true)
                ? $plan['conversation']
                : '',
            $this->plans(),
        ))));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messagePlans(): array
    {
        return array_slice(array_values(array_filter(
            $this->plans(),
            static fn (array $plan): bool => $plan['conversation'] !== ''
                && in_array($plan['status'], ['draft', 'confirmed'], true),
        )), 0, 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function plansForConversation(string $conversation): array
    {
        return array_values(array_filter(
            $this->plans(),
            static fn (array $plan): bool => $plan['conversation'] === $conversation
                && in_array($plan['status'], ['draft', 'confirmed'], true),
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function plans(): array
    {
        $plans = array_map(
            fn (array $plan): array => $this->decorate($plan),
            $this->state->walkPlans(),
        );

        usort($plans, static fn (array $left, array $right): int => strcmp($left['datetime'], $right['datetime']));

        return $plans;
    }

    /**
     * @param  array<string, string>  $plan
     * @return array<string, mixed>
     */
    private function decorate(array $plan): array
    {
        $participant = $this->participant($plan['target'] ?? 'scout');
        $dateTime = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            ($plan['date'] ?? today()->format('Y-m-d')).' '.($plan['time'] ?? '08:30'),
        );
        $status = $plan['status'] ?? 'draft';
        $statusMeta = $this->statusMeta($status);

        return [
            ...$plan,
            'label' => $participant['pet'],
            'conversation' => $participant['conversation'],
            'participant' => $participant,
            'status' => $status,
            'status_label' => $statusMeta['label'],
            'status_icon' => $statusMeta['icon'],
            'status_tone' => $statusMeta['tone'],
            'date_label' => $this->formatter->weekdayMonthDay($dateTime),
            'time_label' => $this->formatter->time($dateTime),
            'datetime' => $dateTime->toAtomString(),
            'next_action' => $this->nextAction($status),
            'steps' => [
                [
                    'icon' => 'map-pin',
                    'label' => __('messages.meet_74567d3512'),
                    'title' => $plan['location'] ?? __('messages.choose_a_familiar_meeting_point_0b782ca18e'),
                ],
                [
                    'icon' => 'footprints',
                    'label' => __('messages.walk_08ee52ae12'),
                    'title' => $plan['detail'] ?: __('messages.easy_pace_30_min_c2585b7d4e'),
                ],
                [
                    'icon' => 'message-circle',
                    'label' => __('messages.settle_a0c79a8531'),
                    'title' => __('messages.share_a_quick_check_in_before_heading_home_5b797466a0'),
                ],
            ],
        ];
    }

    private function activeFilter(string $filter): string
    {
        return in_array($filter, self::FILTER_VALUES, true) ? $filter : 'upcoming';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function filters(): array
    {
        return [
            ['value' => 'upcoming', 'label' => __('messages.upcoming_5f1a2542e4')],
            ['value' => 'drafts', 'label' => __('messages.drafts_f592e6a4db')],
            ['value' => 'completed', 'label' => __('messages.completed_22a970d2e5')],
            ['value' => 'cancelled', 'label' => __('messages.cancelled_d353a99eb4')],
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function matchesFilter(array $plan, string $filter): bool
    {
        return match ($filter) {
            'drafts' => $plan['status'] === 'draft',
            'completed' => $plan['status'] === 'completed',
            'cancelled' => $plan['status'] === 'cancelled',
            default => in_array($plan['status'], ['draft', 'confirmed'], true),
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $plans
     * @return array<string, mixed>
     */
    private function summary(array $plans): array
    {
        $countByStatus = array_count_values(array_column($plans, 'status'));
        $activePlans = array_filter(
            $plans,
            static fn (array $plan): bool => in_array($plan['status'], ['draft', 'confirmed'], true),
        );
        $neighbors = array_unique(array_filter(array_column($activePlans, 'conversation')));

        return [
            'eyebrow' => __('messages.walk_planner_46c2829124'),
            'title' => __('messages.clear_plans_make_calmer_walks_c2629160b5'),
            'description' => __('messages.keep_timing_meeting_points_pace_and_neighbor_context_tog_835975886d'),
            'count' => trans_choice('presentation.plans_count', count($plans), ['count' => count($plans)]),
            'stats' => [
                ['label' => __('messages.upcoming_5f1a2542e4'), 'value' => (string) count($activePlans), 'detail' => __('messages.draft_or_confirmed_7115043976')],
                ['label' => __('messages.confirmed_fe00b67b6d'), 'value' => (string) ($countByStatus['confirmed'] ?? 0), 'detail' => __('messages.ready_to_go_feb320ee9c')],
                ['label' => __('messages.completed_22a970d2e5'), 'value' => (string) ($countByStatus['completed'] ?? 0), 'detail' => __('messages.walks_finished_920a60a508')],
                ['label' => __('messages.neighbors_ecc05289ef'), 'value' => (string) count($neighbors), 'detail' => __('messages.in_active_plans_95c99c2c87')],
            ],
        ];
    }

    /**
     * @return array{label: string, icon: string, tone: string}
     */
    private function statusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => __('messages.confirmed_fe00b67b6d'), 'icon' => 'calendar-check', 'tone' => 'mint'],
            'completed' => ['label' => __('messages.completed_22a970d2e5'), 'icon' => 'circle-check', 'tone' => 'ink'],
            'cancelled' => ['label' => __('messages.cancelled_d353a99eb4'), 'icon' => 'circle-x', 'tone' => 'surface'],
            default => ['label' => __('messages.draft_ebf12ef47c'), 'icon' => 'pencil-line', 'tone' => 'sun'],
        };
    }

    /**
     * @return array{label: string, icon: string}|null
     */
    private function nextAction(string $status): ?array
    {
        return match ($status) {
            'draft' => ['label' => __('messages.confirm_plan_d80926e20d'), 'icon' => 'calendar-check'],
            'confirmed' => ['label' => __('messages.mark_complete_6470f48e43'), 'icon' => 'circle-check'],
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    private function participant(string $target): array
    {
        return match ($target) {
            'mochi' => [
                'pet' => __('messages.mochi_95114c81f3'),
                'person' => __('messages.ari_jensen_6c670df410'),
                'conversation' => 'ari',
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.ari_relaxing_with_mochi_in_a_neighborhood_park_2e4ba2f4ec'),
            ],
            'juniper' => [
                'pet' => __('messages.juniper_fe6a448ec9'),
                'person' => __('messages.noah_patel_147a9793ed'),
                'conversation' => 'noah',
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.noah_practicing_with_a_small_dog_in_a_wooded_park_a01c6fa46c'),
            ],
            default => [
                'pet' => __('messages.scout_8a1db462be'),
                'person' => __('messages.mia_carter_0e5b29cc3b'),
                'conversation' => '',
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass_4abc84adab'),
            ],
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function starterItems(): array
    {
        return [
            [
                'title' => __('messages.start_with_a_familiar_companion_644a17d04c'),
                'meta' => __('messages.scout_and_mia_a4d8a1fd0f'),
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.scout_a_black_and_white_border_collie_resting_on_grass_4abc84adab'),
                'href' => route('compose', 'walk'),
                'icon' => 'calendar-plus',
            ],
            [
                'title' => __('messages.plan_a_quiet_city_loop_86a2350f59'),
                'meta' => __('messages.ari_and_mochi_6ab978b432'),
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.ari_relaxing_with_mochi_in_a_neighborhood_park_2e4ba2f4ec'),
                'href' => route('neighbors.ari'),
                'icon' => 'map',
            ],
            [
                'title' => __('messages.choose_a_shaded_route_8cd4ac8c8a'),
                'meta' => __('messages.noah_and_juniper_875732f92f'),
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => __('messages.noah_practicing_with_a_small_dog_in_a_wooded_park_a01c6fa46c'),
                'href' => route('neighbors.index', ['q' => __('messages.noah_678202b3c0')]),
                'icon' => 'trees',
            ],
        ];
    }
}
