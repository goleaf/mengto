<x-app-shell :owner="$owner" title="{{ __('ui.notifications_brand_c7c4c56ebe') }}" active-section="notifications">
    <x-page-stack>
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            heading-id="notifications-heading"
            :count="$summary['count']"
            :action-label="__('ui.mark_all_read_3bc62a9e6a')"
            action-icon="check-check"
            :action-endpoint="route('actions.perform')"
            :action-payload="['action' => 'mark-all-read', 'target' => 'notifications', 'label' => __('ui.notifications_788011833a')]"
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
                    eyebrow="{{ __('ui.this_week_8c4eef5ab2') }}"
                    title="{{ __('ui.your_activity_aef2eb4ec5') }}"
                    size="compact"
                >
                    <x-stat-grid
                        :items="$weeklyStats"
                        label="{{ __('ui.weekly_activity_summary_cbd54558cc') }}"
                        :icons="['paw-print', 'message-circle', 'users']"
                        empty="{{ __('ui.no_weekly_activity_721a7ffcb6') }}"
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
