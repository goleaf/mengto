@props(['groups'])

<x-result-grid
    section="group-directory"
    title-id="group-directory-title"
    title="{{ __('groups.directory.card.results_title') }}"
    data-group-results-title="{{ __('groups.directory.card.results_title') }}"
>
    @forelse ($groups as $group)
        <x-group-card :group="$group" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="users"
            title="{{ __('groups.directory.card.empty_title') }}"
            role="listitem"
            description="{{ __('groups.directory.card.empty_description') }}"
            :href="route('groups.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
