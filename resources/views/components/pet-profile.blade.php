@props(['profile'])

<x-app-shell
    :owner="$profile['owner']"
    :title="$profile['page_title']"
    :active-section="$profile['active_section']"
>
    <x-page-stack data-section="pet-profile">
        <x-profile-hero
            :profile="$profile['identity']"
            :badges="$profile['badges']"
            section="pet-profile-hero"
            summary-label="Pet profile summary"
            :summary-icons="['users', 'heart-handshake', 'images', 'footprints']"
        />

        <x-tab-list
            :tabs="$profile['tabs']"
            label="Pet profile sections"
        />

        <x-profile-view-switcher
            :options="$profile['audience_options']"
            :audience="$profile['audience']"
        />

        @switch($profile['active_tab'])
            @case('about')
                <x-main-sidebar-layout variant="stacked">
                    <x-slot:main>
                        <x-page-stack gap="content">
                            <x-content-panel
                                eyebrow="Daily life"
                                :title="'About '.$profile['identity']['name']"
                                section="pet-about"
                            >
                                <x-section-copy :text="$profile['identity']['story']" />
                                <x-tag-list
                                    :items="$profile['identity']['traits']"
                                    roomy
                                    class="section-body"
                                />
                            </x-content-panel>

                            <x-pet-facts
                                title="Compatibility"
                                section="compatibility"
                                :facts="$profile['identity']['compatibility']"
                            />
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-pet-facts
                            title="At a glance"
                            section="pet-facts"
                            :facts="$profile['identity']['facts']"
                        />
                        <x-owner-summary :owner="$profile['owner']" />
                        <x-profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
                @break

            @case('photos')
                <x-pet-gallery :photos="$profile['identity']['gallery']" />
                @break

            @case('friends')
                @if ($profile['friends_restricted'])
                    <x-notice
                        section="pet-friends-private"
                        icon="lock-keyhole"
                        title="Pet friends are private"
                        description="The owner shares this social list only with the selected audience."
                    />
                @else
                    @if ($profile['audience'] === 'owner')
                        <x-action-group class="profile-friend-actions">
                            <x-action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'friends',
                                ])"
                                label="Manage pet friends"
                                icon="heart-handshake"
                                variant="primary"
                                size="regular"
                            />
                            <x-action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'discover',
                                ])"
                                label="Find friends"
                                icon="search"
                                variant="paper"
                                size="regular"
                            />
                        </x-action-group>
                    @endif

                    <x-profile-pet-list
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
                    <x-main-sidebar-layout>
                        <x-slot:main>
                            <x-pet-facts
                                title="Care profile"
                                section="pet-care"
                                :facts="$profile['identity']['care']"
                            />
                        </x-slot:main>
                        <x-slot:sidebar>
                            <x-notice
                                section="care-privacy"
                                icon="shield-check"
                                title="Shared with permission"
                                description="Care details are shown for this audience by the profile owner. Exact medical records remain private."
                            />
                            @if ($profile['audience'] === 'owner')
                                <x-action-control
                                    :href="route('medical-records.index')"
                                    label="Open private medical records"
                                    icon="stethoscope"
                                    variant="primary"
                                    size="regular"
                                />
                            @endif
                        </x-slot:sidebar>
                    </x-main-sidebar-layout>
                @else
                    <x-notice
                        section="pet-care-private"
                        icon="lock-keyhole"
                        title="Care details are private"
                        description="Only owners and approved managers can view this section."
                    />
                @endif
                @break

            @case('family')
                <x-main-sidebar-layout>
                    <x-slot:main>
                        <x-content-panel
                            section="pet-family"
                            eyebrow="People behind the profile"
                            title="Owners and managers"
                        >
                            <x-profile-manager-list
                                :managers="$profile['managers']"
                                class="section-body"
                            />
                        </x-content-panel>
                    </x-slot:main>
                    <x-slot:sidebar>
                        <x-notice
                            section="pet-family-context"
                            icon="users-round"
                            title="People perform every action"
                            description="Posts, friend requests, walk invitations, and care updates are always managed by people."
                        />
                        <x-content-panel
                            section="pet-privacy-summary"
                            eyebrow="Audience controls"
                            title="Privacy summary"
                            size="compact"
                        >
                            <x-definition-list
                                :items="$profile['privacy']"
                                strong
                                class="section-body"
                            />
                        </x-content-panel>
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
                @break

            @default
                <x-main-sidebar-layout variant="stacked">
                    <x-slot:main>
                        @if ($profile['posts_restricted'])
                            <x-notice
                                section="pet-feed-private"
                                icon="lock-keyhole"
                                title="This pet feed is limited"
                                description="Follow or connect with the owner to see moments available to this audience."
                            />
                        @else
                            <x-recent-moments
                                :posts="$profile['moments']"
                                :eyebrow="'About '.$profile['identity']['name']"
                                title="Pet feed"
                                section="pet-moments"
                            />
                        @endif
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-owner-summary :owner="$profile['owner']" />
                        <x-content-panel
                            section="pet-profile-badges"
                            eyebrow="Profile signals"
                            title="Badges"
                            size="compact"
                        >
                            <x-profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-content-panel>
                        <x-profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
        @endswitch
    </x-page-stack>
</x-app-shell>
