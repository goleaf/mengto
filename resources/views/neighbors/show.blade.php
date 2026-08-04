<x-app-shell :owner="$owner" :title="$copy['page']['title']" active-section="neighbors">
    <x-page-stack data-section="neighbor-profile" data-neighbor-profile>
        <x-text-link :href="route('neighbors.index')" icon="arrow-left" variant="back">
            {{ $copy['page']['back'] }}
        </x-text-link>

        <x-profile-hero
            :profile="$neighbor"
            section="neighbor-profile-hero"
            :summary-label="$copy['hero']['summary_label']"
            :summary-empty="$copy['hero']['summary_unavailable']"
            :summary-icons="['paw-print', 'users', 'map-pin']"
            :actions-label="$copy['page']['actions_label']"
            data-neighbor-profile-hero
        />

        <x-main-sidebar-layout variant="stacked">
            <x-slot:main>
                <x-page-stack gap="content">
                    <x-content-panel
                        section="about-neighbor"
                        :eyebrow="$copy['about']['eyebrow']"
                        :title="$copy['about']['title']"
                        :icon="$copy['about']['icon']"
                    >
                        <x-section-copy :text="$neighbor['bio']" />
                    </x-content-panel>

                    <x-neighbor-pet-summary :pet="$pet" :copy="$copy['pet']" />
                    <x-recent-moments
                        :posts="$recentMoments"
                        :eyebrow="$copy['moments']['eyebrow']"
                        :title="$copy['moments']['title']"
                        :empty-title="$copy['moments']['empty']"
                        :icon="$copy['moments']['icon']"
                        section="neighbor-moments"
                        data-neighbor-profile-moments
                    />
                </x-page-stack>
            </x-slot:main>

            <x-slot:sidebar>
                <x-content-panel
                    section="neighbor-interests"
                    :title="$copy['interests']['title']"
                    :icon="$copy['interests']['icon']"
                >
                    <x-tag-list :items="$neighbor['interests']" :empty="$copy['interests']['empty']" roomy class="section-body" />
                </x-content-panel>

                <x-mutual-neighbor-list
                    :neighbors="$mutualNeighbors"
                    :copy="$copy['mutual_neighbors']"
                />

                <x-content-panel
                    section="neighbor-communities"
                    :title="$copy['communities']['title']"
                    :icon="$copy['communities']['icon']"
                    data-neighbor-profile-communities
                >
                    <x-community-list :communities="$communities" :empty="$copy['communities']['empty']" class="section-body" />
                </x-content-panel>
            </x-slot:sidebar>
        </x-main-sidebar-layout>
    </x-page-stack>
</x-app-shell>
