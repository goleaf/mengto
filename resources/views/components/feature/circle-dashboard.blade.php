@props([
    'summary',
    'filters',
    'activeFilter',
    'collections',
    'showStarter' => false,
    'starterItems' => [],
])

<div {{ $attributes->class('circle-dashboard') }}>
    <x-ui.summary-strip
        :items="$summary['stats']"
        label="Your circle summary"
        :icons="['bookmark', 'user-round-check', 'users-round', 'calendar-check']"
        :columns="4"
    />

    <x-feature.collection-toolbar
        :action="route('circle.index')"
        :filters="$filters"
        :active="$activeFilter"
        :count="$summary['count']"
        title="Collection view"
        label="Filter your circle"
    />

    @if ($showStarter)
        <x-object.media-starter
            eyebrow="Start with one useful thing"
            title="Build a circle around real routines"
            title-id="circle-starter-title"
            description="Save a useful post, follow a familiar neighbor, or RSVP to a comfortable meetup. Each choice will return here."
            :items="$starterItems"
        />
    @else
        <div @class([
            'circle-collections',
            'circle-collections--single' => $activeFilter !== 'overview',
        ])>
            @forelse ($collections as $collection)
                <x-feature.circle-collection
                    :collection="$collection"
                    :columns="$activeFilter === 'overview' ? 1 : 2"
                />
            @empty
                <x-ui.empty-state
                    icon="inbox"
                    title="This collection is quiet"
                    description="Choose another view or collect something useful from around PawCircle."
                    :href="route('discover.index')"
                    action-label="Open Discover"
                    action-icon="search"
                />
            @endforelse
        </div>
    @endif
</div>
