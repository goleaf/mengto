@props(['neighbors'])

<x-layout.result-grid
    section="neighbor-directory"
    title-id="neighbor-directory-title"
    title="People nearby"
    :columns="4"
>
    @forelse ($neighbors as $neighbor)
        <x-object.neighbor-card :neighbor="$neighbor" :eager="$loop->first" />
    @empty
        <x-ui.empty-state
            icon="users-round"
            title="No neighbors match these filters"
            role="listitem"
            description="Try a broader person, pet, or neighborhood."
            :href="route('neighbors.index')"
            class="sm:col-span-2 xl:col-span-4"
        />
    @endforelse
</x-layout.result-grid>
