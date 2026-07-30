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
        label="{{ __('ui.walk_plan_summary_e10f26c0ae') }}"
        :icons="['calendar-days', 'calendar-check', 'circle-check', 'users-round']"
        :columns="4"
    />

    <x-collection-toolbar
        :action="route('walks.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="{{ __('ui.plan_status_a0630ab64b') }}"
        label="{{ __('ui.filter_walk_plans_a1f7c20be3') }}"
    />

    @if (! $hasPlans && in_array($activeFilter, ['upcoming', 'drafts'], true))
        <x-media-starter
            eyebrow="{{ __('ui.your_first_route_53478a49c5') }}"
            title="{{ __('ui.start_with_a_routine_both_pets_can_enjoy_34e96fdb53') }}"
            title-id="walk-starter-title"
            description="{{ __('ui.choose_a_familiar_companion_set_one_clear_meeting_b8705d4156') }}"
            :items="$starterItems"
        />
    @elseif ($plans !== [])
        <x-walk-plan-list :plans="$plans" />
    @else
        <x-empty-state
            icon="calendar-search"
            title="{{ __('ui.no_plans_in_this_view_4c36fe48f6') }}"
            description="{{ __('ui.choose_another_status_or_start_a_fresh_walk_abf7a5f801') }}"
            :href="route('compose', 'walk')"
            action-label="{{ __('ui.create_a_walk_plan_f885793261') }}"
            action-icon="calendar-plus"
        />
    @endif
</div>
