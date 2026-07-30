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
            summary-label="{{ __('ui.pet_profile_summary_eeffba5e83') }}"
            :summary-icons="['users', 'heart-handshake', 'images', 'footprints']"
        />

        <x-tab-list
            :tabs="$profile['tabs']"
            label="{{ __('ui.pet_profile_sections_15b3568cac') }}"
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
                                eyebrow="{{ __('ui.daily_life_e51ed38f2a') }}"
                                :title="__('ui.about_4efca0d10c').' '.$profile['identity']['name']"
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
                                title="{{ __('ui.compatibility_b37ddf1f6e') }}"
                                section="compatibility"
                                :facts="$profile['identity']['compatibility']"
                            />
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-pet-facts
                            title="{{ __('ui.at_a_glance_46b396c859') }}"
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
                        title="{{ __('ui.pet_friends_are_private_2269e0b56f') }}"
                        description="{{ __('ui.the_owner_shares_this_social_list_only_with_6beb9b1e16') }}"
                    />
                @else
                    @if ($profile['audience'] === 'owner')
                        <x-action-group class="profile-friend-actions">
                            <x-action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'friends',
                                ])"
                                label="{{ __('ui.manage_pet_friends_d1a8d71739') }}"
                                icon="heart-handshake"
                                variant="primary"
                                size="regular"
                            />
                            <x-action-control
                                :href="route('pet-friends.index', [
                                    'pet' => $profile['identity']['slug'],
                                    'tab' => 'discover',
                                ])"
                                label="{{ __('ui.find_friends_feb7bfd172') }}"
                                icon="search"
                                variant="paper"
                                size="regular"
                            />
                        </x-action-group>
                    @endif

                    <x-profile-pet-list
                        :pets="$profile['friends']"
                        eyebrow="{{ __('ui.pet_connections_9529c7ef7c') }}"
                        :title="$profile['identity']['name'].'s friends'"
                        :can-manage="false"
                        empty-title="{{ __('ui.no_pet_friends_yet_a437f446f6') }}"
                    />
                @endif
                @break

            @case('care')
                @if ($profile['care_visible'])
                    <x-main-sidebar-layout>
                        <x-slot:main>
                            <x-pet-facts
                                title="{{ __('ui.care_profile_a9c229194d') }}"
                                section="pet-care"
                                :facts="$profile['identity']['care']"
                            />
                        </x-slot:main>
                        <x-slot:sidebar>
                            <x-notice
                                section="care-privacy"
                                icon="shield-check"
                                title="{{ __('ui.shared_with_permission_9fe27b23c6') }}"
                                description="{{ __('ui.care_details_are_shown_for_this_audience_by_fddb370059') }}"
                            />
                            @if ($profile['audience'] === 'owner')
                                <x-action-group class="mt-4">
                                    <x-action-control
                                        :href="route('medical-records.index')"
                                        label="{{ __('ui.health_record_6ab2c69d2f') }}"
                                        icon="stethoscope"
                                        size="regular"
                                    />
                                    <x-action-control
                                        :href="route('care-journals.index')"
                                        label="{{ __('ui.care_journal_7e2e73e4fb') }}"
                                        icon="notebook-tabs"
                                        variant="primary"
                                        size="regular"
                                    />
                                    <x-action-control
                                        :href="route('devices.index')"
                                        label="{{ __('ui.smart_devices_228fd3f770') }}"
                                        icon="radio-tower"
                                        size="regular"
                                    />
                                </x-action-group>
                            @endif
                        </x-slot:sidebar>
                    </x-main-sidebar-layout>
                @else
                    <x-notice
                        section="pet-care-private"
                        icon="lock-keyhole"
                        title="{{ __('ui.care_details_are_private_f52d4170cf') }}"
                        description="{{ __('ui.only_owners_and_approved_managers_can_view_this_3b631e6914') }}"
                    />
                @endif
                @break

            @case('family')
                <x-main-sidebar-layout>
                    <x-slot:main>
                        <x-content-panel
                            section="pet-family"
                            eyebrow="{{ __('ui.people_behind_the_profile_e89783a43f') }}"
                            title="{{ __('ui.owners_and_managers_28539cb842') }}"
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
                            title="{{ __('ui.people_perform_every_action_aa6b5b4f58') }}"
                            description="{{ __('ui.posts_friend_requests_walk_invitations_and_care_updates_54fd05b7f3') }}"
                        />
                        <x-content-panel
                            section="pet-privacy-summary"
                            eyebrow="{{ __('ui.audience_controls_5b3f1cd201') }}"
                            title="{{ __('ui.privacy_summary_3c83b0e331') }}"
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
                                title="{{ __('ui.this_pet_feed_is_limited_e234e0c75f') }}"
                                description="{{ __('ui.follow_or_connect_with_the_owner_to_see_73289c6d62') }}"
                            />
                        @else
                            <x-recent-moments
                                :posts="$profile['moments']"
                                :eyebrow="__('ui.about_4efca0d10c').' '.$profile['identity']['name']"
                                title="{{ __('ui.pet_feed_e95604e8e0') }}"
                                section="pet-moments"
                            />
                        @endif
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-owner-summary :owner="$profile['owner']" />
                        <x-content-panel
                            section="pet-profile-badges"
                            eyebrow="{{ __('ui.profile_signals_4f5bc3fb04') }}"
                            title="{{ __('ui.badges_185d8ef0ae') }}"
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
