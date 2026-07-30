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
        <x-ui.notice
            section="group-access"
            :icon="$accessGate['icon']"
            :title="$accessGate['title']"
            :description="$accessGate['description']"
        >
            <x-slot:actions>
                <x-ui.action-control
                    :label="$accessGate['action']['label']"
                    :icon="$accessGate['action']['icon']"
                    :endpoint="$accessGate['action']['endpoint']"
                    :payload="$accessGate['action']['payload']"
                    :variant="$accessGate['action']['variant']"
                />
            </x-slot:actions>
        </x-ui.notice>
    @elseif ($activeTab === 'posts')
        <section aria-labelledby="group-posts-title">
            <x-ui.section-heading
                eyebrow="Member feed"
                title="Recent group posts"
                title-id="group-posts-title"
                size="directory"
            />
            <div class="group-dashboard__stream section-body">
                @forelse ($content['posts'] as $post)
                    <x-object.group-post-card :post="$post" />
                @empty
                    <p class="group-dashboard__empty">No group posts have been published yet.</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'discussions')
        <x-ui.content-panel section="group-discussions" eyebrow="Structured conversations" title="Active discussions">
            <div class="discussion-list section-body">
                @forelse ($content['discussions'] as $discussion)
                    <article class="discussion-row">
                        <span class="discussion-row__icon">
                            <x-dynamic-component :component="'lucide-'.$discussion['icon']" class="icon" aria-hidden="true" />
                        </span>
                        <div>
                            <x-ui.status-badge :label="$discussion['status']" tone="surface" />
                            <h3>{{ $discussion['title'] }}</h3>
                            <p>{{ $discussion['description'] }}</p>
                            <span>{{ $discussion['meta'] }}</span>
                        </div>
                    </article>
                @empty
                    <p class="group-dashboard__empty">No active discussions are available.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @elseif ($activeTab === 'events')
        <x-ui.content-panel section="group-events" eyebrow="Group calendar" title="Upcoming events">
            <div class="event-list section-body">
                @forelse ($content['events'] as $event)
                    <x-object.group-event-card :event="$event" />
                @empty
                    <p class="group-dashboard__empty">No upcoming events are scheduled.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @elseif ($activeTab === 'members')
        <x-ui.content-panel section="group-members" eyebrow="Owner-managed participation" title="Community members">
            <div class="member-directory section-body" role="list">
                @forelse ($content['members'] as $member)
                    <article class="member-directory__item" role="listitem">
                        <x-ui.initials-avatar :initials="$member['initials']" :tone="$member['tone']" />
                        <div>
                            <h3>{{ $member['name'] }}</h3>
                            <p>{{ $member['detail'] }}</p>
                        </div>
                        <x-ui.status-badge :label="$member['badge']" tone="surface" />
                    </article>
                @empty
                    <p role="listitem" class="group-dashboard__empty">No members are visible in this directory.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @elseif ($activeTab === 'pets')
        <section aria-labelledby="group-pets-title">
            <x-ui.section-heading
                eyebrow="Participating profiles"
                title="Pets in this community"
                title-id="group-pets-title"
                size="directory"
            />
            <div class="group-pet-grid section-body">
                @forelse ($content['pets'] as $pet)
                    <x-object.group-pet-card :pet="$pet" />
                @empty
                    <p class="group-dashboard__empty">No pet profiles are visible in this community.</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'resources')
        <x-ui.content-panel section="group-resources" eyebrow="Group knowledge" title="Guides and resources">
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
                    <p class="group-dashboard__empty">No guides or resources have been added yet.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @elseif ($activeTab === 'rules')
        <div class="group-dashboard__columns">
            <x-ui.content-panel section="group-rules" eyebrow="Community boundary" title="Rules everyone agrees to">
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
                        <li class="group-dashboard__empty">No additional group rules are listed.</li>
                    @endforelse
                </ol>
            </x-ui.content-panel>
            <x-ui.content-panel section="group-requirements" title="Joining requirements">
                <x-ui.icon-list
                    :items="array_map(
                        static fn ($requirement) => [
                            'icon' => 'circle-check-big',
                            'title' => $requirement,
                            'description' => 'Required before active participation.',
                        ],
                        $group['requirements'],
                    )"
                    class="section-body"
                />
            </x-ui.content-panel>
        </div>
    @else
        <div class="group-dashboard__columns">
            <x-ui.content-panel
                section="group-pinned"
                :eyebrow="$content['pinned']['eyebrow']"
                :title="$content['pinned']['title']"
            >
                <p class="group-dashboard__copy">{{ $content['pinned']['description'] }}</p>
                <x-ui.icon-text :icon="$content['pinned']['icon']" class="section-body">
                    {{ $content['pinned']['meta'] }}
                </x-ui.icon-text>
            </x-ui.content-panel>

            <x-ui.content-panel section="group-principles" eyebrow="Community care" title="How this group works">
                <x-ui.icon-list :items="$content['principles']" class="section-body" />
            </x-ui.content-panel>
        </div>

        @if (! $canViewContent)
            <x-ui.notice
                section="group-access"
                :icon="$accessGate['icon']"
                :title="$accessGate['title']"
                :description="$accessGate['description']"
            >
                <x-slot:actions>
                    <x-ui.action-control
                        :label="$accessGate['action']['label']"
                        :icon="$accessGate['action']['icon']"
                        :endpoint="$accessGate['action']['endpoint']"
                        :payload="$accessGate['action']['payload']"
                        :variant="$accessGate['action']['variant']"
                    />
                </x-slot:actions>
            </x-ui.notice>
        @else
            <section aria-labelledby="overview-posts-title">
                <x-ui.section-heading
                    eyebrow="From the group"
                    title="Recent posts"
                    title-id="overview-posts-title"
                    size="directory"
                />
                <div class="group-dashboard__stream section-body">
                    @forelse (array_slice($content['posts'], 0, 2) as $post)
                        <x-object.group-post-card :post="$post" />
                    @empty
                        <p class="group-dashboard__empty">No recent posts are available.</p>
                    @endforelse
                </div>
            </section>

            <div class="group-dashboard__columns">
                <x-ui.content-panel section="overview-events" eyebrow="Coming up" title="Events">
                    <div class="event-list section-body">
                        @forelse ($content['events'] as $event)
                            <x-object.group-event-card
                                :event="$event"
                                :href="route('groups.show', ['group' => $group['key'], 'tab' => 'events'])"
                            />
                        @empty
                            <p class="group-dashboard__empty">No upcoming events are scheduled.</p>
                        @endforelse
                    </div>
                </x-ui.content-panel>
                <x-feature.group-poll :group="$group" :poll="$content['poll']" :membership="$membership['status']" />
            </div>

            <div class="group-dashboard__columns">
                <x-feature.group-chat-preview :messages="$content['chat']" />
                <x-ui.content-panel section="group-team" title="Community team">
                    <x-object.member-list :members="$content['moderators']" class="section-body" />
                </x-ui.content-panel>
            </div>
        @endif
    @endif

    @if ($membership['status'] === 'joined')
        <x-ui.content-panel section="group-notifications" title="Group notifications" meta="Applies only to this group">
            <div class="notification-options section-body" role="group" aria-label="Group notification level">
                @forelse ($membership['notification_options'] as $option)
                    <x-ui.action-control
                        :label="$option['label']"
                        :icon="$option['active'] ? 'check' : 'bell'"
                        :endpoint="route('actions.perform')"
                        :payload="$option['payload']"
                        :active="$option['active']"
                        :pressed="$option['active']"
                        variant="paper"
                    />
                @empty
                    <p class="group-dashboard__empty">Notification settings are unavailable.</p>
                @endforelse
            </div>
        </x-ui.content-panel>
    @endif
</div>
