@props(['query', 'filters', 'search' => '', 'activeFilter' => 'top-matches'])

<section data-section="discover-query" aria-labelledby="discover-query-title" class="panel">
    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-end sm:justify-between sm:p-5">
        <div class="min-w-0">
            <p id="discover-query-title" class="section-heading__eyebrow">{{ $query['label'] }}</p>
            <p class="mt-2 text-lg font-semibold text-paw-ink">“{{ $query['text'] }}”</p>
        </div>

        <x-icon-text icon="map-pin" class="meta--strong shrink-0">
            {{ $query['location'] }}
        </x-icon-text>
    </div>

    <form method="GET" action="{{ route('discover.index') }}" class="discover-search">
        <x-search-field
            id="discover-search"
            label="Search PawCircle"
            placeholder="Search pets, people, meetups, and groups"
            :value="$search"
        />

        <x-filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="Discover category filters"
            empty="Categories unavailable."
            submit
        />

        <x-action-control
            type="submit"
            label="Search"
            icon="search"
            variant="primary"
            size="toolbar"
        />
    </form>
</section>
