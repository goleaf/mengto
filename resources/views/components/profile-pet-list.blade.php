@props([
    'pets',
    'eyebrow' => __('ui.at_home'),
    'title' => __('ui.pets'),
    'section' => 'profile-pets',
    'canManage' => false,
    'emptyTitle' => __('ui.no_pets_added_yet'),
    'icon' => null,
    'addAction' => null,
])

<x-collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
    :title-id="$section.'-title'"
    :columns="2"
    :icon="$icon"
    {{ $attributes }}
>
    @if ($canManage)
        <x-slot:action>
            <x-action-control
                :href="$addAction['href'] ?? route('pets.manage.create')"
                :label="$addAction['label'] ?? __('ui.add_pet')"
                :icon="$addAction['icon'] ?? 'plus'"
            />
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
