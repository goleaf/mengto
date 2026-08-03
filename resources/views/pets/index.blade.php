<x-directory-page
    :owner="$owner"
    title="{{ __('ui.pets_brand_85e7072e28') }}"
    active-section="pets"
    :summary="$summary"
    header-section="directory-header"
    action-label="{{ __('ui.add_pet_7065b90594') }}"
    action-icon="plus"
    :action-href="route('pets.manage.create')"
>
    <x-slot:toolbar>
        <x-directory-toolbar
            :filters="$filters"
            label="{{ __('ui.pet_directory_filters_fc5e24e3a2') }}"
            filters-label="{{ __('ui.species_filters_b122c9f0a1') }}"
            sort-label="{{ __('ui.nearby_first_a4ffbf414c') }}"
            section="directory-filters"
            search-id="directory-search"
            search-label="{{ __('ui.search_pets_9eb4bc35f3') }}"
            search-placeholder="{{ __('ui.search_by_name_or_breed_4cae794f3b') }}"
            :query="$directoryQuery"
            :active-filter="$activeFilter"
            :active-sort="$activeSort"
            :sort-options="['recommended' => __('ui.nearby_first_a4ffbf414c'), 'name' => __('ui.name_dcd1d5223f')]"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-directory-results :pets="$directoryPets" />
    </x-slot:results>
</x-directory-page>
