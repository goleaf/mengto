<x-layout.directory-page
    :owner="$owner"
    title="Neighbors | PawCircle"
    active-section="neighbors"
    :summary="$summary"
    header-section="neighbor-header"
    action-label="New message"
    action-icon="mail"
    :action-href="route('pet-social.compose', 'message')"
>
    <x-slot:summary-strip>
        <x-ui.summary-strip
            :items="$summary['highlights']"
            label="Neighbor summary"
            :icons="['navigation', 'heart-handshake', 'paw-print']"
            empty="Neighbor summary unavailable."
            data-section="neighbor-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-feature.directory-toolbar
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
        <x-feature.neighbor-directory-results :neighbors="$directoryNeighbors" />
    </x-slot:results>
</x-layout.directory-page>
