@props(['neighbors'])

<x-result-grid
    section="neighbor-directory"
    title-id="neighbor-directory-title"
    title="{{ __('ui.people_nearby_690d8bdc6b') }}"
>
    @forelse ($neighbors as $neighbor)
        <x-neighbor-card :neighbor="$neighbor" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="users-round"
            title="{{ __('ui.no_neighbors_match_these_filters_e2a3976714') }}"
            role="listitem"
            description="{{ __('ui.try_a_broader_person_pet_or_neighborhood_4f267326cd') }}"
            :href="route('neighbors.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
