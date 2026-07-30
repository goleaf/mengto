<x-layout.directory-page
    :owner="$owner"
    title="Groups | PawCircle"
    active-section="groups"
    :summary="$summary"
    header-section="group-header"
    action-label="Create group"
    action-icon="users-round"
    :action-href="route('compose', 'group')"
>
    <x-slot:summary-strip>
        <x-ui.summary-strip
            :items="$summary['highlights']"
            label="Community summary"
            :icons="['users', 'activity', 'map-pin']"
            empty="Community summary unavailable."
            data-section="group-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        @if ($groups['last_dismissed'])
            <x-ui.notice
                section="group-recommendation-feedback"
                icon="eye-off"
                title="Recommendation hidden"
                :description="$groups['last_dismissed']['message']"
                class="mb-5"
            >
                <x-slot:actions>
                    <x-ui.action-control
                        :label="$groups['last_dismissed']['action']['label']"
                        :icon="$groups['last_dismissed']['action']['icon']"
                        :endpoint="$groups['last_dismissed']['action']['endpoint']"
                        :payload="$groups['last_dismissed']['action']['payload']"
                        variant="paper"
                    />
                </x-slot:actions>
            </x-ui.notice>
        @endif

        <x-feature.directory-toolbar
            :filters="$groups['filters']"
            label="Group filters"
            filters-label="Group category filters"
            sort-label="Sort groups"
            section="group-filters"
            search-id="group-search"
            search-label="Search groups"
            search-placeholder="Name, topic, city, or organizer"
            :query="$groups['query']"
            :active-filter="$groups['filter']"
            :active-sort="$groups['sort']"
            :sort-options="$groups['sort_options']"
            class="group-toolbar"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-feature.group-directory-results :groups="$groups['items']" />
    </x-slot:results>
</x-layout.directory-page>
