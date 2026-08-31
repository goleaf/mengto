@props([
    'summary',
    'filters',
    'activeFilter',
    'plans',
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

    <x-empty-state
        icon="calendar-search"
        title="{{ __('ui.no_plans_in_this_view') }}"
        description="{{ __('ui.choose_another_status_or_start_a_fresh_walk_plan_with_a_familiar_neighbor') }}"
    />
</div>
