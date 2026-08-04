@props([
    'places',
    'selected' => null,
    'layer' => 'places',
    'layerLabel' => __('presentation.default_place_layer'),
    'emergency' => false,
])

<section
    {{ $attributes->class(['place-map', 'place-map--emergency' => $emergency]) }}
    data-place-map
    data-selected-place="{{ $selected['key'] ?? '' }}"
    aria-labelledby="place-map-title"
>
    <header class="place-map__header">
        <div>
            <p class="place-map__eyebrow">{{ $emergency ? __('place_directory.map.urgent_mode') : __('presentation.layer_name', ['name' => $layerLabel]) }}</p>
            <h2 id="place-map-title">{{ $emergency ? __('place_directory.map.clinics_title') : __('place_directory.map.places_title') }}</h2>
        </div>
        <div class="place-map__controls" aria-label="{{ __('place_directory.map.controls') }}">
            <button type="button" class="icon-button" data-place-zoom="in" aria-label="{{ __('place_directory.map.zoom_in') }}">
                <x-ui-icon name="plus" size="sm" />
            </button>
            <button type="button" class="icon-button" data-place-zoom="out" aria-label="{{ __('place_directory.map.zoom_out') }}">
                <x-ui-icon name="minus" size="sm" />
            </button>
            <button type="button" class="icon-button" data-place-fullscreen aria-label="{{ __('place_directory.map.fullscreen') }}" aria-pressed="false">
                <x-ui-icon name="maximize-2" size="sm" />
            </button>
        </div>
    </header>

    <div class="place-map__canvas" data-place-map-canvas>
        <span class="place-map__district place-map__district--old-town">{{ __('place_directory.map.old_town') }}</span>
        <span class="place-map__district place-map__district--zverynas">{{ __('ui.žvėrynas_76cb91baf6') }}</span>
        <span class="place-map__district place-map__district--naujamiestis">{{ __('ui.naujamiestis_17a26d0ce9') }}</span>
        <span class="place-map__river" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--north" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--south" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--east" aria-hidden="true"></span>

        @forelse ($places as $marker)
            <button
                type="button"
                class="place-marker place-marker--{{ $marker['category_tone'] }} {{ ($selected['key'] ?? null) === $marker['key'] ? 'place-marker--selected' : '' }}"
                data-place-marker="{{ $marker['key'] }}"
                data-place-position="{{ $marker['position'] }}"
                data-place-x="{{ $marker['x'] }}"
                data-place-y="{{ $marker['y'] }}"
                aria-label="{{ $marker['label'] }}"
                aria-pressed="{{ ($selected['key'] ?? null) === $marker['key'] ? 'true' : 'false' }}"
            >
                <x-ui-icon size="sm" :name="$marker['category_icon']" />
                @if ($marker['warning_count'] > 0)
                    <span class="place-marker__warning" aria-label="{{ trans_choice('presentation.warning_count', $marker['warning_count'], ['count' => $marker['warning_count']]) }}">{{ $marker['warning_count'] }}</span>
                @endif
            </button>
        @empty
            <p class="place-map__empty">{{ __('place_directory.map.no_points') }}</p>
        @endforelse
    </div>

    <div class="place-map__selection" data-place-selection aria-live="polite">
        @if ($selected)
            <div>
                <strong>{{ $selected['name'] }}</strong>
                <span>{{ $selected['open_label'] }} · {{ $selected['distance_label'] }}</span>
            </div>
            <div class="place-map__selection-actions">
                @if ($selected['call_url'])
                    <x-action-control
                        :href="$selected['call_url']"
                        label="{{ __('place_directory.actions.call') }}"
                        icon="phone"
                        variant="surface"
                        size="compact"
                    />
                @endif
                <x-action-control
                    :href="$selected['detail_url']"
                    label="{{ __('place_directory.actions.open') }}"
                    icon="arrow-right"
                    variant="primary"
                    size="compact"
                    data-place-selection-link
                />
            </div>
        @else
            <span>{{ __('place_directory.map.no_selection') }}</span>
        @endif
    </div>

    <ol class="sr-only" aria-label="{{ __('place_directory.map.text_alternative') }}">
        @forelse ($places as $marker)
            <li>{{ $marker['label'] }}</li>
        @empty
            <li>{{ __('place_directory.map.no_locations') }}</li>
        @endforelse
    </ol>
</section>
