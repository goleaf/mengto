<x-directory-page
    :owner="$owner"
    title="Neighbors | PawCircle"
    active-section="neighbors"
    :summary="$summary"
    header-section="neighbor-header"
    action-label="New message"
    action-icon="mail"
    :action-href="route('compose', 'message')"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="Neighbor summary"
            :icons="['navigation', 'heart-handshake', 'paw-print']"
            empty="Neighbor summary unavailable."
            data-section="neighbor-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-directory-toolbar
            :filters="$filters"
            label="Neighbor filters"
            filters-label="Neighbor category filters"
            sort-label="Closest first"
            section="neighbor-filters"
            search-id="neighbor-search"
            search-label="Search neighbors"
            search-placeholder="Search by person, pet, or neighborhood"
            :query="$directoryQuery"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="['closest' => 'Closest first', 'name' => 'Name']"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-neighbor-directory-results :neighbors="$directoryNeighbors" />
    </x-slot:results>
</x-directory-page>
