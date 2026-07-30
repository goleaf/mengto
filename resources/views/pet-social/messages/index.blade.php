<x-layout.app-shell :owner="$owner" title="Messages | PawCircle" active-section="messages">
    <x-layout.page-stack>
        <x-layout.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            data-section="message-header"
        >
            <x-slot:actions>
                <x-ui.action-group>
                    <x-ui.action-control
                        :href="route('pet-social.walks.index')"
                        label="Walk planner"
                        icon="footprints"
                        variant="paper"
                        size="regular"
                    />
                    <x-ui.action-control
                        :href="route('pet-social.compose', 'message')"
                        label="New message"
                        icon="mail"
                        variant="primary"
                        size="regular"
                    />
                </x-ui.action-group>
            </x-slot:actions>
        </x-layout.page-header>

        @if ($activeFilter === 'walk-plans')
            <x-feature.walk-message-summary :plans="$walkPlans" />
        @endif

        <x-layout.message-center-layout :thread-first="$threadFirst">
            <x-slot:conversations>
            <x-feature.conversation-list
                :conversations="$conversations"
                :filters="$filters"
                :unread-count="$summary['unread_count']"
                :query="$conversationQuery"
                :active-filter="$activeFilter"
            />
            </x-slot:conversations>

            <x-slot:thread>
                <x-feature.message-thread :thread="$thread" />
            </x-slot:thread>
        </x-layout.message-center-layout>
    </x-layout.page-stack>
</x-layout.app-shell>
