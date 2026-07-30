<x-layout.app-shell :owner="$owner" title="My Circle | PawCircle" active-section="circle">
    <x-layout.page-stack>
        <x-layout.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
        >
            <x-slot:actions>
                <x-ui.action-group>
                    <x-ui.action-control
                        :href="route('connections.index')"
                        label="Connections"
                        icon="users-round"
                        variant="paper"
                        size="regular"
                    />
                    <x-ui.action-control
                        :href="route('pet-friends.index')"
                        label="Pet friends"
                        icon="heart-handshake"
                        variant="primary"
                        size="regular"
                    />
                </x-ui.action-group>
            </x-slot:actions>
        </x-layout.page-header>

        <x-feature.circle-dashboard
            :summary="$summary"
            :filters="$filters"
            :active-filter="$activeFilter"
            :collections="$collections"
            :show-starter="$showStarter"
            :starter-items="$starterItems"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
