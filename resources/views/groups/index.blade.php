<x-directory-page
    title="{{ __('groups.directory.page_title') }}"
    active-section="groups"
    :summary="$summary"
    header-section="group-header"
    action-label="{{ __('groups.directory.create_group') }}"
    action-icon="users-round"
    :action-href="route('compose', 'group')"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('groups.directory.summary_label') }}"
            :icons="['users', 'activity', 'map-pin']"
            empty="{{ __('groups.directory.summary_unavailable') }}"
            data-group-summary
            data-section="group-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        @if ($groups['last_dismissed'])
            <x-notice
                section="group-recommendation-feedback"
                icon="eye-off"
                title="{{ __('groups.directory.recommendation_hidden') }}"
                :description="$groups['last_dismissed']['message']"
                class="mb-5"
            >
                <x-slot:actions>
                    <x-action-control
                        :label="$groups['last_dismissed']['action']['label']"
                        :icon="$groups['last_dismissed']['action']['icon']"
                        :endpoint="$groups['last_dismissed']['action']['endpoint']"
                        :payload="$groups['last_dismissed']['action']['payload']"
                        variant="paper"
                    />
                </x-slot:actions>
            </x-notice>
        @endif

        <x-directory-toolbar
            :filters="$groups['filters']"
            label="{{ __('groups.directory.filters_label') }}"
            filters-label="{{ __('groups.directory.filter_categories_label') }}"
            sort-label="{{ __('groups.directory.sort_label') }}"
            section="group-filters"
            search-id="group-search"
            search-label="{{ __('groups.directory.search_label') }}"
            search-placeholder="{{ __('groups.directory.search_placeholder') }}"
            :query="$groups['query']"
            :active-filter="$groups['filter']"
            :active-sort="$groups['sort']"
            :sort-options="$groups['sort_options']"
            data-group-filters
            class="group-toolbar"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-group-directory-results :groups="$groups['items']" />
    </x-slot:results>
</x-directory-page>
