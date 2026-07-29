@props(['neighbors'])

<x-pet-social.result-grid
    section="neighbor-directory"
    title-id="neighbor-directory-title"
    title="People nearby"
    :columns="4"
>
    @forelse ($neighbors as $neighbor)
        <x-pet-social.neighbor-card :neighbor="$neighbor" :eager="$loop->first" />
    @empty
        <x-pet-social.empty-state
            icon="users-round"
            title="No neighbors nearby"
            role="listitem"
            description="Check back as more local pet people join PawCircle."
            class="sm:col-span-2 xl:col-span-4"
        />
    @endforelse
</x-pet-social.result-grid>
