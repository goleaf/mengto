<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <x-page-stack gap="compact" class="connections-page">
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Discover profiles"
            action-icon="search"
            :action-href="route('discover.index')"
            class="page-header--connections"
        />

        <x-connection-dashboard
            :summary="$summary"
            :connections="$connections"
        />
    </x-page-stack>
</x-app-shell>
