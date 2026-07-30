@props(['pets'])

<x-result-grid
    section="pet-directory"
    title-id="pet-directory-title"
    title="Pet directory results"
>
    @forelse ($pets as $pet)
        <x-pet-directory-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="paw-print"
            title="No pets match these filters"
            role="listitem"
            description="Try a broader name, breed, or species."
            :href="route('pets.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
