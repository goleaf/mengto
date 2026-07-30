<x-app-shell :owner="$owner" title="{{ __('ui.ari_jensen_brand_03c8f42448') }}" active-section="neighbors">
    <x-page-stack data-section="neighbor-profile">
        <x-text-link :href="route('neighbors.index')" icon="arrow-left" variant="back">
            {{ __('ui.back_to_neighbors_fa21633126') }}
        </x-text-link>

        <x-profile-hero
            :profile="$neighbor"
            section="neighbor-profile-hero"
            summary-label="{{ __('ui.neighbor_profile_summary_4bffb292c8') }}"
            :summary-icons="['paw-print', 'users', 'map-pin']"
        />

        <x-main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-page-stack gap="content">
                    <x-content-panel
                        section="about-neighbor"
                        eyebrow="{{ __('ui.around_the_neighborhood_db1c68dbb1') }}"
                        title="{{ __('ui.about_ari_5694328dcd') }}"
                    >
                        <x-section-copy :text="$neighbor['bio']" />
                    </x-content-panel>

                    <x-neighbor-pet-summary :pet="$pet" />
                    <x-recent-moments
                        :posts="$recentMoments"
                        eyebrow="{{ __('ui.from_ari_and_mochi_eb84448e3d') }}"
                        section="neighbor-moments"
                    />
                </x-page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel section="neighbor-interests" title="{{ __('ui.shared_interests_c118d2e5eb') }}">
                    <x-tag-list :items="$neighbor['interests']" empty="{{ __('ui.no_shared_interests_yet_acff43c7b7') }}" roomy class="section-body" />
                </x-content-panel>

                <x-mutual-neighbor-list
                    :neighbors="$mutualNeighbors"
                    :count="$neighbor['mutual_count']"
                />

                <x-content-panel section="neighbor-communities" title="{{ __('ui.communities_c864f329f5') }}">
                    <x-community-list :communities="$communities" class="section-body" />
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
