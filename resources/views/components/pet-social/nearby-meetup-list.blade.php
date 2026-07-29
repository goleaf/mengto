@props(['meetups'])

<x-pet-social.sidebar-section title="Nearby meetups" section="meetups">
    <div role="list">
        @forelse ($meetups as $meetup)
            <article role="listitem" class="border-b border-paw-line py-4 first:pt-0 last:border-b-0 last:pb-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="break-words text-sm font-semibold text-paw-ink">{{ $meetup['title'] }}</h3>
                        <x-pet-social.icon-text icon="map-pin" class="mt-1">
                            {{ $meetup['place'] }}
                        </x-pet-social.icon-text>
                    </div>

                    <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}" class="shrink-0">
                        <x-pet-social.status-badge :label="$meetup['time']" tone="mint" />
                    </time>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-pet-social.icon-text icon="users">{{ $meetup['attendees'] }}</x-pet-social.icon-text>
                    <x-pet-social.static-action label="Join" icon="user-plus" variant="quiet" size="micro" />
                </div>
            </article>
        @empty
            <p role="listitem" class="text-sm text-paw-muted">No meetups nearby.</p>
        @endforelse
    </div>
</x-pet-social.sidebar-section>
