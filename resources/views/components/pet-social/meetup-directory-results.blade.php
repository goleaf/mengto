@props(['meetups'])

<x-pet-social.result-grid
    section="meetup-directory"
    title-id="meetup-directory-title"
    title="Upcoming meetup results"
>
    @forelse ($meetups as $meetup)
        <x-pet-social.meetup-card :meetup="$meetup" :eager="$loop->first" />
    @empty
        <x-pet-social.empty-state
            icon="calendar-x"
            title="No meetups nearby"
            role="listitem"
            description="Check back when neighbors add another gathering."
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-pet-social.result-grid>
