@props([
    'places',
    'selectedKey' => null,
    'layerLinks',
])

<section class="place-results" aria-labelledby="place-results-title">
    <header class="place-results__heading">
        <div>
            <p>{{ __('place_directory.results.eyebrow') }}</p>
            <h2 id="place-results-title">{{ trans_choice('presentation.matching_places', count($places['items']), ['count' => count($places['items'])]) }}</h2>
        </div>
        <nav class="place-results__layers" aria-label="{{ __('place_directory.results.map_layer') }}" data-place-layers>
            @forelse ($layerLinks as $link)
                <a href="{{ $link['url'] }}" @if ($link['current']) aria-current="page" @endif>
                    <x-ui-icon size="sm" :name="$link['icon']" />
                    <span>{{ $link['label'] }}</span>
                </a>
            @empty
                <span>{{ __('place_directory.results.no_layers') }}</span>
            @endforelse
        </nav>
    </header>

    <div class="place-results__list" role="list">
        @forelse ($places['items'] as $place)
            <x-place-card
                role="listitem"
                :place="$place"
                :selected="$selectedKey === $place['key']"
                :eager="$loop->first"
            />
        @empty
            <x-empty-state
                icon="map-pin-off"
                title="{{ __('place_directory.results.empty_title') }}"
                :description="$places['empty_message']"
            />
        @endforelse
    </div>

    @if ($places['pagination']['last_page'] > 1)
        <nav class="place-pagination" aria-label="{{ __('places.pagination.label') }}" data-place-pagination>
            <p>{{ $places['pagination']['summary'] }}</p>
            <div>
                @if ($places['pagination']['previous_url'])
                    <x-action-control
                        :href="$places['pagination']['previous_url']"
                        label="{{ __('places.pagination.previous') }}"
                        icon="chevron-left"
                        variant="surface"
                        size="compact"
                    />
                @endif

                @forelse ($places['pagination']['pages'] as $page)
                    <a
                        href="{{ $page['url'] }}"
                        class="place-pagination__page {{ $page['current'] ? 'is-current' : '' }}"
                        aria-label="{{ $page['label'] }}"
                        @if ($page['current']) aria-current="page" @endif
                    >
                        {{ $loop->iteration }}
                    </a>
                @empty
                    <span>{{ $places['pagination']['summary'] }}</span>
                @endforelse

                @if ($places['pagination']['next_url'])
                    <x-action-control
                        :href="$places['pagination']['next_url']"
                        label="{{ __('places.pagination.next') }}"
                        icon="chevron-right"
                        variant="surface"
                        size="compact"
                    />
                @endif
            </div>
        </nav>
    @endif
</section>
