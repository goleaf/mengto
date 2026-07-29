@props(['pets'])

<x-pet-social.collection-section
    section="owner-pets"
    eyebrow="At home with Mia"
    title="Scout & Nori"
    title-id="owner-pets-title"
    :columns="2"
>
    <x-slot:action>
        <x-pet-social.static-action label="Add pet" icon="plus" />
    </x-slot:action>

    @forelse ($pets as $pet)
        <x-pet-social.profile-pet-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-pet-social.empty-state
            icon="paw-print"
            title="No pets added yet"
            compact
            role="listitem"
            class="xl:col-span-2"
        />
    @endforelse
</x-pet-social.collection-section>
