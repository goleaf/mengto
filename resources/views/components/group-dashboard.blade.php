@props([
    'group',
    'activeTab',
    'content',
    'membership',
    'canViewContent',
    'accessGate',
])

<div data-group-detail-dashboard class="group-dashboard">
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
                eyebrow="{{ __('groups.detail.sections.posts.eyebrow') }}"
                title="{{ __('groups.detail.sections.posts.title') }}"
                title-id="group-posts-title"
                size="directory"
            />
            <div class="group-dashboard__stream section-body">
                @forelse ($content['posts'] as $post)
                    <x-group-post-card :post="$post" />
                @empty
                    <p class="group-dashboard__empty">{{ __('groups.detail.sections.posts.empty') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'discussions')
        <x-content-panel section="group-discussions" eyebrow="{{ __('groups.detail.sections.discussions.eyebrow') }}" title="{{ __('groups.detail.sections.discussions.title') }}">
            <div class="discussion-list section-body">
                @forelse ($content['discussions'] as $discussion)
                    <article class="discussion-row">
                        <span class="discussion-row__icon">
                            <x-ui-icon :name="$discussion['icon']" />
                        </span>
                        <div>
                            <x-status-badge :label="$discussion['status']" tone="surface" />
                            <h3>{{ $discussion['title'] }}</h3>
                            <p>{{ $discussion['description'] }}</p>
                            <span>{{ $discussion['meta'] }}</span>
                        </div>
                    </article>
                @empty
                    <p class="group-dashboard__empty">{{ __('groups.detail.sections.discussions.empty') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'events')
        <x-content-panel section="group-events" eyebrow="{{ __('groups.detail.sections.events.eyebrow') }}" title="{{ __('groups.detail.sections.events.title') }}">
            <div class="event-list section-body">
                @forelse ($content['events'] as $event)
                    <x-group-event-card :event="$event" />
                @empty
                    <p class="group-dashboard__empty">{{ __('groups.detail.sections.events.empty') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'members')
        <x-content-panel section="group-members" eyebrow="{{ __('groups.detail.sections.members.eyebrow') }}" title="{{ __('groups.detail.sections.members.title') }}">
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
                    <p role="listitem" class="group-dashboard__empty">{{ __('groups.detail.sections.members.empty') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'pets')
        <section aria-labelledby="group-pets-title">
            <x-section-heading
                eyebrow="{{ __('groups.detail.sections.pets.eyebrow') }}"
                title="{{ __('groups.detail.sections.pets.title') }}"
                title-id="group-pets-title"
                size="directory"
            />
            <div class="group-pet-grid section-body">
                @forelse ($content['pets'] as $pet)
                    <x-group-pet-card :pet="$pet" />
                @empty
                    <p class="group-dashboard__empty">{{ __('groups.detail.sections.pets.empty') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'resources')
        <x-content-panel section="group-resources" eyebrow="{{ __('groups.detail.sections.resources.eyebrow') }}" title="{{ __('groups.detail.sections.resources.title') }}">
            <div class="resource-list section-body">
                @forelse ($content['resources'] as $resource)
                    <article class="resource-row">
                        <span class="resource-row__icon">
                            <x-ui-icon :name="$resource['icon']" />
                        </span>
                        <div>
                            <h3>{{ $resource['title'] }}</h3>
                            <p>{{ $resource['description'] }}</p>
                            <span>{{ $resource['meta'] }}</span>
                        </div>
                        <x-ui-icon name="chevron-right" size="sm" />
                    </article>
                @empty
                    <p class="group-dashboard__empty">{{ __('groups.detail.sections.resources.empty') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'rules')
        <div class="group-dashboard__columns">
            <x-content-panel section="group-rules" eyebrow="{{ __('groups.detail.sections.rules.eyebrow') }}" title="{{ __('groups.detail.sections.rules.title') }}">
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
                        <li class="group-dashboard__empty">{{ __('groups.detail.sections.rules.empty') }}</li>
                    @endforelse
                </ol>
            </x-content-panel>
            <x-content-panel section="group-requirements" title="{{ __('groups.detail.sections.rules.requirements_title') }}">
                <x-icon-list
                    :items="array_map(
                        static fn ($requirement) => [
                            'icon' => 'circle-check-big',
                            'title' => $requirement,
                            'description' => __('groups.detail.sections.rules.requirement_description'),
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

            <x-content-panel section="group-principles" eyebrow="{{ __('groups.detail.sections.principles.eyebrow') }}" title="{{ __('groups.detail.sections.principles.title') }}">
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
                    eyebrow="{{ __('groups.detail.sections.overview_posts.eyebrow') }}"
                    title="{{ __('groups.detail.sections.overview_posts.title') }}"
                    title-id="overview-posts-title"
                    size="directory"
                />
                <div class="group-dashboard__stream section-body">
                    @forelse (array_slice($content['posts'], 0, 2) as $post)
                        <x-group-post-card :post="$post" />
                    @empty
                        <p class="group-dashboard__empty">{{ __('groups.detail.sections.overview_posts.empty') }}</p>
                    @endforelse
                </div>
            </section>

            <div class="group-dashboard__columns">
                <x-content-panel section="overview-events" eyebrow="{{ __('groups.detail.sections.overview_events.eyebrow') }}" title="{{ __('groups.detail.sections.overview_events.title') }}">
                    <div class="event-list section-body">
                        @forelse ($content['events'] as $event)
                            <x-group-event-card
                                :event="$event"
                                :href="route('groups.show', ['group' => $group['key'], 'tab' => 'events'])"
                            />
                        @empty
                            <p class="group-dashboard__empty">{{ __('groups.detail.sections.overview_events.empty') }}</p>
                        @endforelse
                    </div>
                </x-content-panel>
                <x-group-poll :group="$group" :poll="$content['poll']" :membership="$membership['status']" />
            </div>

            <div class="group-dashboard__columns">
                <x-group-chat-preview :messages="$content['chat']" />
                <x-content-panel section="group-team" title="{{ __('groups.detail.sections.team_title') }}">
                    <x-member-list :members="$content['moderators']" class="section-body" />
                </x-content-panel>
            </div>
        @endif
    @endif

    @if ($membership['status'] === 'joined')
        <x-content-panel section="group-notifications" title="{{ __('groups.detail.notifications.title') }}" meta="{{ __('groups.detail.notifications.meta') }}">
            <div class="notification-options section-body" role="group" aria-label="{{ __('groups.detail.notifications.level_label') }}">
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
                    <p class="group-dashboard__empty">{{ __('groups.detail.notifications.unavailable') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @endif
</div>
