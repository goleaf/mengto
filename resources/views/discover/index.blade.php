<x-layout.app-shell :owner="$owner" title="Discover | PawCircle" active-section="discover">
    <x-layout.page-stack>
        <x-layout.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            data-section="discover-header"
        />

        <x-feature.discover-query
            :query="$query"
            :filters="$filters"
            :search="$directoryQuery"
            :active-filter="$activeFilter"
        />

        <x-layout.main-sidebar-layout>
            <x-slot:main>
                <x-feature.discover-results :results="$results" />
            </x-slot:main>

            <x-slot:sidebar>
                <x-ui.content-panel
                    section="discover-pulse"
                    eyebrow="Local pulse"
                    title="Around you now"
                    size="compact"
                >
                    <x-ui.metric-list
                        :items="$pulse"
                        empty="Local activity unavailable."
                    />
                </x-ui.content-panel>

                <x-ui.content-panel
                    section="discover-trending"
                    eyebrow="Popular this week"
                    title="Topics nearby"
                    size="compact"
                    tone="coral"
                >
                    <x-object.ranked-list :items="$trending" empty="No trending topics." />
                </x-ui.content-panel>

                <x-object.promo-card :item="$weekend" section="discover-weekend" />
            </x-slot:sidebar>
        </x-layout.main-sidebar-layout>
    </x-layout.page-stack>
</x-layout.app-shell>
