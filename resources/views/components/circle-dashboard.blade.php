@props([
    'summary',
    'filters',
    'activeFilter',
    'collections',
    'showStarter' => false,
    'starterItems' => [],
])

<div {{ $attributes->class('circle-dashboard') }}>
    <x-summary-strip
        :items="$summary['stats']"
        label="{{ __('ui.your_circle_summary_39d25b4e7f') }}"
        :icons="['bookmark', 'user-round-check', 'users-round', 'calendar-check']"
        :columns="4"
    />

    <x-collection-toolbar
        :action="route('circle.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="{{ __('ui.collection_view_ddc0d8cbe8') }}"
        label="{{ __('ui.filter_your_circle_dbaff85629') }}"
    />

    @if ($showStarter)
        <x-media-starter
            eyebrow="{{ __('ui.start_with_one_useful_thing_8a85f9bcd9') }}"
            title="{{ __('ui.build_a_circle_around_real_routines_3ec77af975') }}"
            title-id="circle-starter-title"
            description="{{ __('ui.save_a_useful_post_follow_a_familiar_neighbor_cac470a75c') }}"
            :items="$starterItems"
        />
    @else
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
                    title="{{ __('ui.this_collection_is_quiet_3653cb0ecb') }}"
                    description="{{ __('ui.choose_another_view_or_collect_something_useful_from_8086b1797a') }}"
                    :href="route('discover.index')"
                    action-label="{{ __('ui.open_discover_43454afd16') }}"
                    action-icon="search"
                />
            @endforelse
        </div>
    @endif
</div>
