@props(['profile'])

<x-layout.app-shell
    :owner="$profile['owner']"
    :title="$profile['page_title']"
    :active-section="$profile['active_section']"
>
    <x-layout.page-stack data-section="owner-profile">
        <x-object.profile-hero
            :profile="$profile['identity']"
            :badges="$profile['badges']"
            section="owner-profile-hero"
            summary-label="Owner profile summary"
            :summary-icons="['paw-print', 'users', 'user-round-check', 'images']"
        />

        <x-ui.tab-list
            :tabs="$profile['tabs']"
            label="Owner profile sections"
        />

        <x-feature.profile-view-switcher
            :options="$profile['audience_options']"
            :audience="$profile['audience']"
        />

        @switch($profile['active_tab'])
            @case('pets')
                @if ($profile['pets_restricted'])
                    <x-ui.notice
                        section="owner-pets-private"
                        icon="lock-keyhole"
                        title="Pet profiles are private"
                        description="Mia shares this list only with the audience selected in her privacy settings."
                    />
                @else
                    <x-feature.profile-pet-list
                        :pets="$profile['pets']"
                        eyebrow="Separate social profiles"
                        title="Mia's pets"
                        :can-manage="$profile['audience'] === 'owner'"
                    />
                @endif
                @break

            @case('posts')
                @if ($profile['posts_restricted'])
                    <x-ui.notice
                        section="owner-posts-private"
                        icon="lock-keyhole"
                        title="Posts are limited"
                        description="Follow or connect with Mia to see the moments available to this audience."
                    />
                @else
                    <x-feature.recent-moments
                        :posts="$profile['moments']"
                        eyebrow="Published by Mia"
                        title="Owner posts"
                        section="owner-posts"
                    />
                @endif
                @break

            @case('about')
                <x-layout.main-sidebar-layout>
                    <x-slot:main>
                        <x-layout.page-stack gap="content">
                            <x-ui.content-panel
                                section="owner-about-details"
                                eyebrow="Public identity"
                                title="Profile details"
                            >
                                <x-ui.definition-list
                                    :items="$profile['details']"
                                    strong
                                    class="section-body"
                                />
                            </x-ui.content-panel>

                            <x-ui.content-panel
                                section="owner-interests"
                                eyebrow="Common ground"
                                title="Interests"
                            >
                                <x-ui.tag-list
                                    :items="$profile['interests']"
                                    roomy
                                    class="section-body"
                                />
                            </x-ui.content-panel>
                        </x-layout.page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-ui.content-panel
                            section="owner-languages"
                            eyebrow="Conversation"
                            title="Languages"
                            size="compact"
                        >
                            <x-ui.icon-list
                                :items="$profile['languages']"
                                class="section-body"
                            />
                        </x-ui.content-panel>

                        <x-ui.content-panel
                            section="owner-privacy-summary"
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

                        <x-feature.profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-layout.main-sidebar-layout>
                @break

            @default
                <x-layout.main-sidebar-layout>
                    <x-slot:main>
                        <x-layout.page-stack gap="content">
                            <x-ui.content-panel
                                section="about-owner"
                                eyebrow="Around the neighborhood"
                                title="About Mia"
                            >
                                <x-ui.section-copy :text="$profile['identity']['bio']" />
                            </x-ui.content-panel>

                            @if ($profile['pets_restricted'])
                                <x-ui.notice
                                    section="owner-pets-private"
                                    icon="lock-keyhole"
                                    title="Pet profiles are private"
                                    description="This audience cannot see Mia's pet list."
                                />
                            @else
                                <x-feature.profile-pet-list
                                    :pets="$profile['pets']"
                                    eyebrow="At home with Mia"
                                    title="Scout, Nori, and family"
                                    :can-manage="$profile['audience'] === 'owner'"
                                />
                            @endif

                            @if ($profile['posts_restricted'])
                                <x-ui.notice
                                    section="owner-posts-private"
                                    icon="lock-keyhole"
                                    title="Owner posts are limited"
                                    description="Mia shares these moments with a closer audience."
                                />
                            @else
                                <x-feature.recent-moments
                                    :posts="$profile['moments']"
                                    eyebrow="From Mia"
                                    section="owner-moments"
                                />
                            @endif
                        </x-layout.page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-ui.content-panel
                            section="owner-profile-completion"
                            eyebrow="Profile basics"
                            title="Profile readiness"
                            size="compact"
                            tone="coral"
                        >
                            <x-ui.progress-meter
                                :value="$profile['completion']['value']"
                                :label="$profile['completion']['label']"
                                :detail="$profile['completion']['detail']"
                                class="section-body"
                            />
                        </x-ui.content-panel>

                        <x-ui.content-panel
                            section="owner-profile-badges"
                            eyebrow="Trust signals"
                            title="Badges"
                            size="compact"
                        >
                            <x-object.profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-ui.content-panel>

                        <x-ui.content-panel
                            section="owner-availability"
                            eyebrow="Walk profile"
                            title="Availability"
                            size="compact"
                            tone="coral"
                        >
                            <x-ui.definition-list
                                :items="$profile['availability']"
                                strong
                                class="section-body"
                            />
                        </x-ui.content-panel>

                        <x-feature.profile-safety-actions :actions="$profile['safety_actions']" />
                    </x-slot:sidebar>
                </x-layout.main-sidebar-layout>
        @endswitch
    </x-layout.page-stack>
</x-layout.app-shell>
