@props(['profile'])

<x-app-shell
    :owner="$profile['owner']"
    :title="$profile['page_title']"
    :active-section="$profile['active_section']"
>
    <x-page-stack data-section="owner-profile">
        <x-profile-hero
            :profile="$profile['identity']"
            :badges="$profile['badges']"
            section="owner-profile-hero"
            summary-label="{{ __('ui.owner_profile_summary_6ca384ad6a') }}"
            :summary-icons="['paw-print', 'users', 'user-round-check', 'images']"
        />

        <x-tab-list
            :tabs="$profile['tabs']"
            label="{{ __('ui.owner_profile_sections_a02fb9e1e6') }}"
        />

        <x-profile-view-switcher
            :options="$profile['audience_options']"
            :audience="$profile['audience']"
        />

        @switch($profile['active_tab'])
            @case('pets')
                @if ($profile['pets_restricted'])
                    <x-notice
                        section="owner-pets-private"
                        icon="lock-keyhole"
                        title="{{ __('ui.pet_profiles_are_private_a2b7104fd0') }}"
                        description="{{ __('ui.mia_shares_this_list_only_with_the_audience_466e8eeeae') }}"
                    />
                @else
                    <x-profile-pet-list
                        :pets="$profile['pets']"
                        eyebrow="{{ __('ui.separate_social_profiles_ab5cfb68f8') }}"
                        title="{{ __('ui.mia_s_pets_4ba9532eb0') }}"
                        :can-manage="$profile['audience'] === 'owner'"
                    />
                @endif
                @break

            @case('posts')
                @if ($profile['posts_restricted'])
                    <x-notice
                        section="owner-posts-private"
                        icon="lock-keyhole"
                        title="{{ __('ui.posts_are_limited_f801cbd804') }}"
                        description="{{ __('ui.follow_or_connect_with_mia_to_see_the_1c4176d85e') }}"
                    />
                @else
                    <x-recent-moments
                        :posts="$profile['moments']"
                        eyebrow="{{ __('ui.published_by_mia_9c4c9d21f9') }}"
                        title="{{ __('ui.owner_posts_8986a90a06') }}"
                        section="owner-posts"
                    />
                @endif
                @break

            @case('about')
                <x-main-sidebar-layout>
                    <x-slot:main>
                        <x-page-stack gap="content">
                            <x-content-panel
                                section="owner-about-details"
                                eyebrow="{{ __('ui.public_identity_284303e3ab') }}"
                                title="{{ __('ui.profile_details_73949bb1bd') }}"
                            >
                                <x-definition-list
                                    :items="$profile['details']"
                                    strong
                                    class="section-body"
                                />
                            </x-content-panel>

                            <x-content-panel
                                section="owner-interests"
                                eyebrow="{{ __('ui.common_ground_c295bed5fb') }}"
                                title="{{ __('ui.interests_756aaea140') }}"
                            >
                                <x-tag-list
                                    :items="$profile['interests']"
                                    roomy
                                    class="section-body"
                                />
                            </x-content-panel>
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-content-panel
                            section="owner-languages"
                            eyebrow="{{ __('ui.conversation_ccca181757') }}"
                            title="{{ __('ui.languages_318655cea4') }}"
                            size="compact"
                        >
                            <x-icon-list
                                :items="$profile['languages']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-privacy-summary"
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

                        <x-profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
                @break

            @default
                <x-main-sidebar-layout>
                    <x-slot:main>
                        <x-page-stack gap="content">
                            <x-content-panel
                                section="about-owner"
                                eyebrow="{{ __('ui.around_the_neighborhood_db1c68dbb1') }}"
                                title="{{ __('ui.about_mia_45de98db9b') }}"
                            >
                                <x-section-copy :text="$profile['identity']['bio']" />
                            </x-content-panel>

                            @if ($profile['pets_restricted'])
                                <x-notice
                                    section="owner-pets-private"
                                    icon="lock-keyhole"
                                    title="{{ __('ui.pet_profiles_are_private_a2b7104fd0') }}"
                                    description="{{ __('ui.this_audience_cannot_see_mia_s_pet_list_e106bf7e2b') }}"
                                />
                            @else
                                <x-profile-pet-list
                                    :pets="$profile['pets']"
                                    eyebrow="{{ __('ui.at_home_with_mia_ccd49f05ec') }}"
                                    title="{{ __('ui.scout_nori_and_family_da531492d5') }}"
                                    :can-manage="$profile['audience'] === 'owner'"
                                />
                            @endif

                            @if ($profile['posts_restricted'])
                                <x-notice
                                    section="owner-posts-private"
                                    icon="lock-keyhole"
                                    title="{{ __('ui.owner_posts_are_limited_b0f790305b') }}"
                                    description="{{ __('ui.mia_shares_these_moments_with_a_closer_audience_a6180e10ed') }}"
                                />
                            @else
                                <x-recent-moments
                                    :posts="$profile['moments']"
                                    eyebrow="{{ __('ui.from_mia_f791282978') }}"
                                    section="owner-moments"
                                />
                            @endif
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-content-panel
                            section="owner-profile-completion"
                            eyebrow="{{ __('ui.profile_basics_0145f57561') }}"
                            title="{{ __('ui.profile_readiness_e75c4c7ee1') }}"
                            size="compact"
                            tone="coral"
                        >
                            <x-progress-meter
                                :value="$profile['completion']['value']"
                                :label="$profile['completion']['label']"
                                :detail="$profile['completion']['detail']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-profile-badges"
                            eyebrow="{{ __('ui.trust_signals_a02028d200') }}"
                            title="{{ __('ui.badges_185d8ef0ae') }}"
                            size="compact"
                        >
                            <x-profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-availability"
                            eyebrow="{{ __('ui.walk_profile_3ccfc4f87c') }}"
                            title="{{ __('ui.availability_12f67f8539') }}"
                            size="compact"
                            tone="coral"
                        >
                            <x-definition-list
                                :items="$profile['availability']"
                                strong
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
        @endswitch
    </x-page-stack>
</x-app-shell>
