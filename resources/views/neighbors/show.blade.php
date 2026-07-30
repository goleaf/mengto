<x-app-shell :owner="$owner" title="Ari Jensen | PawCircle" active-section="neighbors">
    <x-page-stack data-section="neighbor-profile">
        <x-text-link :href="route('neighbors.index')" icon="arrow-left" variant="back">
            Back to neighbors
        </x-text-link>

        <x-profile-hero
            :profile="$neighbor"
            section="neighbor-profile-hero"
            summary-label="Neighbor profile summary"
            :summary-icons="['paw-print', 'users', 'map-pin']"
        />

        <x-main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-page-stack gap="content">
                    <x-content-panel
                        section="about-neighbor"
                        eyebrow="Around the neighborhood"
                        title="About Ari"
                    >
                        <x-section-copy :text="$neighbor['bio']" />
                    </x-content-panel>

                    <x-neighbor-pet-summary :pet="$pet" />
                    <x-recent-moments
                        :posts="$recentMoments"
                        eyebrow="From Ari and Mochi"
                        section="neighbor-moments"
                    />
                </x-page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel section="neighbor-interests" title="Shared interests">
                    <x-tag-list :items="$neighbor['interests']" empty="No shared interests yet." roomy class="section-body" />
                </x-content-panel>

                <x-mutual-neighbor-list
                    :neighbors="$mutualNeighbors"
                    :count="$neighbor['mutual_count']"
                />

                <x-content-panel section="neighbor-communities" title="Communities">
                    <x-community-list :communities="$communities" class="section-body" />
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
