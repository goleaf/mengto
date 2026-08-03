@props(['neighbors'])

<x-result-grid
    section="neighbor-directory"
    title-id="neighbor-directory-title"
    title="{{ __('neighbors.results.title') }}"
    data-neighbor-results
>
    @forelse ($neighbors as $neighbor)
        <x-neighbor-card :neighbor="$neighbor" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="users-round"
            title="{{ __('neighbors.results.empty_title') }}"
            role="listitem"
            description="{{ __('neighbors.results.empty_description') }}"
            :href="route('neighbors.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
