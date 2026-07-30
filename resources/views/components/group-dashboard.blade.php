@props([
    'group',
    'activeTab',
    'content',
    'membership',
    'canViewContent',
    'accessGate',
])

<div class="group-dashboard">
    @if (! $canViewContent && ! in_array($activeTab, ['overview', 'rules'], true))
        <x-notice
            section="group-access"
            :icon="$accessGate['icon']"
            :title="$accessGate['title']"
            :description="$accessGate['description']"
        >
            <x-slot:actions>
                <x-action-control
                    :label="$accessGate['action']['label']"
                    :icon="$accessGate['action']['icon']"
                    :endpoint="$accessGate['action']['endpoint']"
                    :payload="$accessGate['action']['payload']"
                    :variant="$accessGate['action']['variant']"
                />
            </x-slot:actions>
        </x-notice>
    @elseif ($activeTab === 'posts')
        <section aria-labelledby="group-posts-title">
            <x-section-heading
                eyebrow="{{ __('ui.member_feed_0e259ee595') }}"
                title="{{ __('ui.recent_group_posts_58931aab97') }}"
                title-id="group-posts-title"
                size="directory"
            />
            <div class="group-dashboard__stream section-body">
                @forelse ($content['posts'] as $post)
                    <x-group-post-card :post="$post" />
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.no_group_posts_have_been_published_yet_907b2e09af') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'discussions')
        <x-content-panel section="group-discussions" eyebrow="{{ __('ui.structured_conversations_f123524612') }}" title="{{ __('ui.active_discussions_2b28959464') }}">
            <div class="discussion-list section-body">
                @forelse ($content['discussions'] as $discussion)
                    <article class="discussion-row">
                        <span class="discussion-row__icon">
                            <x-dynamic-component :component="'lucide-'.$discussion['icon']" class="icon" aria-hidden="true" />
                        </span>
                        <div>
                            <x-status-badge :label="$discussion['status']" tone="surface" />
                            <h3>{{ $discussion['title'] }}</h3>
                            <p>{{ $discussion['description'] }}</p>
                            <span>{{ $discussion['meta'] }}</span>
                        </div>
                    </article>
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.no_active_discussions_are_available_853abf17e2') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'events')
        <x-content-panel section="group-events" eyebrow="{{ __('ui.group_calendar_4b4216fa5b') }}" title="{{ __('ui.upcoming_events_df9110b56f') }}">
            <div class="event-list section-body">
                @forelse ($content['events'] as $event)
                    <x-group-event-card :event="$event" />
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.no_upcoming_events_are_scheduled_5e3c2e1b79') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'members')
        <x-content-panel section="group-members" eyebrow="{{ __('ui.owner_managed_participation_deb4c70b44') }}" title="{{ __('ui.community_members_6efb83e499') }}">
            <div class="member-directory section-body" role="list">
                @forelse ($content['members'] as $member)
                    <article class="member-directory__item" role="listitem">
                        <x-initials-avatar :initials="$member['initials']" :tone="$member['tone']" />
                        <div>
                            <h3>{{ $member['name'] }}</h3>
                            <p>{{ $member['detail'] }}</p>
                        </div>
                        <x-status-badge :label="$member['badge']" tone="surface" />
                    </article>
                @empty
                    <p role="listitem" class="group-dashboard__empty">{{ __('ui.no_members_are_visible_in_this_directory_96595af060') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'pets')
        <section aria-labelledby="group-pets-title">
            <x-section-heading
                eyebrow="{{ __('ui.participating_profiles_51b5b8dd44') }}"
                title="{{ __('ui.pets_in_this_community_2531d9c8e1') }}"
                title-id="group-pets-title"
                size="directory"
            />
            <div class="group-pet-grid section-body">
                @forelse ($content['pets'] as $pet)
                    <x-group-pet-card :pet="$pet" />
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.no_pet_profiles_are_visible_in_this_community_95a86ad6a0') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'resources')
        <x-content-panel section="group-resources" eyebrow="{{ __('ui.group_knowledge_4f421fa6c6') }}" title="{{ __('ui.guides_and_resources_109c219906') }}">
            <div class="resource-list section-body">
                @forelse ($content['resources'] as $resource)
                    <article class="resource-row">
                        <span class="resource-row__icon">
                            <x-dynamic-component :component="'lucide-'.$resource['icon']" class="icon" aria-hidden="true" />
                        </span>
                        <div>
                            <h3>{{ $resource['title'] }}</h3>
                            <p>{{ $resource['description'] }}</p>
                            <span>{{ $resource['meta'] }}</span>
                        </div>
                        <x-lucide-chevron-right class="icon icon--sm" aria-hidden="true" />
                    </article>
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.no_guides_or_resources_have_been_added_yet_08d7121d50') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'rules')
        <div class="group-dashboard__columns">
            <x-content-panel section="group-rules" eyebrow="{{ __('ui.community_boundary_2e272f13ce') }}" title="{{ __('ui.rules_everyone_agrees_to_9fde042d75') }}">
                <ol class="rule-list section-body">
                    @forelse ($content['rules'] as $rule)
                        <li>
                            <span>{{ $loop->iteration }}</span>
                            <div>
                                <h3>{{ $rule['title'] }}</h3>
                                <p>{{ $rule['description'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="group-dashboard__empty">{{ __('ui.no_additional_group_rules_are_listed_b58cda5efc') }}</li>
                    @endforelse
                </ol>
            </x-content-panel>
            <x-content-panel section="group-requirements" title="{{ __('ui.joining_requirements_6399184f74') }}">
                <x-icon-list
                    :items="array_map(
                        static fn ($requirement) => [
                            'icon' => 'circle-check-big',
                            'title' => $requirement,
                            'description' => __('ui.required_before_active_participation_99abd2353c'),
                        ],
                        $group['requirements'],
                    )"
                    class="section-body"
                />
            </x-content-panel>
        </div>
    @else
        <div class="group-dashboard__columns">
            <x-content-panel
                section="group-pinned"
                :eyebrow="$content['pinned']['eyebrow']"
                :title="$content['pinned']['title']"
            >
                <p class="group-dashboard__copy">{{ $content['pinned']['description'] }}</p>
                <x-icon-text :icon="$content['pinned']['icon']" class="section-body">
                    {{ $content['pinned']['meta'] }}
                </x-icon-text>
            </x-content-panel>

            <x-content-panel section="group-principles" eyebrow="{{ __('ui.community_care_d0650d1a0a') }}" title="{{ __('ui.how_this_group_works_ea33488aba') }}">
                <x-icon-list :items="$content['principles']" class="section-body" />
            </x-content-panel>
        </div>

        @if (! $canViewContent)
            <x-notice
                section="group-access"
                :icon="$accessGate['icon']"
                :title="$accessGate['title']"
                :description="$accessGate['description']"
            >
                <x-slot:actions>
                    <x-action-control
                        :label="$accessGate['action']['label']"
                        :icon="$accessGate['action']['icon']"
                        :endpoint="$accessGate['action']['endpoint']"
                        :payload="$accessGate['action']['payload']"
                        :variant="$accessGate['action']['variant']"
                    />
                </x-slot:actions>
            </x-notice>
        @else
            <section aria-labelledby="overview-posts-title">
                <x-section-heading
                    eyebrow="{{ __('ui.from_the_group_c73400d709') }}"
                    title="{{ __('ui.recent_posts_eba7045ba5') }}"
                    title-id="overview-posts-title"
                    size="directory"
                />
                <div class="group-dashboard__stream section-body">
                    @forelse (array_slice($content['posts'], 0, 2) as $post)
                        <x-group-post-card :post="$post" />
                    @empty
                        <p class="group-dashboard__empty">{{ __('ui.no_recent_posts_are_available_f5cfd52689') }}</p>
                    @endforelse
                </div>
            </section>

            <div class="group-dashboard__columns">
                <x-content-panel section="overview-events" eyebrow="{{ __('ui.coming_up_b743c76082') }}" title="{{ __('ui.events_8d14f6e72d') }}">
                    <div class="event-list section-body">
                        @forelse ($content['events'] as $event)
                            <x-group-event-card
                                :event="$event"
                                :href="route('groups.show', ['group' => $group['key'], 'tab' => 'events'])"
                            />
                        @empty
                            <p class="group-dashboard__empty">{{ __('ui.no_upcoming_events_are_scheduled_5e3c2e1b79') }}</p>
                        @endforelse
                    </div>
                </x-content-panel>
                <x-group-poll :group="$group" :poll="$content['poll']" :membership="$membership['status']" />
            </div>

            <div class="group-dashboard__columns">
                <x-group-chat-preview :messages="$content['chat']" />
                <x-content-panel section="group-team" title="{{ __('ui.community_team_53d704af04') }}">
                    <x-member-list :members="$content['moderators']" class="section-body" />
                </x-content-panel>
            </div>
        @endif
    @endif

    @if ($membership['status'] === 'joined')
        <x-content-panel section="group-notifications" title="{{ __('ui.group_notifications_30706f75cc') }}" meta="Applies only to this group">
            <div class="notification-options section-body" role="group" aria-label="{{ __('ui.group_notification_level_4217391593') }}">
                @forelse ($membership['notification_options'] as $option)
                    <x-action-control
                        :label="$option['label']"
                        :icon="$option['active'] ? 'check' : 'bell'"
                        :endpoint="route('actions.perform')"
                        :payload="$option['payload']"
                        :active="$option['active']"
                        :pressed="$option['active']"
                        variant="paper"
                    />
                @empty
                    <p class="group-dashboard__empty">{{ __('ui.notification_settings_are_unavailable_40b8672453') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @endif
</div>
