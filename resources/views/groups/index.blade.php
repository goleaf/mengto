<x-directory-page
    :owner="$owner"
    title="{{ __('ui.groups_brand_2cc8a218be') }}"
    active-section="groups"
    :summary="$summary"
    header-section="group-header"
    action-label="{{ __('ui.create_group_35be9c541d') }}"
    action-icon="users-round"
    :action-href="route('compose', 'group')"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('ui.community_summary_2014a7ddb6') }}"
            :icons="['users', 'activity', 'map-pin']"
            empty="{{ __('ui.community_summary_unavailable_2b08b30afc') }}"
            data-section="group-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        @if ($groups['last_dismissed'])
            <x-notice
                section="group-recommendation-feedback"
                icon="eye-off"
                title="{{ __('ui.recommendation_hidden_28d507ab00') }}"
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
            label="{{ __('ui.group_filters_6abd03f6d9') }}"
            filters-label="{{ __('ui.group_category_filters_2e1d2c99cc') }}"
            sort-label="{{ __('ui.sort_groups_f511b3d2dc') }}"
            section="group-filters"
            search-id="group-search"
            search-label="{{ __('ui.search_groups_6b6482a28b') }}"
            search-placeholder="{{ __('ui.name_topic_city_or_organizer_0ed7b1b6f6') }}"
            :query="$groups['query']"
            :active-filter="$groups['filter']"
            :active-sort="$groups['sort']"
            :sort-options="$groups['sort_options']"
            class="group-toolbar"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-group-directory-results :groups="$groups['items']" />
    </x-slot:results>
</x-directory-page>
