@props(['groups'])

<x-pet-social.result-grid
    section="group-directory"
    title-id="group-directory-title"
    title="Community group results"
    :columns="4"
>
    @forelse ($groups as $group)
        <x-pet-social.group-card :group="$group" :eager="$loop->first" />
    @empty
        <x-pet-social.empty-state
            icon="users"
            title="No groups nearby"
            role="listitem"
            description="Check back when neighbors start another local circle."
            class="sm:col-span-2 xl:col-span-4"
        />
    @endforelse
</x-pet-social.result-grid>
