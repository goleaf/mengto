@props(['meetups'])

<x-layout.sidebar-section title="Nearby meetups" section="meetups" :href="route('pet-social.meetups.index')">
    <x-object.sidebar-list>
        @forelse ($meetups as $meetup)
            <x-object.sidebar-list-item>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="break-words text-sm font-semibold text-paw-ink">
                            <x-ui.optional-link
                                :route-name="$meetup['detail_route'] ?? null"
                                :route-parameters="$meetup['detail_parameters'] ?? []"
                            >
                                {{ $meetup['title'] }}
                            </x-ui.optional-link>
                        </h3>
                        <x-ui.icon-text icon="map-pin" class="mt-1">
                            {{ $meetup['place'] }}
                        </x-ui.icon-text>
                    </div>

                    <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}" class="shrink-0">
                        <x-ui.status-badge :label="$meetup['time']" tone="mint" />
                    </time>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-ui.icon-text icon="users">{{ $meetup['attendees'] }}</x-ui.icon-text>
                    <x-ui.action-control
                        label="Join"
                        active-label="Going"
                        icon="user-plus"
                        active-icon="check"
                        variant="quiet"
                        size="micro"
                        :active="$meetup['rsvp']"
                        :pressed="$meetup['rsvp']"
                        :endpoint="route('pet-social.actions.perform')"
                        :payload="['action' => 'toggle-meetup', 'target' => $meetup['key'], 'label' => $meetup['title']]"
                    />
                </div>
            </x-object.sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">No meetups nearby.</p>
        @endforelse
    </x-object.sidebar-list>
</x-layout.sidebar-section>
