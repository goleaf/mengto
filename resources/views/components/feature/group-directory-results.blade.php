@props(['groups'])

<x-layout.result-grid
    section="group-directory"
    title-id="group-directory-title"
    title="Community group results"
    :columns="4"
>
    @forelse ($groups as $group)
        <x-object.group-card :group="$group" :eager="$loop->first" />
    @empty
        <x-ui.empty-state
            icon="users"
            title="No groups match these filters"
            role="listitem"
            description="Try a broader topic, category, or organizer."
            :href="route('groups.index')"
            class="sm:col-span-2 xl:col-span-4"
        />
    @endforelse
</x-layout.result-grid>
