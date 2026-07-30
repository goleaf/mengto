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
            summary-label="Owner profile summary"
            :summary-icons="['paw-print', 'users', 'user-round-check', 'images']"
        />

        <x-tab-list
            :tabs="$profile['tabs']"
            label="Owner profile sections"
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
                        title="Pet profiles are private"
                        description="Mia shares this list only with the audience selected in her privacy settings."
                    />
                @else
                    <x-profile-pet-list
                        :pets="$profile['pets']"
                        eyebrow="Separate social profiles"
                        title="Mia's pets"
                        :can-manage="$profile['audience'] === 'owner'"
                    />
                @endif
                @break

            @case('posts')
                @if ($profile['posts_restricted'])
                    <x-notice
                        section="owner-posts-private"
                        icon="lock-keyhole"
                        title="Posts are limited"
                        description="Follow or connect with Mia to see the moments available to this audience."
                    />
                @else
                    <x-recent-moments
                        :posts="$profile['moments']"
                        eyebrow="Published by Mia"
                        title="Owner posts"
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
                                eyebrow="Public identity"
                                title="Profile details"
                            >
                                <x-definition-list
                                    :items="$profile['details']"
                                    strong
                                    class="section-body"
                                />
                            </x-content-panel>

                            <x-content-panel
                                section="owner-interests"
                                eyebrow="Common ground"
                                title="Interests"
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
                            eyebrow="Conversation"
                            title="Languages"
                            size="compact"
                        >
                            <x-icon-list
                                :items="$profile['languages']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-privacy-summary"
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
                                eyebrow="Around the neighborhood"
                                title="About Mia"
                            >
                                <x-section-copy :text="$profile['identity']['bio']" />
                            </x-content-panel>

                            @if ($profile['pets_restricted'])
                                <x-notice
                                    section="owner-pets-private"
                                    icon="lock-keyhole"
                                    title="Pet profiles are private"
                                    description="This audience cannot see Mia's pet list."
                                />
                            @else
                                <x-profile-pet-list
                                    :pets="$profile['pets']"
                                    eyebrow="At home with Mia"
                                    title="Scout, Nori, and family"
                                    :can-manage="$profile['audience'] === 'owner'"
                                />
                            @endif

                            @if ($profile['posts_restricted'])
                                <x-notice
                                    section="owner-posts-private"
                                    icon="lock-keyhole"
                                    title="Owner posts are limited"
                                    description="Mia shares these moments with a closer audience."
                                />
                            @else
                                <x-recent-moments
                                    :posts="$profile['moments']"
                                    eyebrow="From Mia"
                                    section="owner-moments"
                                />
                            @endif
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-content-panel
                            section="owner-profile-completion"
                            eyebrow="Profile basics"
                            title="Profile readiness"
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
                            eyebrow="Trust signals"
                            title="Badges"
                            size="compact"
                        >
                            <x-profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-availability"
                            eyebrow="Walk profile"
                            title="Availability"
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
