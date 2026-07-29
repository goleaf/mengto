@props(['groups', 'filters', 'unreadCount'])

<section data-section="activity-timeline" {{ $attributes->class(['pc-panel', 'pc-panel--clip']) }}>
    <h2 class="sr-only">Activity updates</h2>

    <div class="border-b border-paw-line p-4 sm:p-5">
        <x-pet-social.panel-heading title="Activity filters" :meta="$unreadCount.' new'" />

        <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Activity filters">
            @forelse ($filters as $filter)
                <x-pet-social.filter-chip :label="$filter" :active="$loop->first" />
            @empty
                <span class="text-sm text-paw-muted">Filters unavailable.</span>
            @endforelse
        </div>
    </div>

    @forelse ($groups as $group)
        <x-pet-social.activity-group :group="$group" :index="$loop->index" />
    @empty
        <x-pet-social.empty-state
            icon="bell-off"
            title="All caught up"
            description="New PawCircle activity will appear here."
            class="m-4 sm:m-5"
        />
    @endforelse
</section>
