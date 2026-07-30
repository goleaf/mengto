@props([
    'summary',
    'filters',
    'activeFilter',
    'plans',
    'hasPlans' => false,
    'starterItems' => [],
])

<div {{ $attributes->class('walk-dashboard') }}>
    <x-ui.summary-strip
        :items="$summary['stats']"
        label="Walk plan summary"
        :icons="['calendar-days', 'calendar-check', 'circle-check', 'users-round']"
        :columns="4"
    />

    <x-feature.collection-toolbar
        :action="route('walks.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="Plan status"
        label="Filter walk plans"
    />

    @if (! $hasPlans && in_array($activeFilter, ['upcoming', 'drafts'], true))
        <x-object.media-starter
            eyebrow="Your first route"
            title="Start with a routine both pets can enjoy"
            title-id="walk-starter-title"
            description="Choose a familiar companion, set one clear meeting point, and keep the first plan comfortably short."
            :items="$starterItems"
        />
    @elseif ($plans !== [])
        <x-feature.walk-plan-list :plans="$plans" />
    @else
        <x-ui.empty-state
            icon="calendar-search"
            title="No plans in this view"
            description="Choose another status or start a fresh walk plan with a familiar neighbor."
            :href="route('compose', 'walk')"
            action-label="Create a walk plan"
            action-icon="calendar-plus"
        />
    @endif
</div>
