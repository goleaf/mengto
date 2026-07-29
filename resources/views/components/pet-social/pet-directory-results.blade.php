@props(['pets'])

<x-pet-social.result-grid
    section="pet-directory"
    title-id="pet-directory-title"
    title="Pet directory results"
>
    @forelse ($pets as $pet)
        <x-pet-social.pet-directory-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-pet-social.empty-state
            icon="paw-print"
            title="No pets nearby"
            role="listitem"
            description="Try another neighborhood when discovery becomes available."
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-pet-social.result-grid>
