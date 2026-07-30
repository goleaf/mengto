<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <x-layout.page-stack gap="compact" class="connections-page">
        <x-layout.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Discover profiles"
            action-icon="search"
            :action-href="route('pet-social.discover.index')"
            class="page-header--connections"
        />

        <x-feature.connection-dashboard
            :summary="$summary"
            :connections="$connections"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
