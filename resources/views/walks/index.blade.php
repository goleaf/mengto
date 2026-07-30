<x-layout.app-shell :owner="$owner" title="Walk Planner | PawCircle" active-section="meetups">
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
                        :href="route('messages.index', ['filter' => 'walk-plans'])"
                        label="Walk messages"
                        icon="messages-square"
                        variant="paper"
                        size="regular"
                    />
                    <x-ui.action-control
                        :href="route('compose', 'walk')"
                        label="New plan"
                        icon="calendar-plus"
                        variant="primary"
                        size="regular"
                    />
                </x-ui.action-group>
            </x-slot:actions>
        </x-layout.page-header>

        <x-feature.walk-planner-dashboard
            :summary="$summary"
            :filters="$filters"
            :active-filter="$activeFilter"
            :plans="$plans"
            :has-plans="$hasPlans"
            :starter-items="$starterItems"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
