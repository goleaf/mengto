<x-pet-social.directory-page
    :owner="$owner"
    title="Meetups | PawCircle"
    active-section="meetups"
    :summary="$summary"
    header-section="meetup-header"
    action-label="Create meetup"
    action-icon="calendar-plus"
>
    <x-slot:summary-strip>
        <x-pet-social.summary-strip
            :items="$summary['schedule']"
            label="Meetup schedule summary"
            :icons="['calendar-days', 'users', 'navigation']"
            empty="Schedule unavailable."
            data-section="meetup-schedule"
        />
    </x-slot:summary-strip>

    <x-slot:toolbar>
        <x-pet-social.directory-toolbar
            :filters="$filters"
            label="Meetup filters"
            filters-label="Meetup type filters"
            sort-label="Soonest first"
            section="meetup-filters"
        />
    </x-slot:toolbar>

    <x-slot:results>
        <x-pet-social.meetup-directory-results :meetups="$directoryMeetups" />
    </x-slot:results>
</x-pet-social.directory-page>
