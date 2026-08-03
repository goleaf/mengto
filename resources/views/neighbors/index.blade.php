<x-directory-page
    :owner="$owner"
    title="{{ __('neighbors.page.title') }}"
    active-section="neighbors"
    :summary="$summary"
    header-section="neighbor-header"
    action-label="{{ __('neighbors.actions.new_message') }}"
    action-icon="mail"
    :action-href="route('compose', 'message')"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('neighbors.summary.label') }}"
            :icons="['navigation', 'heart-handshake', 'paw-print']"
            empty="{{ __('neighbors.summary.unavailable') }}"
            data-section="neighbor-summary"
            data-neighbor-summary
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-directory-toolbar
            :filters="$filters"
            label="{{ __('neighbors.filters.toolbar_label') }}"
            filters-label="{{ __('neighbors.filters.category_label') }}"
            sort-label="{{ __('neighbors.sort.label') }}"
            section="neighbor-filters"
            search-id="neighbor-search"
            search-label="{{ __('neighbors.search.label') }}"
            search-placeholder="{{ __('neighbors.search.placeholder') }}"
            :query="$directoryQuery"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="['closest' => __('neighbors.sort.closest'), 'name' => __('neighbors.sort.name')]"
            data-neighbor-filters
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-neighbor-directory-results :neighbors="$directoryNeighbors" />
    </x-slot:results>
</x-directory-page>
