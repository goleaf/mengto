@props(['meetups'])

<x-layout.result-grid
    section="meetup-directory"
    title-id="meetup-directory-title"
    title="Upcoming meetup results"
>
    @forelse ($meetups as $meetup)
        <x-object.meetup-card :meetup="$meetup" :eager="$loop->first" />
    @empty
        <x-ui.empty-state
            icon="calendar-x"
            title="No meetups match these filters"
            role="listitem"
            description="Try a broader plan, place, or meetup type."
            :href="route('meetups.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-layout.result-grid>
