<x-app-shell :owner="$owner" title="Discover | PawCircle" active-section="discover">
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
                    eyebrow="Local pulse"
                    title="Around you now"
                    size="compact"
                >
                    <x-metric-list
                        :items="$pulse"
                        empty="Local activity unavailable."
                    />
                </x-content-panel>

                <x-content-panel
                    section="discover-trending"
                    eyebrow="Popular this week"
                    title="Topics nearby"
                    size="compact"
                    tone="coral"
                >
                    <x-ranked-list :items="$trending" empty="No trending topics." />
                </x-content-panel>

                <x-promo-card :item="$weekend" section="discover-weekend" />
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
