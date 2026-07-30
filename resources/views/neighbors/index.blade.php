<x-directory-page
    :owner="$owner"
    title="{{ __('ui.neighbors_brand_de44b47ada') }}"
    active-section="neighbors"
    :summary="$summary"
    header-section="neighbor-header"
    action-label="{{ __('ui.new_message_78f5975a5d') }}"
    action-icon="mail"
    :action-href="route('compose', 'message')"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('ui.neighbor_summary_9b59b6f189') }}"
            :icons="['navigation', 'heart-handshake', 'paw-print']"
            empty="{{ __('ui.neighbor_summary_unavailable_f5be52b5f7') }}"
            data-section="neighbor-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-directory-toolbar
            :filters="$filters"
            label="{{ __('ui.neighbor_filters_08abe6849c') }}"
            filters-label="{{ __('ui.neighbor_category_filters_d43990e27b') }}"
            sort-label="{{ __('ui.closest_first_f178d8be90') }}"
            section="neighbor-filters"
            search-id="neighbor-search"
            search-label="{{ __('ui.search_neighbors_7e5e2e7b1e') }}"
            search-placeholder="{{ __('ui.search_by_person_pet_or_neighborhood_6827cfd6ce') }}"
            :query="$directoryQuery"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="['closest' => __('ui.closest_first_f178d8be90'), 'name' => __('ui.name_dcd1d5223f')]"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-neighbor-directory-results :neighbors="$directoryNeighbors" />
    </x-slot:results>
</x-directory-page>
