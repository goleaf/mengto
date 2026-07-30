@props([
    'markers',
    'title' => 'Search map',
    'compact' => false,
])

<section {{ $attributes->class('grid gap-3') }} aria-labelledby="search-map-title">
    <div class="flex items-center justify-between gap-3">
        <h2 id="search-map-title" class="text-lg font-bold">{{ $title }}</h2>
        <span class="inline-flex items-center gap-1 text-xs font-semibold text-paw-muted">
            <x-lucide-shield-check class="size-4 text-paw-leaf" aria-hidden="true" />
            Generalized locations
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
                <x-lucide-map-pin class="size-4" aria-hidden="true" />
                <span class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden w-44 -translate-x-1/2 rounded bg-paw-ink px-2 py-1.5 text-center text-xs text-white shadow-lg group-hover:block group-focus:block">
                    {{ $marker['label'] ?? $marker['pet_name'] }} · {{ $marker['area'] ?? $marker['last_seen_area'] }}
                </span>
            </a>
        @empty
            <p class="absolute inset-0 grid place-items-center p-6 text-center text-sm font-semibold text-paw-muted">
                No public map points match these filters.
            </p>
        @endforelse
    </div>

    <ol class="grid gap-2 sm:grid-cols-2" aria-label="Map points in text form">
        @forelse ($markers as $marker)
            <li class="flex items-start gap-2 border-l-2 border-paw-coral pl-3 text-sm">
                <x-lucide-map-pin class="mt-0.5 size-4 shrink-0 text-paw-coral" aria-hidden="true" />
                <span>
                    <strong>{{ $marker['label'] ?? $marker['pet_name'] }}</strong>
                    · {{ $marker['area'] ?? $marker['last_seen_area'] }}
                    · {{ $marker['time'] ?? $marker['last_seen_label'] }}
                </span>
            </li>
        @empty
            <li class="text-sm text-paw-muted">No locations available.</li>
        @endforelse
    </ol>
</section>
