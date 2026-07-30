<x-app-shell
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
>
    <x-page-stack>
        <x-text-link :href="route('meetups.index')" icon="arrow-left" variant="back">
            {{ __('ui.back_to_events_6f92c8203b') }}
        </x-text-link>

        <x-event-hero :event="$event" />

        <x-tab-list
            :tabs="$tabs"
            :label="__('presentation.sections_for', ['name' => $event['title']])"
            class="event-tabs"
        />

        <x-event-dashboard
            :event="$event"
            :active-tab="$active_tab"
            :content="$content"
            :registration="$registration"
            :can-view-private-details="$can_view_private_details"
            :organizer-tools="$organizer_tools"
        />
    </x-page-stack>
</x-app-shell>
