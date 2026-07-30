@props(['groups', 'filters', 'unreadCount', 'activeFilter' => 'all-activity'])

<section data-section="activity-timeline" {{ $attributes->class(['panel', 'panel--clip']) }}>
    <h2 class="sr-only">Activity updates</h2>

    <form method="GET" action="{{ route('pet-social.notifications.index') }}" class="border-b border-paw-line p-4 sm:p-5">
        <x-ui.panel-heading title="Activity filters" :meta="$unreadCount.' new'" />

        <x-ui.filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="Activity filters"
            submit
            class="mt-4"
        />
    </form>

    @forelse ($groups as $group)
        <x-object.activity-group :group="$group" :index="$loop->index" />
    @empty
        <x-ui.empty-state
            icon="bell-off"
            title="All caught up"
            description="New PawCircle activity will appear here."
            class="m-4 sm:m-5"
        />
    @endforelse
</section>
