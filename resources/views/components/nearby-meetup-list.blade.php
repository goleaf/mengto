@props(['meetups'])

<x-sidebar-section title="{{ __('ui.nearby_meetups') }}" section="meetups" :href="route('meetups.index')">
    <x-sidebar-list>
        @forelse ($meetups as $meetup)
            <x-sidebar-list-item>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="break-words text-sm font-semibold text-paw-ink">
                            <x-optional-link
                                :route-name="$meetup['detail_route'] ?? null"
                                :route-parameters="$meetup['detail_parameters'] ?? []"
                            >
                                {{ $meetup['title'] }}
                            </x-optional-link>
                        </h3>
                        <x-icon-text icon="map-pin" class="mt-1">
                            {{ $meetup['place'] }}
                        </x-icon-text>
                    </div>

                    <time datetime="{{ $meetup['datetime'] }}" aria-label="{{ $meetup['date_accessible'] }}" class="shrink-0">
                        <x-status-badge :label="$meetup['time']" tone="mint" />
                    </time>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-icon-text icon="users">{{ $meetup['attendees'] }}</x-icon-text>
                    <x-action-control
                        label="{{ __('ui.join') }}"
                        active-label="{{ __('ui.going') }}"
                        icon="user-plus"
                        active-icon="check"
                        variant="quiet"
                        size="micro"
                        :active="$meetup['rsvp']"
                        :pressed="$meetup['rsvp']"
                        :endpoint="route('actions.perform')"
                        :payload="['action' => 'toggle-meetup', 'target' => $meetup['key'], 'label' => $meetup['title']]"
                    />
                </div>
            </x-sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">{{ __('ui.no_meetups_nearby') }}</p>
        @endforelse
    </x-sidebar-list>
</x-sidebar-section>
