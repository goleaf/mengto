<x-pet-social.app-shell :owner="$owner" title="Discover | PawCircle" active-section="discover">
    <x-pet-social.page-stack>
        <x-pet-social.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Search again"
            action-icon="search"
            data-section="discover-header"
        />

        <x-pet-social.discover-query :query="$query" :filters="$filters" />

        <x-pet-social.main-sidebar-layout>
            <x-slot:main>
                <x-pet-social.discover-results :results="$results" />
            </x-slot:main>

            <x-slot:sidebar>
                <x-pet-social.content-panel
                    section="discover-pulse"
                    eyebrow="Local pulse"
                    title="Around you now"
                    size="compact"
                >
                    <x-pet-social.metric-list
                        :items="$pulse"
                        empty="Local activity unavailable."
                    />
                </x-pet-social.content-panel>

                <x-pet-social.content-panel
                    section="discover-trending"
                    eyebrow="Popular this week"
                    title="Topics nearby"
                    size="compact"
                    tone="coral"
                >
                    <x-pet-social.ranked-list :items="$trending" empty="No trending topics." />
                </x-pet-social.content-panel>

                <x-pet-social.promo-card :item="$weekend" section="discover-weekend" />
            </x-slot:sidebar>
        </x-pet-social.main-sidebar-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
