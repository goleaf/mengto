<x-pet-social.app-shell :owner="$owner" title="Mia Carter | PawCircle" active-section="profile">
    <x-pet-social.page-stack data-section="member-profile">
        <x-pet-social.member-profile-hero
            :profile="$owner"
            section="owner-profile-hero"
            :eyebrow="$owner['role']"
            :avatar-alt="$owner['name']"
            :cover-height="760"
            :cover-small="$owner['cover_image_small']"
            :cover-medium="$owner['cover_image_medium']"
            secondary-label="Share profile"
            secondary-icon="share-2"
            primary-label="Edit profile"
            primary-icon="pencil"
            summary-label="Owner profile summary"
            :summary-icons="['paw-print', 'users', 'users-round']"
        />

        <x-pet-social.main-sidebar-layout>
            <x-slot:main>
                <x-pet-social.page-stack gap="content">
                    <x-pet-social.content-panel
                        section="about-owner"
                        eyebrow="Around the neighborhood"
                        title="About Mia"
                    >
                        <x-pet-social.section-copy :text="$owner['bio']" />
                    </x-pet-social.content-panel>

                    <x-pet-social.profile-pet-list :pets="$pets" />
                    <x-pet-social.recent-moments :posts="$recentMoments" eyebrow="From Mia" section="owner-moments" />
                </x-pet-social.page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-pet-social.content-panel
                    section="owner-availability"
                    eyebrow="Walk profile"
                    title="Availability"
                    size="compact"
                    tone="coral"
                >
                    <x-pet-social.definition-list
                        :items="$availability"
                        empty="Availability not shared."
                        strong
                        class="pc-section-body"
                    />
                </x-pet-social.content-panel>

                <x-pet-social.content-panel
                    section="owner-interests"
                    eyebrow="Common ground"
                    title="Interests"
                    size="compact"
                >
                    <x-pet-social.tag-list
                        :items="$interests"
                        empty="No interests shared."
                        roomy
                        class="pc-section-body"
                    />
                </x-pet-social.content-panel>

                <x-pet-social.content-panel
                    section="owner-communities"
                    eyebrow="Local circles"
                    title="Communities"
                    size="compact"
                    tone="coral"
                >
                    <x-pet-social.community-list :communities="$communities" class="pc-section-body" />
                </x-pet-social.content-panel>
            </x-slot:sidebar>
        </x-pet-social.main-sidebar-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
