<x-app-shell :owner="$owner" title="{{ __('ui.discover_brand_b18e6020d1') }}" active-section="discover">
    <x-page-stack>
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            data-section="discover-header"
        />

        <x-discover-query
            :query="$query"
            :filters="$filters"
            :search="$directoryQuery"
            :active-filter="$activeFilter"
        />

        <x-main-sidebar-layout>
            <x-slot:main>
                <x-discover-results :results="$results" />
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel
                    section="discover-pulse"
                    eyebrow="{{ __('ui.local_pulse_617ee5b089') }}"
                    title="{{ __('ui.around_you_now_57b5d36d0f') }}"
                    size="compact"
                >
                    <x-metric-list
                        :items="$pulse"
                        empty="{{ __('ui.local_activity_unavailable_8c8c11ec3d') }}"
                    />
                </x-content-panel>

                <x-content-panel
                    section="discover-trending"
                    eyebrow="{{ __('ui.popular_this_week_4c021c3e7c') }}"
                    title="{{ __('ui.topics_nearby_af5fe4f48a') }}"
                    size="compact"
                    tone="coral"
                >
                    <x-ranked-list :items="$trending" empty="{{ __('ui.no_trending_topics_83742bc18b') }}" />
                </x-content-panel>

                <x-promo-card :item="$weekend" section="discover-weekend" />
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
