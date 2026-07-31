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
            <p class="place-map__eyebrow">{{ $emergency ? __('ui.urgent_mode_6593baaaa0') : __('presentation.layer_name', ['name' => $layerLabel]) }}</p>
            <h2 id="place-map-title">{{ $emergency ? __('ui.suitable_open_clinics_f0eae2582a') : __('ui.places_in_the_selected_area_8f304ae7f9') }}</h2>
        </div>
        <div class="place-map__controls" aria-label="{{ __('ui.map_controls_e463873f9f') }}">
            <button type="button" class="icon-button" data-place-zoom="in" aria-label="{{ __('ui.zoom_in_0e47f09a74') }}">
                <x-lucide-plus class="icon icon--sm" aria-hidden="true" />
            </button>
            <button type="button" class="icon-button" data-place-zoom="out" aria-label="{{ __('ui.zoom_out_bc7b631a68') }}">
                <x-lucide-minus class="icon icon--sm" aria-hidden="true" />
            </button>
            <button type="button" class="icon-button" data-place-fullscreen aria-label="{{ __('ui.toggle_fullscreen_map_55b5219245') }}" aria-pressed="false">
                <x-lucide-maximize-2 class="icon icon--sm" aria-hidden="true" />
            </button>
        </div>
    </header>

    <div class="place-map__canvas" data-place-map-canvas>
        <span class="place-map__district place-map__district--old-town">{{ __('ui.old_town_9a9e4acaf8') }}</span>
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
                <x-dynamic-component :component="'lucide-'.$marker['category_icon']" class="icon icon--sm" aria-hidden="true" />
                @if ($marker['warning_count'] > 0)
                    <span class="place-marker__warning" aria-label="{{ trans_choice('presentation.warning_count', $marker['warning_count'], ['count' => $marker['warning_count']]) }}">{{ $marker['warning_count'] }}</span>
                @endif
            </button>
        @empty
            <p class="place-map__empty">{{ __('ui.no_map_points_match_these_filters_813101d503') }}</p>
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
                        label="{{ __('ui.call_d6e645b7d2') }}"
                        icon="phone"
                        variant="surface"
                        size="compact"
                    />
                @endif
                <x-action-control
                    :href="$selected['detail_url']"
                    label="{{ __('ui.open_ed077f3d81') }}"
                    icon="arrow-right"
                    variant="primary"
                    size="compact"
                    data-place-selection-link
                />
            </div>
        @else
            <span>{{ __('ui.no_place_selected_21564af90e') }}</span>
        @endif
    </div>

    <ol class="sr-only" aria-label="{{ __('ui.text_alternative_for_map_locations_9fcfc6f49c') }}">
        @forelse ($places as $marker)
            <li>{{ $marker['label'] }}</li>
        @empty
            <li>{{ __('ui.no_locations_available_c1f36516a2') }}</li>
        @endforelse
    </ol>
</section>
