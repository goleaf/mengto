@props(['pets'])

<x-result-grid
    section="pet-directory"
    title-id="pet-directory-title"
    title="{{ __('ui.pet_directory_results_7cc90b0799') }}"
>
    @forelse ($pets as $pet)
        <x-pet-directory-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="paw-print"
            title="{{ __('ui.no_pets_match_these_filters_84263ef1c0') }}"
            role="listitem"
            description="{{ __('ui.try_a_broader_name_breed_or_species_9c0423538e') }}"
            :href="route('pets.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
