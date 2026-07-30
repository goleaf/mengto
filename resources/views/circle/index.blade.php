<x-app-shell :owner="$owner" title="{{ __('ui.my_circle_brand_80cfb33331') }}" active-section="circle">
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
                        label="{{ __('ui.connections_dc27311748') }}"
                        icon="users-round"
                        variant="paper"
                        size="regular"
                    />
                    <x-action-control
                        :href="route('pet-friends.index')"
                        label="{{ __('ui.pet_friends_8866f0adbb') }}"
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
