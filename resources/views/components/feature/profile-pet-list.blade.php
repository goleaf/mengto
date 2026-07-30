@props([
    'pets',
    'eyebrow' => 'At home',
    'title' => 'Pets',
    'section' => 'profile-pets',
    'canManage' => false,
    'emptyTitle' => 'No pets added yet',
])

<x-layout.collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
    :title-id="$section.'-title'"
    :columns="2"
>
    @if ($canManage)
        <x-slot:action>
            <x-ui.action-control :href="route('pet-social.compose', 'pet')" label="Add pet" icon="plus" />
        </x-slot:action>
    @endif

    @forelse ($pets as $pet)
        <x-object.profile-pet-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-ui.empty-state
            icon="paw-print"
            :title="$emptyTitle"
            compact
            role="listitem"
            class="xl:col-span-2"
        />
    @endforelse
</x-layout.collection-section>
