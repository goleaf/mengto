@props(['events'])

<div class="event-directory">
    <section class="panel panel--padded-sm event-toolbar" aria-label="Browse events">
        <form method="GET" action="{{ $events['browse_url'] }}" class="event-toolbar__form">
            <x-ui.search-field
                id="event-search"
                label="Search events"
                placeholder="Search walks, training, shows, or organizers"
                :value="$events['query']"
            />

            <div class="event-toolbar__filters" role="group" aria-label="Event types">
                @forelse ($events['filters'] as $filter)
                    <x-ui.filter-chip
                        :label="$filter['label']"
                        name="filter"
                        :value="$filter['value']"
                        type="submit"
                        :active="$events['filter'] === $filter['value']"
                        size="toolbar"
                    />
                @empty
                    <span class="event-directory__empty">No event filters are available.</span>
                @endforelse
            </div>

            <div class="event-toolbar__commands">
                <label for="event-sort" class="sr-only">Sort events</label>
                <span class="select-wrap">
                    <x-lucide-arrow-up-down class="icon icon--sm" aria-hidden="true" />
                    <select id="event-sort" name="sort" class="field field--select" onchange="this.form.submit()">
                        @foreach ($events['sort_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($events['sort'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </span>

                <input type="hidden" name="view" value="{{ $events['view'] }}">
                <x-ui.action-control type="submit" label="Search" icon="search" variant="primary" size="toolbar" />
            </div>
        </form>

        <nav class="event-toolbar__views" aria-label="Event view">
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
        <x-ui.content-panel section="event-calendar" eyebrow="Calendar" title="Upcoming dates">
            <div class="event-calendar section-body">
                @forelse ($events['calendar'] as $item)
                    <a href="{{ $item['href'] }}" class="event-calendar__item">
                        <span>{{ $item['date'] }}</span>
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['time'] }} · {{ $item['status'] }}</small>
                        <x-lucide-chevron-right class="icon icon--sm" aria-hidden="true" />
                    </a>
                @empty
                    <p class="event-directory__empty">No dates match these filters.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @elseif ($events['view'] === 'map')
        <x-ui.content-panel section="event-map" eyebrow="Generalized locations" title="Events on the map">
            <div class="event-map section-body">
                <div class="event-map__surface" role="img" aria-label="Generalized map of public event areas. Exact private addresses are not shown.">
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
                        <li class="event-directory__empty">No offline events match these filters.</li>
                    @endforelse
                </ol>
            </div>
        </x-ui.content-panel>
    @else
        <div class="event-grid">
            @forelse ($events['items'] as $event)
                <x-object.event-card :event="$event" />
            @empty
                <x-ui.empty-state
                    icon="calendar-search"
                    title="No events match these filters"
                    description="Try another date, format, or search phrase."
                />
            @endforelse
        </div>
    @endif
</div>
