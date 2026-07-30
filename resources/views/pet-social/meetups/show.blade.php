<x-layout.app-shell
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
>
    <x-layout.page-stack>
        <x-ui.text-link :href="route('pet-social.meetups.index')" icon="arrow-left" variant="back">
            Back to events
        </x-ui.text-link>

        <x-object.event-hero :event="$event" />

        <x-ui.tab-list
            :tabs="$tabs"
            :label="$event['title'].' sections'"
            class="event-tabs"
        />

        <x-feature.event-dashboard
            :event="$event"
            :active-tab="$active_tab"
            :content="$content"
            :registration="$registration"
            :can-view-private-details="$can_view_private_details"
            :organizer-tools="$organizer_tools"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
