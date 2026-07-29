@props(['query', 'filters'])

<section data-section="discover-query" aria-labelledby="discover-query-title" class="pc-panel">
    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:justify-between sm:p-5">
        <div class="min-w-0">
            <p id="discover-query-title" class="pc-section-heading__eyebrow">{{ $query['label'] }}</p>
            <p class="mt-2 truncate text-lg font-semibold text-paw-ink">“{{ $query['text'] }}”</p>
        </div>

        <x-pet-social.icon-text icon="map-pin" class="pc-meta--strong shrink-0">
            {{ $query['location'] }}
        </x-pet-social.icon-text>
    </div>

    <div class="flex flex-wrap gap-2 border-t border-paw-line p-4 sm:px-5" role="group" aria-label="Discover category filters">
        @forelse ($filters as $filter)
            <x-pet-social.filter-chip :label="$filter" :active="$loop->first" />
        @empty
            <span class="text-sm text-paw-muted">Categories unavailable.</span>
        @endforelse
    </div>
</section>
