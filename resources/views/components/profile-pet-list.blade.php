@props([
    'pets',
    'eyebrow' => __('ui.at_home_fbccda060e'),
    'title' => __('ui.pets_7dc1cd7eaf'),
    'section' => 'profile-pets',
    'canManage' => false,
    'emptyTitle' => __('ui.no_pets_added_yet_5728267e77'),
])

<x-collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
    :title-id="$section.'-title'"
    :columns="2"
>
    @if ($canManage)
        <x-slot:action>
            <x-action-control :href="route('pets.manage.create')" label="{{ __('ui.add_pet_7065b90594') }}" icon="plus" />
        </x-slot:action>
    @endif

    @forelse ($pets as $pet)
        <x-profile-pet-card :pet="$pet" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="paw-print"
            :title="$emptyTitle"
            compact
            role="listitem"
            class="xl:col-span-2"
        />
    @endforelse
</x-collection-section>
