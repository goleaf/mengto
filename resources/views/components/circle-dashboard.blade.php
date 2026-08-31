@props([
    'summary',
    'filters',
    'activeFilter',
    'collections',
    'showStarter' => false,
])

<div {{ $attributes->class('circle-dashboard') }}>
    <x-summary-strip
        :items="$summary['stats']"
        label="{{ __('ui.your_circle_summary') }}"
        :icons="['bookmark', 'user-round-check', 'users-round', 'calendar-check']"
        :columns="4"
    />

    <x-collection-toolbar
        :action="route('circle.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="{{ __('ui.collection_view') }}"
        label="{{ __('ui.filter_your_circle') }}"
    />

    <div @class([
            'circle-collections',
            'circle-collections--single' => $activeFilter !== 'overview',
        ])>
            @forelse ($collections as $collection)
                <x-circle-collection
                    :collection="$collection"
                    :columns="$activeFilter === 'overview' ? 1 : 2"
                />
            @empty
                <x-empty-state
                    icon="inbox"
                    title="{{ __('ui.this_collection_is_quiet') }}"
                    description="{{ __('ui.choose_another_view_or_collect_something_useful_from_around_brand') }}"
                    :href="route('discover.index')"
                    action-label="{{ __('ui.open_discover') }}"
                    action-icon="search"
                />
            @endforelse
    </div>
</div>
