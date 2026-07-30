@props(['events'])

<div class="event-directory">
    <section class="panel panel--padded-sm event-toolbar" aria-label="{{ __('ui.browse_events_8d71009f10') }}">
        <form method="GET" action="{{ $events['browse_url'] }}" class="event-toolbar__form">
            <x-search-field
                id="event-search"
                label="{{ __('ui.search_events_901abe9521') }}"
                placeholder="{{ __('ui.search_walks_training_shows_or_organizers_76a7d6df5d') }}"
                :value="$events['query']"
            />

            <div class="event-toolbar__filters" role="group" aria-label="{{ __('ui.event_types_8f338cc0b2') }}">
                @forelse ($events['filters'] as $filter)
                    <x-filter-chip
                        :label="$filter['label']"
                        name="filter"
                        :value="$filter['value']"
                        type="submit"
                        :active="$events['filter'] === $filter['value']"
                        size="toolbar"
                    />
                @empty
                    <span class="event-directory__empty">{{ __('ui.no_event_filters_are_available_077a38d634') }}</span>
                @endforelse
            </div>

            <div class="event-toolbar__commands">
                <label for="event-sort" class="sr-only">{{ __('ui.sort_events_b5c2233507') }}</label>
                <span class="select-wrap">
                    <x-lucide-arrow-up-down class="icon icon--sm" aria-hidden="true" />
                    <select id="event-sort" name="sort" class="field field--select" onchange="this.form.submit()">
                        @foreach ($events['sort_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($events['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </span>

                <input type="hidden" name="view" value="{{ $events['view'] }}">
                <x-action-control type="submit" label="{{ __('ui.search_49c266baaa') }}" icon="search" variant="primary" size="toolbar" />
            </div>
        </form>

        <nav class="event-toolbar__views" aria-label="{{ __('ui.event_view_4083c18dd7') }}">
            @foreach ($events['view_options'] as $value => $label)
                <a
                    href="{{ $events['browse_url'].'?'.http_build_query(array_filter([
                        'q' => $events['query'],
                        'filter' => $events['filter'],
                        'sort' => $events['sort'],
                        'view' => $value,
                    ])) }}"
                    @if ($events['view'] === $value) aria-current="page" @endif
                    class="event-toolbar__view"
                >
                    <x-dynamic-component
                        :component="'lucide-'.match ($value) {
                            'calendar' => 'calendar-days',
                            'map' => 'map',
                            default => 'list',
                        }"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </section>

    @if ($events['view'] === 'calendar')
        <x-content-panel section="event-calendar" eyebrow="{{ __('ui.calendar_d5d0a30b51') }}" title="{{ __('ui.upcoming_dates_552efd51a1') }}">
            <div class="event-calendar section-body">
                @forelse ($events['calendar'] as $item)
                    <a href="{{ $item['href'] }}" class="event-calendar__item">
                        <span>{{ $item['date'] }}</span>
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['time'] }} · {{ $item['status'] }}</small>
                        <x-lucide-chevron-right class="icon icon--sm" aria-hidden="true" />
                    </a>
                @empty
                    <p class="event-directory__empty">{{ __('ui.no_dates_match_these_filters_24bb2f49d4') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($events['view'] === 'map')
        <x-content-panel section="event-map" eyebrow="{{ __('ui.generalized_locations_f8a7558000') }}" title="{{ __('ui.events_on_the_map_0c184311a8') }}">
            <div class="event-map section-body">
                <div class="event-map__surface" role="img" aria-label="{{ __('ui.generalized_map_of_public_event_areas_exact_private_52d33fe545') }}">
                    <span class="event-map__road event-map__road--one"></span>
                    <span class="event-map__road event-map__road--two"></span>
                    @foreach ($events['map'] as $item)
                        <span
                            class="event-map__marker event-map__marker--{{ ($loop->index % 5) + 1 }}"
                            aria-hidden="true"
                        >{{ $loop->iteration }}</span>
                    @endforeach
                </div>
                <ol class="event-map__list">
                    @forelse ($events['map'] as $item)
                        <li>
                            <span>{{ $loop->iteration }}</span>
                            <a href="{{ $item['href'] }}">
                                <strong>{{ $item['title'] }}</strong>
                                <small>{{ $item['place'] }} · {{ $item['distance'] }}</small>
                            </a>
                        </li>
                    @empty
                        <li class="event-directory__empty">{{ __('ui.no_offline_events_match_these_filters_018c48a097') }}</li>
                    @endforelse
                </ol>
            </div>
        </x-content-panel>
    @else
        <div class="event-grid">
            @forelse ($events['items'] as $event)
                <x-event-card :event="$event" />
            @empty
                <x-empty-state
                    icon="calendar-search"
                    title="{{ __('ui.no_events_match_these_filters_046aea06df') }}"
                    description="{{ __('ui.try_another_date_format_or_search_phrase_b1e8a95379') }}"
                />
            @endforelse
        </div>
    @endif
</div>
