<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <x-layout.page-stack gap="compact" class="pet-friends-page">
        <x-layout.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Open pet profile"
            action-icon="circle-user-round"
            :action-href="route($friend_center['source']['route_name'], $friend_center['source']['route_parameters'])"
        />

        <x-feature.pet-friend-dashboard
            :summary="$summary"
            :center="$friend_center"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
