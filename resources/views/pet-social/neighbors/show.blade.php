<x-pet-social.app-shell :owner="$owner" title="Ari Jensen | PawCircle" active-section="neighbors">
    <x-pet-social.page-stack data-section="neighbor-profile">
        <x-pet-social.text-link :href="route('pet-social.neighbors.index')" icon="arrow-left" variant="back">
            Back to neighbors
        </x-pet-social.text-link>

        <x-pet-social.member-profile-hero
            :profile="$neighbor"
            section="neighbor-profile-hero"
            :eyebrow="$neighbor['category']"
            :avatar-alt="$neighbor['avatar_alt']"
            :cover-small="$neighbor['cover_image_small']"
            :cover-medium="$neighbor['cover_image_medium']"
            secondary-label="Message"
            secondary-icon="message-circle"
            primary-label="Follow"
            primary-icon="user-plus"
            summary-label="Neighbor profile summary"
            :summary-icons="['paw-print', 'users', 'map-pin']"
        />

        <x-pet-social.main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-pet-social.page-stack gap="content">
                    <x-pet-social.content-panel
                        section="about-neighbor"
                        eyebrow="Around the neighborhood"
                        title="About Ari"
                    >
                        <x-pet-social.section-copy :text="$neighbor['bio']" />
                    </x-pet-social.content-panel>

                    <x-pet-social.neighbor-pet-summary :pet="$pet" />
                    <x-pet-social.recent-moments
                        :posts="$recentMoments"
                        eyebrow="From Ari and Mochi"
                        section="neighbor-moments"
                    />
                </x-pet-social.page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-pet-social.content-panel section="neighbor-interests" title="Shared interests">
                    <x-pet-social.tag-list :items="$neighbor['interests']" empty="No shared interests yet." roomy class="pc-section-body" />
                </x-pet-social.content-panel>

                <x-pet-social.mutual-neighbor-list
                    :neighbors="$mutualNeighbors"
                    :count="$neighbor['mutual_count']"
                />

                <x-pet-social.content-panel section="neighbor-communities" title="Communities">
                    <x-pet-social.community-list :communities="$communities" class="pc-section-body" />
                </x-pet-social.content-panel>
            </x-slot:sidebar>
        </x-pet-social.main-sidebar-layout>
    </x-pet-social.page-stack>
</x-pet-social.app-shell>
