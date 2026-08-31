<?php

declare(strict_types=1);

namespace App\Services;

final class WalkPlanPresenter
{
    private const FILTER_VALUES = ['upcoming', 'drafts', 'completed', 'cancelled'];

    /**
     * Walk plans do not yet have a canonical persistence model. Until they do,
     * the production surface presents an honest empty state.
     *
     * @return array<string, mixed>
     */
    public function present(string $filter): array
    {
        $activeFilter = in_array($filter, self::FILTER_VALUES, true) ? $filter : 'upcoming';

        return [
            'summary' => $this->summary(),
            'filters' => $this->filters(),
            'activeFilter' => $activeFilter,
            'plans' => [],
            'hasPlans' => false,
        ];
    }

    /** @return array<int, string> */
    public function conversationKeys(): array
    {
        return [];
    }

    /** @return array<int, array<string, mixed>> */
    public function messagePlans(): array
    {
        return [];
    }

    /** @return array<int, array<string, mixed>> */
    public function plansForConversation(string $conversation): array
    {
        return [];
    }

    /** @return array<int, array{value: string, label: string}> */
    private function filters(): array
    {
        return [
            ['value' => 'upcoming', 'label' => __('messages.upcoming')],
            ['value' => 'drafts', 'label' => __('messages.drafts')],
            ['value' => 'completed', 'label' => __('messages.completed')],
            ['value' => 'cancelled', 'label' => __('messages.cancelled')],
        ];
    }

    /** @return array<string, mixed> */
    private function summary(): array
    {
        return [
            'eyebrow' => __('messages.walk_planner'),
            'title' => __('messages.clear_plans_make_calmer_walks'),
            'description' => __('messages.keep_timing_meeting_points_pace_and_neighbor_context_together_from_the_first_draft_through_the_final_check_in'),
            'count' => trans_choice('presentation.plans_count', 0, ['count' => 0]),
            'stats' => [
                ['label' => __('messages.upcoming'), 'value' => '0', 'detail' => __('messages.draft_or_confirmed')],
                ['label' => __('messages.confirmed'), 'value' => '0', 'detail' => __('messages.ready_to_go')],
                ['label' => __('messages.completed'), 'value' => '0', 'detail' => __('messages.walks_finished')],
                ['label' => __('messages.neighbors'), 'value' => '0', 'detail' => __('messages.in_active_plans')],
            ],
        ];
    }
}
