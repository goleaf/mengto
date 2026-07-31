@props(['groups'])

<x-result-grid
    section="group-directory"
    title-id="group-directory-title"
    title="{{ __('ui.community_group_results_88b232865f') }}"
>
    @forelse ($groups as $group)
        <x-group-card :group="$group" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="users"
            title="{{ __('ui.no_groups_match_these_filters_7051ff11e4') }}"
            role="listitem"
            description="{{ __('ui.try_a_broader_topic_category_or_organizer_25f4a422e4') }}"
            :href="route('groups.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
