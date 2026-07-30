@props(['groups', 'filters', 'unreadCount', 'activeFilter' => 'all-activity'])

<section data-section="activity-timeline" {{ $attributes->class(['panel', 'panel--clip']) }}>
    <h2 class="sr-only">{{ __('ui.activity_updates_082149a030') }}</h2>

    <form method="GET" action="{{ route('notifications.index') }}" class="border-b border-paw-line p-4 sm:p-5">
        <x-panel-heading title="{{ __('ui.activity_filters_b58a53ca6c') }}" :meta="__('presentation.new_count', ['count' => $unreadCount])" />

        <x-filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="{{ __('ui.activity_filters_b58a53ca6c') }}"
            submit
            class="mt-4"
        />
    </form>

    @forelse ($groups as $group)
        <x-activity-group :group="$group" :index="$loop->index" />
    @empty
        <x-empty-state
            icon="bell-off"
            title="{{ __('ui.all_caught_up_7773db01ae') }}"
            description="{{ __('ui.new_brand_activity_will_appear_here_89284d9a48') }}"
            class="m-4 sm:m-5"
        />
    @endforelse
</section>
