@props(['profile'])

<x-app-shell
    :owner="$profile['owner']"
    :title="$profile['page_title']"
    :active-section="$profile['active_section']"
>
    <x-page-stack
        data-section="owner-profile"
        data-owner-profile
        data-owner-profile-tab="{{ $profile['active_tab'] }}"
        data-owner-profile-audience="{{ $profile['audience'] }}"
    >
        <x-profile-hero
            :profile="$profile['identity']"
            :badges="$profile['badges']"
            section="owner-profile-hero"
            :summary-label="$profile['copy']['hero']['summary_label']"
            :summary-empty="$profile['copy']['hero']['summary_unavailable']"
            :summary-icons="['paw-print', 'users', 'user-round-check', 'images']"
            :actions-label="$profile['copy']['hero']['actions_label']"
            data-owner-profile-hero
        />

        <x-tab-list
            :tabs="$profile['tabs']"
            :label="$profile['copy']['tabs']['label']"
        />

        <x-profile-view-switcher
            :options="$profile['audience_options']"
            :audience="$profile['audience']"
            :copy="$profile['copy']['preview']"
            data-owner-profile-preview
        />

        @switch($profile['active_tab'])
            @case('pets')
                @if ($profile['pets_restricted'])
                    <x-notice
                        section="owner-pets-private"
                        icon="lock-keyhole"
                        :title="$profile['copy']['restrictions']['pets']['title']"
                        :description="$profile['copy']['restrictions']['pets']['tab_description']"
                    />
                @else
                    <x-profile-pet-list
                        :pets="$profile['pets']"
                        :eyebrow="$profile['copy']['sections']['pets']['tab_eyebrow']"
                        :title="$profile['copy']['sections']['pets']['tab_title']"
                        :empty-title="$profile['copy']['sections']['pets']['empty']"
                        :can-manage="$profile['audience'] === 'owner'"
                        :add-action="$profile['copy']['sections']['pets']['add_action']"
                        :icon="$profile['copy']['sections']['pets']['icon']"
                    />
                @endif
                @break

            @case('posts')
                @if ($profile['posts_restricted'])
                    <x-notice
                        section="owner-posts-private"
                        icon="lock-keyhole"
                        :title="$profile['copy']['restrictions']['posts']['title']"
                        :description="$profile['copy']['restrictions']['posts']['tab_description']"
                    />
                @else
                    <x-recent-moments
                        :posts="$profile['moments']"
                        :eyebrow="$profile['copy']['sections']['posts']['tab_eyebrow']"
                        :title="$profile['copy']['sections']['posts']['tab_title']"
                        :empty-title="$profile['copy']['sections']['posts']['empty']"
                        :icon="$profile['copy']['sections']['posts']['icon']"
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
                                :eyebrow="$profile['copy']['sections']['details']['eyebrow']"
                                :title="$profile['copy']['sections']['details']['title']"
                                :icon="$profile['copy']['sections']['details']['icon']"
                            >
                                <x-definition-list
                                    :items="$profile['details']"
                                    strong
                                    class="section-body"
                                />
                            </x-content-panel>

                            <x-content-panel
                                section="owner-interests"
                                :eyebrow="$profile['copy']['sections']['interests']['eyebrow']"
                                :title="$profile['copy']['sections']['interests']['title']"
                                :icon="$profile['copy']['sections']['interests']['icon']"
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
                            :eyebrow="$profile['copy']['sections']['languages']['eyebrow']"
                            :title="$profile['copy']['sections']['languages']['title']"
                            :icon="$profile['copy']['sections']['languages']['icon']"
                            size="compact"
                        >
                            <x-icon-list
                                :items="$profile['languages']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-privacy-summary"
                            :eyebrow="$profile['copy']['sections']['privacy']['eyebrow']"
                            :title="$profile['copy']['sections']['privacy']['title']"
                            :icon="$profile['copy']['sections']['privacy']['icon']"
                            size="compact"
                        >
                            <x-definition-list
                                :items="$profile['privacy']"
                                strong
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-profile-safety-actions
                            :actions="$profile['safety_actions']"
                            :copy="$profile['copy']['sections']['safety']"
                        />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
                @break

            @default
                <x-main-sidebar-layout data-owner-profile-overview>
                    <x-slot:main>
                        <x-page-stack gap="content">
                            <x-content-panel
                                section="about-owner"
                                :eyebrow="$profile['copy']['sections']['about']['eyebrow']"
                                :title="$profile['copy']['sections']['about']['title']"
                                :icon="$profile['copy']['sections']['about']['icon']"
                                data-owner-profile-section-icon
                            >
                                <x-section-copy :text="$profile['identity']['bio']" />
                            </x-content-panel>

                            @if ($profile['pets_restricted'])
                                <x-notice
                                    section="owner-pets-private"
                                    icon="lock-keyhole"
                                    :title="$profile['copy']['restrictions']['pets']['title']"
                                    :description="$profile['copy']['restrictions']['pets']['overview_description']"
                                />
                            @else
                                <x-profile-pet-list
                                    :pets="$profile['pets']"
                                    :eyebrow="$profile['copy']['sections']['pets']['eyebrow']"
                                    :title="$profile['copy']['sections']['pets']['title']"
                                    :empty-title="$profile['copy']['sections']['pets']['empty']"
                                    :can-manage="$profile['audience'] === 'owner'"
                                    :add-action="$profile['copy']['sections']['pets']['add_action']"
                                    :icon="$profile['copy']['sections']['pets']['icon']"
                                    data-owner-profile-section-icon
                                />
                            @endif

                            @if ($profile['posts_restricted'])
                                <x-notice
                                    section="owner-posts-private"
                                    icon="lock-keyhole"
                                    :title="$profile['copy']['restrictions']['posts']['overview_title']"
                                    :description="$profile['copy']['restrictions']['posts']['overview_description']"
                                />
                            @else
                                <x-recent-moments
                                    :posts="$profile['moments']"
                                    :eyebrow="$profile['copy']['sections']['posts']['eyebrow']"
                                    :title="$profile['copy']['sections']['posts']['title']"
                                    :empty-title="$profile['copy']['sections']['posts']['empty']"
                                    :icon="$profile['copy']['sections']['posts']['icon']"
                                    section="owner-moments"
                                    data-owner-profile-section-icon
                                />
                            @endif
                        </x-page-stack>
                    </x-slot:main>

                    <x-slot:sidebar>
                        <x-content-panel
                            section="owner-profile-completion"
                            :eyebrow="$profile['copy']['sections']['completion']['eyebrow']"
                            :title="$profile['copy']['sections']['completion']['title']"
                            :icon="$profile['copy']['sections']['completion']['icon']"
                            size="compact"
                            tone="coral"
                            data-owner-profile-section-icon
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
                            :eyebrow="$profile['copy']['sections']['badges']['eyebrow']"
                            :title="$profile['copy']['sections']['badges']['title']"
                            :icon="$profile['copy']['sections']['badges']['icon']"
                            size="compact"
                            data-owner-profile-section-icon
                        >
                            <x-profile-badge-list
                                :badges="$profile['badges']"
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-content-panel
                            section="owner-availability"
                            :eyebrow="$profile['copy']['sections']['availability']['eyebrow']"
                            :title="$profile['copy']['sections']['availability']['title']"
                            :icon="$profile['copy']['sections']['availability']['icon']"
                            size="compact"
                            tone="coral"
                            data-owner-profile-section-icon
                        >
                            <x-definition-list
                                :items="$profile['availability']"
                                strong
                                class="section-body"
                            />
                        </x-content-panel>

                        <x-profile-safety-actions
                            :actions="$profile['safety_actions']"
                            :copy="$profile['copy']['sections']['safety']"
                        />
                    </x-slot:sidebar>
                </x-main-sidebar-layout>
        @endswitch
    </x-page-stack>
</x-app-shell>
