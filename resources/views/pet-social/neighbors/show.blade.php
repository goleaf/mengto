<x-layout.app-shell :owner="$owner" title="Ari Jensen | PawCircle" active-section="neighbors">
    <x-layout.page-stack data-section="neighbor-profile">
        <x-ui.text-link :href="route('pet-social.neighbors.index')" icon="arrow-left" variant="back">
            Back to neighbors
        </x-ui.text-link>

        <x-object.profile-hero
            :profile="$neighbor"
            section="neighbor-profile-hero"
            summary-label="Neighbor profile summary"
            :summary-icons="['paw-print', 'users', 'map-pin']"
        />

        <x-layout.main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-layout.page-stack gap="content">
                    <x-ui.content-panel
                        section="about-neighbor"
                        eyebrow="Around the neighborhood"
                        title="About Ari"
                    >
                        <x-ui.section-copy :text="$neighbor['bio']" />
                    </x-ui.content-panel>

                    <x-object.neighbor-pet-summary :pet="$pet" />
                    <x-feature.recent-moments
                        :posts="$recentMoments"
                        eyebrow="From Ari and Mochi"
                        section="neighbor-moments"
                    />
                </x-layout.page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-ui.content-panel section="neighbor-interests" title="Shared interests">
                    <x-ui.tag-list :items="$neighbor['interests']" empty="No shared interests yet." roomy class="section-body" />
                </x-ui.content-panel>

                <x-object.mutual-neighbor-list
                    :neighbors="$mutualNeighbors"
                    :count="$neighbor['mutual_count']"
                />

                <x-ui.content-panel section="neighbor-communities" title="Communities">
                    <x-object.community-list :communities="$communities" class="section-body" />
                </x-ui.content-panel>
            </x-slot:sidebar>
        </x-layout.main-sidebar-layout>
    </x-layout.page-stack>
</x-layout.app-shell>
