<x-app-shell :owner="$owner" title="{{ __('ui.walk_planner_brand_b113eccac9') }}" active-section="meetups">
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
                        :href="route('messages.index', ['filter' => 'walk-plans'])"
                        label="{{ __('ui.walk_messages_389ba5a893') }}"
                        icon="messages-square"
                        variant="paper"
                        size="regular"
                    />
                    <x-action-control
                        :href="route('compose', 'walk')"
                        label="{{ __('ui.new_plan_0e646825df') }}"
                        icon="calendar-plus"
                        variant="primary"
                        size="regular"
                    />
                </x-action-group>
            </x-slot:actions>
        </x-page-header>

        <x-walk-planner-dashboard
            :summary="$summary"
            :filters="$filters"
            :active-filter="$activeFilter"
            :plans="$plans"
            :has-plans="$hasPlans"
            :starter-items="$starterItems"
        />
    </x-page-stack>
</x-app-shell>
