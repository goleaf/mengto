<x-pet-social.directory-page
    :owner="$owner"
    title="Pets | PawCircle"
    active-section="pets"
    :summary="$summary"
    header-section="directory-header"
>
    <x-slot:toolbar>
        <x-pet-social.directory-toolbar
            :filters="$filters"
            label="Pet directory filters"
            filters-label="Species filters"
            sort-label="Nearby first"
            section="directory-filters"
            search-id="directory-search"
            search-label="Search pets"
            search-placeholder="Search by name or breed"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-social.pet-directory-results :pets="$directoryPets" />
    </x-slot:results>
</x-pet-social.directory-page>
