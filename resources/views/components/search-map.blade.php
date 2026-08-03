@props([
    'markers',
    'title' => __('ui.search_map_c802781b2d'),
    'compact' => false,
])

<section data-search-map {{ $attributes->class('grid gap-3') }} aria-labelledby="search-map-title">
    <div class="flex items-center justify-between gap-3">
        <h2 id="search-map-title" class="text-lg font-bold">{{ $title }}</h2>
        <span data-search-map-privacy class="inline-flex items-center gap-1 text-xs font-semibold text-paw-muted">
            <x-ui-icon name="shield-check" size="sm" class="text-paw-leaf" />
            {{ __('ui.generalized_locations_f8a7558000') }}
        </span>
    </div>

    <div class="relative overflow-hidden rounded-md border border-paw-line bg-[#e9efe8] {{ $compact ? 'h-64' : 'h-[22rem] lg:h-[30rem]' }}">
        <div class="absolute inset-0 opacity-70" aria-hidden="true" style="background-image: linear-gradient(#c8d2ca 1px, transparent 1px), linear-gradient(90deg, #c8d2ca 1px, transparent 1px); background-size: 48px 48px;"></div>
        <div class="absolute inset-x-0 top-[42%] h-8 -rotate-3 bg-white/85 shadow-sm" aria-hidden="true"></div>
        <div class="absolute -bottom-10 left-[62%] size-52 rounded-full border-[20px] border-[#b7d7de] bg-[#d5e9ed]" aria-hidden="true"></div>

        @forelse ($markers as $marker)
            <a
                @if (isset($marker['slug'])) href="{{ route('lost-found.show', $marker['slug']) }}" @else href="#search-timeline" @endif
                class="group absolute z-10 grid size-9 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white bg-paw-coral text-white shadow-md focus:outline-none focus:ring-4 focus:ring-paw-sun"
                style="left: {{ $marker['x'] ?? $marker['map_x'] }}%; top: {{ $marker['y'] ?? $marker['map_y'] }}%;"
                aria-label="{{ $marker['label'] ?? $marker['pet_name'] }} · {{ $marker['area'] ?? $marker['last_seen_area'] }} · {{ $marker['time'] ?? $marker['last_seen_label'] }}"
            >
                <x-ui-icon name="map-pin" size="sm" />
                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden w-44 -translate-x-1/2 rounded bg-paw-ink px-2 py-1.5 text-center text-xs text-white shadow-lg group-hover:block group-focus:block">
                    {{ $marker['label'] ?? $marker['pet_name'] }} · {{ $marker['area'] ?? $marker['last_seen_area'] }}
                </span>
            </a>
        @empty
            <p class="absolute inset-0 grid place-items-center p-6 text-center text-sm font-semibold text-paw-muted">
                {{ __('ui.no_public_map_points_match_these_filters_2df593b49d') }}
            </p>
        @endforelse
    </div>

    <ol class="grid gap-2 sm:grid-cols-2" aria-label="{{ __('ui.map_points_in_text_form_591995251f') }}">
        @forelse ($markers as $marker)
            <li class="flex items-start gap-2 border-l-2 border-paw-coral pl-3 text-sm">
                <x-ui-icon name="map-pin" size="sm" class="mt-0.5 shrink-0 text-paw-coral" />
                <span>
                    <strong>{{ $marker['label'] ?? $marker['pet_name'] }}</strong>
                    · {{ $marker['area'] ?? $marker['last_seen_area'] }}
                    · {{ $marker['time'] ?? $marker['last_seen_label'] }}
                </span>
            </li>
        @empty
            <li class="text-sm text-paw-muted">{{ __('ui.no_locations_available_c1f36516a2') }}</li>
        @endforelse
    </ol>
</section>
