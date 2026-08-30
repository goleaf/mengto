@props([
    'summary',
    'filters',
    'activeFilter',
    'plans',
    'hasPlans' => false,
    'starterItems' => [],
])

<div {{ $attributes->class('walk-dashboard') }}>
    <x-summary-strip
        :items="$summary['stats']"
        label="{{ __('ui.walk_plan_summary') }}"
        :icons="['calendar-days', 'calendar-check', 'circle-check', 'users-round']"
        :columns="4"
    />

    <x-collection-toolbar
        :action="route('walks.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="{{ __('ui.plan_status') }}"
        label="{{ __('ui.filter_walk_plans') }}"
    />

    @if (! $hasPlans && in_array($activeFilter, ['upcoming', 'drafts'], true))
        <x-media-starter
            eyebrow="{{ __('ui.your_first_route') }}"
            title="{{ __('ui.start_with_a_routine_both_pets_can_enjoy') }}"
            title-id="walk-starter-title"
            description="{{ __('ui.choose_a_familiar_companion_set_one_clear_meeting_point_and_keep_the_first_plan_comfortably_short') }}"
            :items="$starterItems"
        />
    @elseif ($plans !== [])
        <x-walk-plan-list :plans="$plans" />
    @else
        <x-empty-state
            icon="calendar-search"
            title="{{ __('ui.no_plans_in_this_view') }}"
            description="{{ __('ui.choose_another_status_or_start_a_fresh_walk_plan_with_a_familiar_neighbor') }}"
            :href="route('compose', 'walk')"
            action-label="{{ __('ui.create_a_walk_plan') }}"
            action-icon="calendar-plus"
        />
    @endif
</div>
