<x-pet-social.app-shell :owner="$owner" title="Notifications | PawCircle" active-section="notifications">
    <x-pet-social.page-stack>
        <x-pet-social.page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            :count="$summary['count']"
            action-label="Mark all read"
            action-icon="check-check"
            data-section="notification-header"
        />

        <x-pet-social.main-sidebar-layout variant="compact">
            <x-slot:main>
                <x-pet-social.activity-timeline
                    :groups="$activityGroups"
                    :filters="$filters"
                    :unread-count="$summary['unread_count']"
                />
            </x-slot:main>

            <x-slot:sidebar>
                <x-pet-social.content-panel
                    section="weekly-activity"
                    eyebrow="This week"
                    title="Your activity"
                    size="compact"
                >
                    <x-pet-social.stat-grid
                        :items="$weeklyStats"
                        label="Weekly activity summary"
                        :icons="['paw-print', 'message-circle', 'users']"
                        empty="No weekly activity."
                        variant="panel"
                        tone="muted"
                        large
                    />
                </x-pet-social.content-panel>

                <x-pet-social.promo-card
                    :item="$upcoming"
                    section="activity-meetup"
                    :attendees="$upcoming['attendees']"
                />

                <x-pet-social.notification-settings :settings="$settings" />
            </x-slot:sidebar>
        </x-pet-social.main-sidebar-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
