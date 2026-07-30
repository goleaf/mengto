<x-directory-page
    :owner="$owner"
    title="Pets | PawCircle"
    active-section="pets"
    :summary="$summary"
    header-section="directory-header"
    action-label="Add pet"
    action-icon="plus"
    :action-href="route('compose', 'pet')"
>
    <x-slot:toolbar>
        <x-directory-toolbar
            :filters="$filters"
            label="Pet directory filters"
            filters-label="Species filters"
            sort-label="Nearby first"
            section="directory-filters"
            search-id="directory-search"
            search-label="Search pets"
            search-placeholder="Search by name or breed"
            :query="$directoryQuery"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="['recommended' => 'Nearby first', 'name' => 'Name']"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-directory-results :pets="$directoryPets" />
    </x-slot:results>
</x-directory-page>
