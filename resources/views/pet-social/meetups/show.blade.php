<x-pet-social.detail-page
    :owner="$owner"
    title="{{ $meetup['title'] }} | PawCircle"
    active-section="meetups"
    section="meetup-detail"
    :back-href="route('pet-social.meetups.index')"
    back-label="Back to meetups"
>
    <x-slot:hero>
        <x-pet-social.detail-hero
            :detail="$meetup"
            section="meetup-detail-hero"
            primary-label="RSVP"
            primary-icon="calendar-plus"
            secondary-label="Share"
            secondary-icon="send"
            summary-label="Meetup summary"
            :summary-icons="['users', 'clock-3', 'paw-print']"
        />
    </x-slot:hero>

    <x-slot:main>
        <x-pet-social.content-panel
            section="meetup-about"
            eyebrow="A comfortable social"
            title="About this meetup"
        >
            <x-pet-social.section-copy :text="$meetup['long_description']" />
        </x-pet-social.content-panel>

        <x-pet-social.content-panel
            section="meetup-expectations"
            eyebrow="Before you arrive"
            title="What to expect"
        >
            <x-pet-social.icon-list
                :items="$expectations"
                empty="No arrival guidance available."
                class="pc-section-body"
            />
        </x-pet-social.content-panel>

        <x-pet-social.content-panel
            section="meetup-location"
            eyebrow="Laurelhurst Park"
            title="Meeting spot and route"
        >
            <x-pet-social.callout
                icon="map-pinned"
                title="Covered picnic tables by the SE Ankeny entrance"
                description="Jamie will be beside the park map with a yellow PawCircle bandana. The social stays inside the adjacent fenced lawn."
                class="pc-section-body"
            />
            <x-pet-social.definition-list :items="$details" strong class="pc-section-body" />
        </x-pet-social.content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-pet-social.panel section="meetup-host">
            <x-pet-social.person-summary
                :person="$host"
                kicker="Your host"
                action-label="Message host"
                action-icon="message-circle"
            />
        </x-pet-social.panel>

        <x-pet-social.content-panel section="meetup-attendees" title="Who's going" meta="18 neighbors">
            <x-pet-social.member-list :members="$attendees" class="pc-section-body" />
        </x-pet-social.content-panel>

        <x-pet-social.notice
            section="meetup-reminder"
            icon="shield-check"
            title="A considerate gathering"
            description="Give every pet room, follow park rules, and check with the host before bringing shared treats."
        />
    </x-slot:sidebar>
</x-pet-social.detail-page>
