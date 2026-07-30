@props(['profile'])

<x-layout.app-shell
    :owner="$profile['owner']"
    :title="$profile['page_title']"
    :active-section="$profile['active_section']"
>
    <x-layout.page-stack data-section="pet-profile">
        <x-object.profile-hero
            :profile="$profile['identity']"
            :badges="$profile['badges']"
            section="pet-profile-hero"
            summary-label="Pet profile summary"
            :summary-icons="['users', 'heart-handshake', 'images', 'footprints']"
        />

        <x-ui.tab-list
            :tabs="$profile['tabs']"
            label="Pet profile sections"
        />

        <x-feature.profile-view-switcher
            :options="$profile['audience_options']"
            :audience="$profile['audience']"
        />

        @switch($profile['active_tab'])
            @case('about')
                <x-layout.main-sidebar-layout variant="stacked">
                    <x-slot:main>
                        <x-layout.page-stack gap="content">
                            <x-ui.content-panel
                                eyebrow="Daily life"
                                :title="'About '.$profile['identity']['name']"
                                section="pet-about"
                            >
                                <x-ui.section-copy :text="$profile['identity']['story']" />
                                <x-ui.tag-list
                                    :items="$profile['identity']['traits']"
                                    roomy
                                    class="section-body"
                                />
                            </x-ui.content-panel>

                            <x-object.pet-facts
                                title="Compatibility"
                                section="compatibility"
                                :facts="$profile['identity']['compatibility']"
                            />
                        </x-layout.page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-object.pet-facts
                            title="At a glance"
                            section="pet-facts"
                            :facts="$profile['identity']['facts']"
                        />
                        <x-object.owner-summary :owner="$profile['owner']" />
                        <x-feature.profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-layout.main-sidebar-layout>
                @break

            @case('photos')
                <x-object.pet-gallery :photos="$profile['identity']['gallery']" />
                @break

            @case('friends')
                @if ($profile['friends_restricted'])
                    <x-ui.notice
                        section="pet-friends-private"
                        icon="lock-keyhole"
                        title="Pet friends are private"
                        description="The owner shares this social list only with the selected audience."
                    />
                @else
                    @if ($profile['audience'] === 'owner')
                        <x-ui.action-group class="profile-friend-actions">
                            <x-ui.action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'friends',
                                ])"
                                label="Manage pet friends"
                                icon="heart-handshake"
                                variant="primary"
                                size="regular"
                            />
                            <x-ui.action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'discover',
                                ])"
                                label="Find friends"
                                icon="search"
                                variant="paper"
                                size="regular"
                            />
                        </x-ui.action-group>
                    @endif

                    <x-feature.profile-pet-list
                        :pets="$profile['friends']"
                        eyebrow="Pet connections"
                        :title="$profile['identity']['name'].'s friends'"
                        :can-manage="false"
                        empty-title="No pet friends yet"
                    />
                @endif
                @break

            @case('care')
                @if ($profile['care_visible'])
                    <x-layout.main-sidebar-layout>
                        <x-slot:main>
                            <x-object.pet-facts
                                title="Care profile"
                                section="pet-care"
                                :facts="$profile['identity']['care']"
                            />
                        </x-slot:main>
                        <x-slot:sidebar>
                            <x-ui.notice
                                section="care-privacy"
                                icon="shield-check"
                                title="Shared with permission"
                                description="Care details are shown for this audience by the profile owner. Exact medical records remain private."
                            />
                        </x-slot:sidebar>
                    </x-layout.main-sidebar-layout>
                @else
                    <x-ui.notice
                        section="pet-care-private"
                        icon="lock-keyhole"
                        title="Care details are private"
                        description="Only owners and approved managers can view this section."
                    />
                @endif
                @break

            @case('family')
                <x-layout.main-sidebar-layout>
                    <x-slot:main>
                        <x-ui.content-panel
                            section="pet-family"
                            eyebrow="People behind the profile"
                            title="Owners and managers"
                        >
                            <x-object.profile-manager-list
                                :managers="$profile['managers']"
                                class="section-body"
                            />
                        </x-ui.content-panel>
                    </x-slot:main>
                    <x-slot:sidebar>
                        <x-ui.notice
                            section="pet-family-context"
                            icon="users-round"
                            title="People perform every action"
                            description="Posts, friend requests, walk invitations, and care updates are always managed by people."
                        />
                        <x-ui.content-panel
                            section="pet-privacy-summary"
                            eyebrow="Audience controls"
                            title="Privacy summary"
                            size="compact"
                        >
                            <x-ui.definition-list
                                :items="$profile['privacy']"
                                strong
                                class="section-body"
                            />
                        </x-ui.content-panel>
                    </x-slot:sidebar>
                </x-layout.main-sidebar-layout>
                @break

            @default
                <x-layout.main-sidebar-layout variant="stacked">
                    <x-slot:main>
                        @if ($profile['posts_restricted'])
                            <x-ui.notice
                                section="pet-feed-private"
                                icon="lock-keyhole"
                                title="This pet feed is limited"
                                description="Follow or connect with the owner to see moments available to this audience."
                            />
                        @else
                            <x-feature.recent-moments
                                :posts="$profile['moments']"
                                :eyebrow="'About '.$profile['identity']['name']"
                                title="Pet feed"
                                section="pet-moments"
                            />
                        @endif
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-object.owner-summary :owner="$profile['owner']" />
                        <x-ui.content-panel
                            section="pet-profile-badges"
                            eyebrow="Profile signals"
                            title="Badges"
                            size="compact"
                        >
                            <x-object.profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-ui.content-panel>
                        <x-feature.profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-layout.main-sidebar-layout>
        @endswitch
    </x-layout.page-stack>
</x-layout.app-shell>
