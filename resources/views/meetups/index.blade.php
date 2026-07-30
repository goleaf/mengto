<x-directory-page
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
    :summary="$summary"
    header-section="event-header"
    action-label="Create event"
    action-icon="calendar-plus"
    :action-href="$events['create_url']"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="Event schedule summary"
            :icons="['calendar-days', 'sparkles', 'bookmark', 'clock-3']"
            empty="Event summary unavailable."
            :columns="4"
            data-section="event-summary"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
    </x-slot:toolbar>

    <x-slot:results>
        <x-event-directory :events="$events" />
    </x-slot:results>
</x-directory-page>
