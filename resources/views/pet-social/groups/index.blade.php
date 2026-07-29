<x-pet-social.directory-page
    :owner="$owner"
    title="Groups | PawCircle"
    active-section="groups"
    :summary="$summary"
    header-section="group-header"
    action-label="Create group"
    action-icon="users-round"
>
    <x-slot:summary-strip>
        <x-pet-social.summary-strip
            :items="$summary['highlights']"
            label="Community summary"
            :icons="['users', 'activity', 'map-pin']"
            empty="Community summary unavailable."
            data-section="group-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-pet-social.directory-toolbar
            :filters="$filters"
            label="Group filters"
            filters-label="Group category filters"
            sort-label="Most active"
            section="group-filters"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-social.group-directory-results :groups="$directoryGroups" />
    </x-slot:results>
</x-pet-social.directory-page>
