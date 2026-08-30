<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <x-page-stack gap="compact" class="pet-friends-page">
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            heading-id="pet-friends-heading"
            :count="$summary['count']"
            :action-label="__('ui.open_pet_profile')"
            action-icon="circle-user-round"
            :action-href="route($friend_center['source']['route_name'], $friend_center['source']['route_parameters'])"
        />

        <x-pet-friend-dashboard
            :summary="$summary"
            :center="$friend_center"
        />
    </x-page-stack>
</x-app-shell>
