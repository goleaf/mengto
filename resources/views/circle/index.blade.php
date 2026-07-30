<x-app-shell :owner="$owner" title="My Circle | PawCircle" active-section="circle">
    <x-page-stack>
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
        >
            <x-slot:actions>
                <x-action-group>
                    <x-action-control
                        :href="route('connections.index')"
                        label="Connections"
                        icon="users-round"
                        variant="paper"
                        size="regular"
                    />
                    <x-action-control
                        :href="route('pet-friends.index')"
                        label="Pet friends"
                        icon="heart-handshake"
                        variant="primary"
                        size="regular"
                    />
                </x-action-group>
            </x-slot:actions>
        </x-page-header>

        <x-circle-dashboard
            :summary="$summary"
            :filters="$filters"
            :active-filter="$activeFilter"
            :collections="$collections"
            :show-starter="$showStarter"
            :starter-items="$starterItems"
        />
    </x-page-stack>
</x-app-shell>
