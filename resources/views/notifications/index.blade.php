<x-app-shell :owner="$owner" title="Notifications | PawCircle" active-section="notifications">
    <x-page-stack>
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Mark all read"
            action-icon="check-check"
            :action-endpoint="route('actions.perform')"
            :action-payload="['action' => 'mark-all-read', 'target' => 'notifications', 'label' => 'Notifications']"
            data-section="notification-header"
        />

        <x-main-sidebar-layout variant="compact">
            <x-slot:main>
                <x-activity-timeline
                    :groups="$activityGroups"
                    :filters="$filters"
                    :unread-count="$summary['unread_count']"
                    :active-filter="$activeFilter"
                />
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel
                    section="weekly-activity"
                    eyebrow="This week"
                    title="Your activity"
                    size="compact"
                >
                    <x-stat-grid
                        :items="$weeklyStats"
                        label="Weekly activity summary"
                        :icons="['paw-print', 'message-circle', 'users']"
                        empty="No weekly activity."
                        variant="panel"
                        tone="muted"
                        large
                    />
                </x-content-panel>

                <x-promo-card
                    :item="$upcoming"
                    section="activity-meetup"
                    :attendees="$upcoming['attendees']"
                />

                <x-notification-settings :settings="$settings" />
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
