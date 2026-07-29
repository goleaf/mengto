<x-pet-social.directory-page
    :owner="$owner"
    title="Neighbors | PawCircle"
    active-section="neighbors"
    :summary="$summary"
    header-section="neighbor-header"
    action-label="Invite friends"
    action-icon="user-plus"
>
    <x-slot:summary-strip>
        <x-pet-social.summary-strip
            :items="$summary['highlights']"
            label="Neighbor summary"
            :icons="['navigation', 'heart-handshake', 'paw-print']"
            empty="Neighbor summary unavailable."
            data-section="neighbor-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-pet-social.directory-toolbar
            :filters="$filters"
            label="Neighbor filters"
            filters-label="Neighbor category filters"
            sort-label="Closest first"
            section="neighbor-filters"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-social.neighbor-directory-results :neighbors="$directoryNeighbors" />
    </x-slot:results>
</x-pet-social.directory-page>
