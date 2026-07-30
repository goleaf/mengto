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
            label="{{ __('ui.search_brand_a6f8e15d35') }}"
            placeholder="{{ __('ui.search_pets_people_meetups_and_groups_49b01f3814') }}"
            :value="$search"
        />

        <x-filter-group
            :filters="$filters"
            :active="$activeFilter"
            label="{{ __('ui.discover_category_filters_db5c46449b') }}"
            empty="{{ __('ui.categories_unavailable_4e5733146b') }}"
            submit
        />

        <x-action-control
            type="submit"
            label="{{ __('ui.search_49c266baaa') }}"
            icon="search"
            variant="primary"
            size="toolbar"
        />
    </form>
</section>
