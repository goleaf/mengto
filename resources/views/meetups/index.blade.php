<x-directory-page
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
    :summary="$summary"
    header-section="event-header"
    action-label="{{ __('ui.create_event_946cbe2dbb') }}"
    action-icon="calendar-plus"
    :action-href="$events['create_url']"
>
    <x-slot:summary-strip>
        <x-summary-strip
            :items="$summary['highlights']"
            label="{{ __('ui.event_schedule_summary_3b1ab1da2e') }}"
            :icons="['calendar-days', 'sparkles', 'bookmark', 'clock-3']"
            empty="{{ __('ui.event_summary_unavailable_b29f788909') }}"
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
