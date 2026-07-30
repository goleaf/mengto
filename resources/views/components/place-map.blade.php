@props([
    'places',
    'selected' => null,
    'layer' => 'places',
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
            <p class="place-map__eyebrow">{{ $emergency ? 'Urgent mode' : str($layer)->headline().' layer' }}</p>
            <h2 id="place-map-title">{{ $emergency ? 'Suitable open clinics' : 'Places in the selected area' }}</h2>
        </div>
        <div class="place-map__controls" aria-label="Map controls">
            <button type="button" class="icon-button" data-place-zoom="in" aria-label="Zoom in">
                <x-lucide-plus class="icon icon--sm" aria-hidden="true" />
            </button>
            <button type="button" class="icon-button" data-place-zoom="out" aria-label="Zoom out">
                <x-lucide-minus class="icon icon--sm" aria-hidden="true" />
            </button>
            <button type="button" class="icon-button" data-place-fullscreen aria-label="Toggle fullscreen map" aria-pressed="false">
                <x-lucide-maximize-2 class="icon icon--sm" aria-hidden="true" />
            </button>
        </div>
    </header>

    <div class="place-map__canvas" data-place-map-canvas>
        <span class="place-map__district place-map__district--old-town">Old Town</span>
        <span class="place-map__district place-map__district--zverynas">Žvėrynas</span>
        <span class="place-map__district place-map__district--naujamiestis">Naujamiestis</span>
        <span class="place-map__river" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--north" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--south" aria-hidden="true"></span>
        <span class="place-map__road place-map__road--east" aria-hidden="true"></span>

        @forelse ($places as $marker)
            <button
                type="button"
                class="place-marker place-marker--{{ $marker['category_tone'] }} {{ ($selected['key'] ?? null) === $marker['key'] ? 'place-marker--selected' : '' }}"
                style="--marker-x: {{ $marker['x'] }}%; --marker-y: {{ $marker['y'] }}%;"
                data-place-marker="{{ $marker['key'] }}"
                data-place-position="{{ $marker['position'] }}"
                aria-label="{{ $marker['label'] }}"
                aria-pressed="{{ ($selected['key'] ?? null) === $marker['key'] ? 'true' : 'false' }}"
            >
                <x-dynamic-component :component="'lucide-'.$marker['category_icon']" class="icon icon--sm" aria-hidden="true" />
                @if ($marker['warning_count'] > 0)
                    <span class="place-marker__warning" aria-label="{{ $marker['warning_count'] }} active warnings">{{ $marker['warning_count'] }}</span>
                @endif
            </button>
        @empty
            <p class="place-map__empty">No map points match these filters.</p>
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
                        label="Call"
                        icon="phone"
                        variant="surface"
                        size="compact"
                    />
                @endif
                <x-action-control
                    :href="$selected['detail_url']"
                    label="Open"
                    icon="arrow-right"
                    variant="primary"
                    size="compact"
                    data-place-selection-link
                />
            </div>
        @else
            <span>No place selected.</span>
        @endif
    </div>

    <ol class="sr-only" aria-label="Text alternative for map locations">
        @forelse ($places as $marker)
            <li>{{ $marker['label'] }}</li>
        @empty
            <li>No locations available.</li>
        @endforelse
    </ol>
</section>
