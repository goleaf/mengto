<x-pet-social.detail-page
    :owner="$owner"
    title="{{ $group['title'] }} | PawCircle"
    active-section="groups"
    section="group-detail"
    :back-href="route('pet-social.groups.index')"
    back-label="Back to groups"
>
    <x-slot:hero>
        <x-pet-social.detail-hero
            :detail="$group"
            section="group-detail-hero"
            primary-label="Join group"
            primary-icon="user-plus"
            secondary-label="Share"
            secondary-icon="send"
            summary-label="Community summary"
            :summary-icons="['users', 'messages-square', 'timer']"
        />
    </x-slot:hero>

    <x-slot:main>
        <x-pet-social.content-panel
            section="group-about"
            eyebrow="Small-space routines"
            title="About the community"
        >
            <x-pet-social.section-copy :text="$group['long_description']" />
            <x-pet-social.tag-list :items="$group['tags']" empty="No topics listed." roomy class="pc-section-body" />
        </x-pet-social.content-panel>

        <x-pet-social.content-panel
            section="group-principles"
            eyebrow="Community care"
            title="How the group works"
        >
            <x-pet-social.icon-list
                :items="$principles"
                empty="No community guidance available."
                class="pc-section-body"
            />
        </x-pet-social.content-panel>

        <x-pet-social.content-panel
            section="group-activity"
            eyebrow="Recent and upcoming"
            title="Around the group"
        >
            <x-slot:aside>
                <x-pet-social.static-action label="New post" icon="square-pen" variant="paper" size="compact" />
            </x-slot:aside>
            <x-pet-social.icon-list
                :items="$activity"
                empty="No recent activity."
                class="pc-section-body"
            />
        </x-pet-social.content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-pet-social.content-panel section="group-moderators" title="Community team" meta="3 organizers">
            <x-pet-social.member-list :members="$moderators" class="pc-section-body" />
        </x-pet-social.content-panel>

        <x-pet-social.content-panel section="group-details" title="Group details">
            <x-pet-social.definition-list :items="$details" strong class="pc-section-body" />
        </x-pet-social.content-panel>

        <x-pet-social.notice
            section="group-welcome"
            icon="house-heart"
            title="Made for real homes"
            description="Thoughtful questions are welcome, whether you share a studio with a cat or a high-rise with a senior dog."
        />
    </x-slot:sidebar>
</x-pet-social.detail-page>
