@props(['searchCase'])

<article data-search-case-card {{ $attributes->class('grid min-h-full overflow-hidden rounded-md border border-paw-line bg-white') }}>
    <a href="{{ route('lost-found.show', $searchCase['slug']) }}" class="group grid" aria-label="{{ __('presentation.open_search_for', ['pet' => $searchCase['pet_name']]) }}">
        <div class="relative aspect-[16/10] overflow-hidden bg-paw-mint">
            @if ($searchCase['cover_url'])
                <img
                    src="{{ $searchCase['cover_url'] }}"
                    alt="{{ $searchCase['pet_name'] }}, {{ strtolower($searchCase['species_label']) }}, {{ $searchCase['color'] }}"
                    class="size-full object-cover transition duration-300 group-hover:scale-[1.02]"
                >
            @else
                <div class="grid size-full place-items-center">
                    <x-ui-icon size="4xl" :name="$searchCase['type_icon']" class="text-paw-leaf" />
                </div>
            @endif
            <span data-search-case-type class="absolute left-3 top-3 inline-flex items-center gap-1 rounded bg-white/95 px-2 py-1 text-xs font-bold shadow-sm">
                <x-ui-icon size="sm" :name="$searchCase['type_icon']" />
                {{ $searchCase['type_label'] }}
            </span>
        </div>

        <div class="grid content-start gap-3 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold">{{ $searchCase['pet_name'] }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">
                        <span data-search-case-species>{{ $searchCase['species_label'] }}</span>
                        @if ($searchCase['breed'])
                            · {{ $searchCase['breed'] }}
                        @endif
                    </p>
                </div>
                <span data-search-case-status class="shrink-0 rounded px-2 py-1 text-xs font-bold {{ $searchCase['urgent'] ? 'bg-red-100 text-red-800' : 'bg-paw-mint text-paw-leaf' }}">
                    {{ $searchCase['status_label'] }}
                </span>
            </div>

            <p class="text-sm leading-6 text-paw-muted">{{ $searchCase['description'] }}</p>

            <dl class="grid gap-2 border-t border-paw-line pt-3 text-sm">
                <div class="flex items-start gap-2">
                    <x-ui-icon name="map-pin" size="sm" class="mt-0.5 shrink-0 text-paw-coral" />
                    <div>
                        <dt data-search-case-area-label class="sr-only">{{ __('ui.area_024dc204d7') }}</dt>
                        <dd class="font-semibold">{{ $searchCase['last_seen_area'] }}</dd>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-paw-muted">
                    <x-ui-icon name="clock-3" size="sm" class="shrink-0" />
                    <dt data-search-case-last-seen-label class="sr-only">{{ __('ui.last_seen_21fd79c7de') }}</dt>
                    <dd>{{ $searchCase['last_seen_label'] }}</dd>
                </div>
            </dl>

            <div data-search-case-counts class="flex flex-wrap gap-3 text-xs font-semibold text-paw-muted">
                <span>{{ trans_choice('presentation.sightings_count', $searchCase['confirmed_sightings_count'], ['count' => $searchCase['confirmed_sightings_count']]) }}</span>
                <span>{{ trans_choice('presentation.volunteers_count', $searchCase['active_volunteers_count'], ['count' => $searchCase['active_volunteers_count']]) }}</span>
                <span>{{ trans_choice('presentation.tasks_count', $searchCase['open_tasks_count'], ['count' => $searchCase['open_tasks_count']]) }}</span>
            </div>
        </div>
    </a>
</article>
